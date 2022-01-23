<?php

namespace envPHP\payments;


class gwBank24
{
    protected $logDir = "/www/gwLog";
    protected $provider = null;
    protected $sql = null;
    function __construct()
    {
        $this->provider = new  bank24();
        $this->provider->setCheck([__NAMESPACE__ . '\gwBank24','check'])
                      ->setPayment([__NAMESPACE__ . '\gwBank24','payment'])
                      ->setCancel([__NAMESPACE__ . '\gwBank24','cancel']);
    }
    static function checkAuth($login,$password) {
        $conf = getGlobalConfigVar('GW_PAYMENTS_BANK24');
        if($login === $conf['login'] && $password === $conf['pass']) return true;
        throw new \Exception("INCORRECT LOGIN OR PASSWORD",7);
    }
    function go($raw_http) {
        $response =  $this->provider->parseXML($raw_http);
        $this->logger($raw_http,$response);
        return $response;
    }
    function check($login, $password,  $transactionID, $account,$payElementId) {
        self::checkAuth($login,$password);
        if($payElementId != 1) {
            throw new \Exception("ACCOUNT NOT FOUND", 5);
        }
       $data = dbConn()->query("SELECT Name, Balance FROM service.clients WHERE agreement = '$account' and `status` = 'ENABLED'");
       if($data->num_rows == 0) {
           dbConn()->query("INSERT INTO gwPayments.b24_check (transactionId,  account, status) VALUES ($transactionID,$account,5)");
           throw new \Exception("ACCOUNT NOT FOUND", 5);
       }
       $resp = $data->fetch_assoc();
       $test = dbConn()->query("INSERT INTO gwPayments.b24_check (transactionId,  account, status) VALUES ($transactionID,$account,0)");
       if(!$test) throw  new \Exception(dbConn()->error, 5);
       $resp['id'] = dbConn()->insert_id;
       return $resp;
    }
    function payment($login, $password, $transactionID, $payTimestamp, $payID,$payElementID, $account, $amount, $terminalId) {
            self::checkAuth($login,$password);

            //Проверка, что платеж уже был
            if(dbConn()->query("SELECT id FROM gwPayments.b24_payment WHERE payID = '$payID'")->num_rows != 0) {
                return 0;
            }

            //Проверка аккаунта
            if(!$agreement = dbConn()->query("SELECT id FROM service.clients WHERE agreement = '$account' and `status` = 'ENABLED'")->fetch_assoc()['id']) {
                throw new \Exception("ERROR CREATE PAYMENT - INCORRECT ACCOUNT", 5);
            }
            $amount = $amount/100;

            $test = dbConn()->query("INSERT INTO gwPayments.b24_payment (`account`, `amount`, `payID`, `payTimestamp`, `transactionId`, `terminalid`, `payElementID`, `status`) 
            VALUES ('$account',$amount,'$payID','$payTimestamp','$transactionID','$terminalId','$payElementID',0)");
            if(!$test) {
                $err = dbConn()->error;
                throw new \Exception("ERROR LOG PAYMENT - {$err}", 1);
            }
            $insertId = dbConn()->insert_id;
            try {
                \envPHP\service\payment::add($agreement, $amount, "Bank24", $payID, "Оплата через Bank24");
            } catch (\Exception $e) {
                throw new \Exception("ERROR CREATE PAYMENT", 1);
            }
           return $insertId;
    }
    function cancel($login, $password,$transactionID,$cancelPayID) {
        self::checkAuth($login,$password);
        try {
            \envPHP\service\payment::delete(\envPHP\service\payment::getByBank24PayId($cancelPayID));
        } catch (\Exception $e) {
            throw new \Exception("ERROR CANCEL PAYMENT");
        }
        dbConn()->query("INSERT INTO gwPayments.b24_cancel (payID, transactionID) VALUES ('$cancelPayID','$transactionID')");
        return dbConn()->insert_id;
    }
    function logger($request,$response) {
        $file_name = $this->logDir . "/bank24_" . date("Y-m-d") . ".txt";
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

