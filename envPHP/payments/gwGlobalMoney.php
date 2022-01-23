<?php
namespace envPHP\payments;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 22.08.2017
 * Time: 23:43
 */

class gwGlobalMoney
{
    protected $logDir = "/www/gwLog";
    protected $easypay = null;
    protected $sql = null;
    static protected $providers = [];
    function __construct()
    {
        $conf = getGlobalConfigVar('GW_PAYMENTS_GLOBAL_MONEY');
        self::$providers = $conf['providers'];
        $this->easypay = new  globalMoney($conf['cert_path'], $conf['key_path']);
        $this->easypay->checkSign = $conf['sign_check_enabled'];
        $this->easypay->setCheck([__NAMESPACE__ . '\gwGlobalMoney','check'])
                      ->setConfirm([__NAMESPACE__ . '\gwGlobalMoney','confirm'])
                      ->setPayment([__NAMESPACE__ . '\gwGlobalMoney','payment'])
                      ->setCancel([__NAMESPACE__ . '\gwGlobalMoney','cancel']);
    }
    function go($raw_http) {
        $response =  $this->easypay->createOperation($raw_http);
        $this->logger($raw_http,$response);
        return $response;
    }
    function check($service_id,$account) {
       $data = dbConn()->query("SELECT Name, Balance FROM service.clients WHERE agreement = '$account'  and `status` = 'ENABLED'");
       if($data->num_rows == 0) {
           dbConn()->query("INSERT INTO gwPayments.gb_check (serviceId, account, response) VALUES ($service_id, $account, 'ACCOUNT_NOT_FOUND')");
           throw new \Exception("ACCOUNT NOT FOUND", 404);
       }
       $resp = $data->fetch_assoc();
        dbConn()->query("INSERT INTO gwPayments.gb_check (serviceId, account, response) VALUES ($service_id, $account, '".json_encode($resp, JSON_UNESCAPED_UNICODE )."')");
       return $resp;
    }
    function payment($service_id,$account,$amount,$order_id) {

            //Проверка аккаунта
            if(dbConn()->query("SELECT id FROM service.clients WHERE agreement = '$account'  and `status` = 'ENABLED'")->num_rows == 0) {
                throw new \Exception("ERROR CREATE PAYMENT - INCORRECT ACCOUNT", 400);
            }

            //$amount = str_replace(".",",",$amount);
            $test = dbConn()->query("INSERT INTO gwPayments.gb_payments (account, serviceId, amount, orderId) VALUES ('$account', '$service_id', '$amount','$order_id')");
            if(!$test) {
                throw new \Exception("ERROR CREATE PAYMENT", 502);
            }
            return dbConn()->insert_id;
    }
    function confirm($service_id,$payment_id) {
            //Проверка на оплату
            $checking = dbConn()->query("SELECT time FROM gwPayments.gb_confirm WHERE payment = $payment_id")->fetch_assoc()['time'];
            if($checking) return $checking;

           $checking = dbConn()->query("SELECT cl.id, p.amount 
                                                FROM gwPayments.gb_payments p 
                                                JOIN service.clients cl on cl.agreement = p.account
                                                WHERE p.id = $payment_id LIMIT 1");
           if($checking->num_rows == 0) {
               throw new \Exception("PAYMENT NOT FOUND");
           }
           $data  = $checking->fetch_assoc() ;
           try {
               \envPHP\service\payment::add($data['id'], $data['amount'], "GlobalMoney", $payment_id, "Оплата через GlobalMoney");
           } catch (\Exception $e) {
               throw new \Exception("ERROR CONFIRM PAYMENT");
           }
           dbConn()->query("INSERT INTO gwPayments.gb_confirm (serviceId,payment) VALUES ($service_id,$payment_id)");
           return dbConn()->query("SELECT time FROM gwPayments.gb_confirm WHERE id = ".dbConn()->insert_id)->fetch_assoc()['time'];
    }
    function cancel($service,$payment) {
        try {
            $paymentId = \envPHP\service\payment::getByGlobalMoneyId($payment);
            if($paymentId)
            \envPHP\service\payment::delete($paymentId);
        } catch (\Exception $e) {
            throw new \Exception("ERROR CANCEL PAYMENT");
        }
        dbConn()->query("INSERT INTO gwPayments.gb_cancel (serviceId,payment) VALUES ($service,$payment)");
        return dbConn()->query("SELECT time FROM gwPayments.gb_cancel WHERE id = ".dbConn()->insert_id)->fetch_assoc()['time'];
    }
    function logger($request,$response) {
        $file_name = $this->logDir . "/globalmoney_" . date("Y-m-d") . ".txt";
        file_put_contents($file_name, "
====================================START============================================
DATE: ".date("Y-m-d H:i:s")."
HOST: ".$_SERVER['REMOTE_ADDR']."
REQUEST: 
$request
RESPONSE: 
$response
=====================================END==============================================
",FILE_APPEND);
    }
}

