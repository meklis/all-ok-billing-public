<?php
namespace envPHP\payments;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 22.08.2017
 * Time: 23:43
 */

class gwIBox
{


    protected $logDir = "/www/gwLog";
    protected $provider = null;
    static protected $DEBUG = false;
    static protected $providersList = [
      '1'=>1026,
    ];
    protected $sql = null;
    function __construct()
    {
        $this->provider = new  iBox();
        $this->provider->setPayment([__NAMESPACE__ . '\gwIBox','payment'])
                       ->setCheck([__NAMESPACE__ . '\gwIBox','check'])
                       ->setGetStatus([__NAMESPACE__ . '\gwIBox', 'status'])
                       ->setCancel([__NAMESPACE__ . '\gwIBox', 'cancel'])
        ;
    }
    function enableDebug() {
        self::$DEBUG = true;
        return $this;
    }
    function go($request) {
        $response =  $this->provider->createOperation($request);
        $this->logger(json_encode($request, JSON_UNESCAPED_UNICODE ),$response);
        return $response;
    }
    function check($transactionId,$account,$amount, $payType, $providerId, $termId, $agentId) {
        $provider = isset(self::$providersList[$providerId]) ? self::$providersList[$providerId]: -1;

        //Проверка существования транзакции в платежах
        if(dbConn()->query("SELECT id FROM gwPayments.ibox_payment WHERE transactionID = '$transactionId'")->num_rows != 0) {
            throw new \Exception("TRANSACTION ALREADY EXIST",3);
        }

       $data = dbConn()->query("SELECT Name, Balance FROM service.clients WHERE agreement = '$account' and provider = $provider and `status` = 'ENABLED'");
       if($data->num_rows == 0) {
           dbConn()->query("INSERT INTO gwPayments.ibox_check (time, transactionId, account, amount, `status`, payType, provider) VALUES 
(NOW(), '$transactionId', '$account', '$amount', 5, '$payType', '$providerId');");
           throw new \Exception("ACCOUNT NOT FOUND", 5);
       }
       $resp = $data->fetch_assoc();
       $test = dbConn()->query("INSERT INTO gwPayments.ibox_check (time, transactionId, account, amount, `status`, payType, provider) VALUES 
(NOW(), '$transactionId', '$account', '$amount',0, '$payType', '$providerId');");
       if(!$test) throw  new \Exception(dbConn()->error, 1);
       $resp['id'] = dbConn()->insert_id;
       return $resp;
    }
    function status($transactionId) {
        $data = dbConn()->query("SELECT c.name Name, c.balance Balance
FROM gwPayments.`ibox_check` i 
JOIN service.clients c on c.agreement = i.account 
WHERE i.transactionId = '$transactionId' LIMIT 1");
        if($data->num_rows == 0) {
            throw new \Exception("TRANSACTION NOT FOUND", 210);
        }
        $resp = $data->fetch_assoc();
        $resp['id'] = dbConn()->insert_id;
        return $resp;
    }
    function cancel($transactionId) {
        if(dbConn()->query("SELECT * FROM gwPayments.ibox_cancel WHERE payment in (SELECT id FROM gwPayments.ibox_payment WHERE transactionId = '$transactionId')")->num_rows > 0) {
            throw new \Exception("PAYMENT HAS CANCEL STATUS EARLY", 507);
        }
        $status = self::status($transactionId);
        try {
            \envPHP\service\payment::delete(\envPHP\service\payment::getByIBoxId($transactionId));
        } catch (\Exception $e) {
            throw new \Exception("ERROR CANCEL PAYMENT", 300);
        }
        $paymentId = dbConn()->query("SELECT id FROM gwPayments.ibox_payment WHERE transactionID = '$transactionId'")->fetch_assoc()['id'];
        if(!$paymentId) $paymentId = -1;
        dbConn()->query("INSERT INTO gwPayments.ibox_cancel (payment) VALUES ('$paymentId')");
        return $status;
    }
    function payment($transactionId,$account,$amount, $payType, $providerId, $termId, $agentId, $transactionDate) {

        $nowTime = iBox::datetime();
        $provider = isset(self::$providersList[$providerId]) ? self::$providersList[$providerId]: -1;

      //Проверка, что платеж уже был
        $res = dbConn()->query("SELECT id, time, amount FROM gwPayments.ibox_payment WHERE transactionID = '$transactionId'");
        if (dbConn()->error != "") {
            throw new \Exception("ERROR CHECK EXIST PAYMENT", 243);
        } elseif ($res->num_rows != 0) {
            dbConn()->query("INSERT INTO gwPayments.ibox_payment (time, account, amount, transactionID, status, provider, payType, termId, agentId, transactionDate) VALUES 
                                                      ('$nowTime', '$account', '$amount', '$transactionId', 3, '$provider', '$payType', '$termId', '$agentId', '$transactionDate');");
            return $res->fetch_assoc();
        }

        //Проверка аккаунта
        if(!$agreementId = dbConn()->query("SELECT id FROM service.clients WHERE agreement = '$account' and provider = '$provider' and `status` = 'ENABLED'")->fetch_assoc()['id']) {
            throw new \Exception("ACCOUNT NOT FOUND", 5);
        }
        try {
           if(!self::$DEBUG) \envPHP\service\payment::add($agreementId, $amount, "iBox", $transactionId, "Оплата через iBox");
        } catch (\Exception $e) {
            $test = dbConn()->query("INSERT INTO gwPayments.ibox_payment (time, account, amount, transactionID, status, provider, payType, termId, agentId, transactionDate) VALUES 
                                                            ('$nowTime', '$account', '$amount', '$transactionId', 2, '$provider', '$payType', '$termId', '$agentId', '$transactionDate');");
            throw new \Exception("ERROR CREATE PAYMENT" , 2);
        }
        $test = dbConn()->query("INSERT INTO gwPayments.ibox_payment (`time`, account, amount, transactionID, status, provider, payType, termId, agentId, transactionDate) VALUES 
                                                            ('$nowTime', '$account', '$amount', '$transactionId', 0, '$provider', '$payType', '$termId', '$agentId', '$transactionDate');");
        if(!$test) {
            throw new \Exception("ERROR LOG PAYMENT", 2);
        }
       return ['id' => dbConn()->insert_id, 'time'=>$nowTime, 'amount'=>$amount];
    }
    function logger($request,$response) {
        $file_name = $this->logDir . "/ibox_" . date("Y-m-d") . ".txt";
        file_put_contents($file_name, "
====================================START============================================
DATE: ".date("Y-m-d H:i:s")."
HOST: ".$_SERVER['REMOTE_ADDR']."
REQUEST: 
$request
RESPONSE: 
$response
",FILE_APPEND);
    }
}

