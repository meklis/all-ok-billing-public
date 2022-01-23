<?php

namespace envPHP\payments;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 22.08.2017
 * Time: 23:43
 */
class gwCity24
{
    protected $SignCertEasyPay = "";
    protected $PrivateKeyProvider = "";
    protected $checkSign = false;
    protected $logDir = "/www/gwLog";
    protected $easypay = null;
    protected $sql = null;

    function __construct()
    {

        $this->easypay = new  easypay($this->SignCertEasyPay, $this->PrivateKeyProvider);
        $this->easypay->checkSign = $this->checkSign;
        $this->easypay->setCheck([__NAMESPACE__ . '\gwCity24', 'check'])
            ->setConfirm([__NAMESPACE__ . '\gwCity24', 'confirm'])
            ->setPayment([__NAMESPACE__ . '\gwCity24', 'payment'])
            ->setCancel([__NAMESPACE__ . '\gwCity24', 'cancel']);
    }

    function go($raw_http)
    {
        $response = $this->easypay->createOperation($raw_http);
        $this->logger($raw_http, $response);
        return $response;
    }

    function check($service_id, $account)
    {

        $data = dbConn()->query("SELECT Name, Balance FROM service.clients WHERE agreement = '$account' and   `status` = 'ENABLED'");
        if ($data->num_rows == 0) {
            dbConn()->query("INSERT INTO gwPayments.city24_check (serviceId, account, response) VALUES ($service_id, $account, 'ACCOUNT_NOT_FOUND')");
            throw new \Exception("ACCOUNT NOT FOUND", 404);
        }
        $resp = $data->fetch_assoc();
        dbConn()->query("INSERT INTO gwPayments.city24_check (serviceId, account, response) VALUES ($service_id, $account, '" . json_encode($resp, JSON_UNESCAPED_UNICODE) . "')");
        return $resp;
    }

    function payment($service_id, $account, $amount, $order_id)
    {
        //@TODO Временно убрать ServiceID для тестирования
        /*
        if(!isset(self::$providers[$service_id])) {
            throw new \Exception("ACCOUNT NOT FOUND", 404);
        }
        */
        $provider = 1026;//self::$providers[$service_id];

        //Проверка аккаунта
        if (dbConn()->query("SELECT id FROM service.clients WHERE agreement = $account and provider = $provider and `status` = 'ENABLED'")->num_rows == 0) {
            throw new \Exception("ERROR CREATE PAYMENT - INCORRECT ACCOUNT", 400);
        }

        //$amount = str_replace(".",",",$amount);
        if (!$service_id) {
            $service_id = 1;
        }
        $test = dbConn()->query("INSERT INTO gwPayments.city24_payments (account, serviceId, amount, orderId) VALUES ('$account', '$service_id', '$amount','$order_id')");
        if (!$test) {
            throw new \Exception("ERROR CREATE PAYMENT", 502);
        }
        return dbConn()->insert_id;
    }

    function confirm($service_id, $payment_id)
    {
        //Проверка на оплату
        $checking = dbConn()->query("SELECT time FROM gwPayments.city24_confirm WHERE payment = $payment_id");
        if ($checking->num_rows !== 0) return $checking->fetch_assoc()['time'];

        $checking = dbConn()->query("SELECT cl.id, p.amount 
                                                FROM gwPayments.city24_payments p 
                                                JOIN service.clients cl on cl.agreement = p.account
                                                WHERE p.id = $payment_id LIMIT 1");
        if ($checking->num_rows == 0) {
            throw new \Exception("PAYMENT NOT FOUND");
        }
        $data = $checking->fetch_assoc();

        dbConn()->query("INSERT INTO gwPayments.city24_confirm (serviceId,payment) VALUES ($service_id,$payment_id)");
        $checkInserted = dbConn()->query("SELECT time FROM gwPayments.city24_confirm WHERE id = " . dbConn()->insert_id);
        $time = date("Y-m-d H:i:s");
        if ($checkInserted->num_rows !== 0) {
            $time = $checkInserted->fetch_assoc()['time'];
        }
        try {
            \envPHP\service\payment::add($data['id'], $data['amount'], "City24", $payment_id, "Оплата через City24");
        } catch (\Exception $e) {
            throw new \Exception("ERROR CONFIRM PAYMENT");
        }
        return $time;
    }

    function cancel($service, $payment)
    {
        try {
            \envPHP\service\payment::delete(\envPHP\service\payment::getByCity24Id($payment));
        } catch (Exception $e) {
            throw new Exception("ERROR CANCEL PAYMENT");
        }
        dbConn()->query("INSERT INTO gwPayments.city24_cancel (serviceId,payment) VALUES ($service,$payment)");
        return dbConn()->query("SELECT time FROM gwPayments.city24_cancel WHERE id = " . dbConn()->insert_id)->fetch_assoc()['time'];
    }

    function logger($request, $response)
    {
        $file_name = $this->logDir . "/easypay_" . date("Y-m-d") . ".txt";
        file_put_contents($file_name, "
====================================START============================================
DATE: " . date("Y-m-d H:i:s") . "
HOST: " . $_SERVER['REMOTE_ADDR'] . "
REQUEST: 
$request
RESPONSE: 
$response
=====================================END==============================================
", FILE_APPEND);
    }
}

