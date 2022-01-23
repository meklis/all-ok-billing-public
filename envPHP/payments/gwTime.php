<?php
namespace envPHP\payments;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 22.08.2017
 * Time: 23:43
 */

class gwTime
{


    protected $logDir = "/www/gwLog";
    protected $provider = null;
    static protected $providersList = [
      '51245'=>1024,
      '51452'=>1023,
    ];
    protected $sql = null;
    function __construct()
    {
        $this->provider = new  timeOSMP();
        $this->provider->setCheck([__NAMESPACE__ . '\gwTime','check'])
                      ->setPayment([__NAMESPACE__ . '\gwTime','payment'])
                      ->setCancel([__NAMESPACE__ . '\gwTime','cancel']);
    }
    function go($request) {
        $response =  $this->provider->createOperation($request);
        $this->logger(json_encode($request, JSON_UNESCAPED_UNICODE ),$response);
        return $response;
    }
    function check($transactionOsmp,$account,$amount, $provider=0) {
        $provider = isset(self::$providersList[$provider]) ? self::$providersList[$provider]: -1;
       $data = dbConn()->query("SELECT Name, Balance FROM service.clients WHERE agreement = '$account' and provider = $provider and `status` = 'ENABLED'");
       if($data->num_rows == 0) {
           dbConn()->query("INSERT INTO gwPayments.time_check (transactionId,  account, status, provider) VALUES ($transactionOsmp,$account,5,$provider)");
           throw new \Exception("ACCOUNT NOT FOUND", 5);
       }
       $resp = $data->fetch_assoc();
       $test = dbConn()->query("INSERT INTO gwPayments.time_check (transactionId,  account, status, provider) VALUES ($transactionOsmp,$account,0, $provider)");
       if(!$test) throw  new \Exception(dbConn()->error, 5);
       $resp['id'] = dbConn()->insert_id;
       return $resp;
    }
    function payment($transactionOsmp, $transactionDate,$account,$amount, $provider= 0) {

        $provider = isset(self::$providersList[$provider]) ? self::$providersList[$provider]: -1;
        //Проверка, что платеж уже был
            if(dbConn()->query("SELECT id FROM gwPayments.time_payment WHERE transactionID = '$transactionOsmp'")->num_rows != 0) {
                return 0;
            }
            //Проверка аккаунта
            if(!$agreement = dbConn()->query("SELECT id FROM service.clients WHERE agreement = '$account' and provider = '$provider' and `status` = 'ENABLED'")->fetch_assoc()['id']) {
                throw new \Exception("ACCOUNT NOT FOUND", 5);
            }
            try {
                \envPHP\service\payment::add($agreement, $amount, "Time", $transactionOsmp, "Оплата через Time");
            } catch (\Exception $e) {
                throw new \Exception("ERROR CREATE PAYMENT" , 1);
            }
            $test = dbConn()->query("INSERT INTO gwPayments.time_payment (account, amount,  payTimestamp, transactionId,  status, provider) 
            VALUES ($account,'$amount','$transactionDate',$transactionOsmp,0, $provider)");
            if(!$test) {
                throw new \Exception("ERROR LOG PAYMENT", 1);
            }
           return dbConn()->insert_id;
    }
    function cancel($transactionOsmp,$transactionOsmpDate,$cancelTrasaction) {
        try {
            \envPHP\service\payment::delete(\envPHP\service\payment::getByTimeId($cancelTrasaction));
        } catch (Exception $e) {
            throw new \Exception("ERROR CANCEL PAYMENT");
        }
        dbConn()->query("INSERT INTO gwPayments.time_cancel (time, payID, transactionID) VALUES ('$transactionOsmpDate','$cancelTrasaction','$transactionOsmp')");
        return dbConn()->insert_id;
    }
    function logger($request,$response) {
        $file_name = $this->logDir . "/time_" . date("Y-m-d") . ".txt";
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

