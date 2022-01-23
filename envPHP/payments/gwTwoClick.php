<?php
namespace envPHP\payments;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 22.08.2017
 * Time: 23:43
 */
require_once __DIR__ . "/TwoClick.php";


class gwTwoClick
{
    protected $logDir = "/www/gwLog";
    protected $gw = null;
    protected $sql = null;

    function __construct()
    {
        $this->gw = new  TwoClick(getGlobalConfigVar('GW_PAYMENTS_TWO_CLICK')['secret']);
        $this->gw->setCheck([__NAMESPACE__ . '\gwTwoClick','check'])
                      ->setPayment([__NAMESPACE__ . '\gwTwoClick','payment'])
                      ->setStatus([__NAMESPACE__ . '\gwTwoClick','status']);
    }
    function go($raw_http) {
        $response =  $this->gw->createOperation($raw_http);
        $this->logger($raw_http,$response);
        return $response;
    }
    function check($service_id,$account,$payId,$terminal) {

        //Проверка дубликата
        if(dbConn()->query("SELECT id FROM gwPayments.twoClick_check WHERE payment_id = '{$payId}' ")->num_rows != 0) {
            throw new \Exception("ERROR CREATE CHECK - DUPLICATE KEY", -100);
        }

       $data = dbConn()->query("SELECT Name, Balance FROM service.clients WHERE agreement = '{$account}' and provider = '{$service_id}' and `status` = 'ENABLED'");
       if($data->num_rows == 0) {
           dbConn()->query("INSERT INTO gwPayments.twoClick_check (service_id, account, payment_id, terminal, `status`, status_code) 
                                    VALUES ('{$service_id}','{$account}','{$payId}','{$terminal}','ACCOUNT NOT FOUND', 404 )");
           throw new \Exception("ACCOUNT NOT FOUND", -40);
       }
       $resp = $data->fetch_assoc();
        dbConn()->query("INSERT INTO gwPayments.twoClick_check (service_id, account, payment_id, terminal, `status`, status_code) 
                                    VALUES ('{$service_id}','{$account}','{$payId}','{$terminal}','".addslashes(json_encode($resp, JSON_UNESCAPED_UNICODE))."', 0)");
       return $resp;
    }
    function payment($service_id,$account,$amount,$receiptNum, $payId, $terminal) {

        //Проверка чека. Если чека не было - отменяем транзакцию
        if(dbConn()->query("SELECT id FROM gwPayments.twoClick_check WHERE payment_id = '{$payId}'")->num_rows == 0) {
            throw new \Exception("ERROR CREATE PAYMENT - CHECK NOT EXISTS", -101);
        }

        //Проверка дубликата
        if(dbConn()->query("SELECT id FROM gwPayments.twoClick_payment WHERE payment_id = '{$payId}' ")->num_rows != 0) {
            throw new \Exception("ERROR CREATE PAYMENT - DUPLICATE KEY", -100);
        }

        //Попытка внести платеж
        if(!dbConn()->query("INSERT INTO gwPayments.twoClick_payment (service_id, account, amount, receipt_num, payment_id, terminal) 
                                    VALUES ('{$service_id}','{$account}','{$amount}','{$receiptNum}','{$payId}', '{$terminal}')")) {
            throw new \Exception("ERROR CREATE PAYMENT", -90);
        }

        //Проверка аккаунта
        if(dbConn()->query("SELECT id FROM service.clients WHERE agreement = {$account} and provider = '{$service_id}' and `status` = 'ENABLED'")->num_rows == 0) {
            throw new \Exception("ERROR CREATE PAYMENT - INCORRECT ACCOUNT", -40);
        }

        //Заводим новый платеж
        try {
            $id = dbConn()->query("SELECT id FROM service.clients WHERE agreement = '{$account}' and `status` = 'ENABLED'")->fetch_assoc()['id'];
            \envPHP\service\payment::add($id, $amount, "2click", $payId, "Оплата через 2Click");
        } catch (\Exception $e) {
            throw new \Exception("ERROR INSERT PAYMENT INTO ServiceDatabase", -90);
        }

        //Возвращаем ID
        return dbConn()->insert_id;
    }
    function status($service_id,$payId) {
        $data = dbConn()->query("SELECT id, `time`, amount, canceled, account FROM gwPayments.twoClick_payment WHERE payment_id = '{$payId}'");
        if($data->num_rows == 0) {
            dbConn()->query("INSERT into gwPayments.twoClick_status (service_id, payment_id, pay_local_id) 
            VALUES ('{$service_id}', '{$payId}', 0)");
            throw new \Exception("ERROR CHECK PAYMENT - TRANSACTION NOT FOUND", -10);
        }
        $resp = $data->fetch_assoc();
        dbConn()->query("INSERT into gwPayments.twoClick_status (service_id, payment_id, pay_local_id) 
            VALUES ('{$service_id}', '{$payId}', {$resp['id']})");
        return ['amount'=>$resp['amount'], 'time'=>$resp['time'], 'account'=>$resp['account']];
    }
    function logger($request,$response) {
        $file_name = $this->logDir . "/twoClick_" . date("Y-m-d") . ".txt";
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

