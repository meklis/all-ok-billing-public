<?php
namespace envPHP\service;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 30.08.2017
 * Time: 10:48
 */
class notification
{
    protected $smsGW = null;
    static protected $smsConfig = [
        'login'=>'',
        'pass'=>'',
        'sender'=>'',
    ];
    protected $mail = null;
    private $type = "SMS";
    protected function __construct($type = "SMS")
    {
        self::$smsConfig = getGlobalConfigVar('TURBO_SMS');
        if($type == "SMS") {
            $this->setSMSGw();
        } else {
            $this->type = $type;
        }
    }
    static function sendSMS($phone, $message, $translit = true) {
       $sms = new self("SMS");
       $phone =  preg_replace("/[^0-9]/", '', $phone);
       if($translit) {
           $message = rus2lat($message);
       }
       $body = Array (
            'sender' => self::$smsConfig['sender'],
            'destination' => '+'.$phone,
            'text' =>$message,
        );
        \envPHP\classes\std::msg(json_encode($body));
        $result = $sms->smsGW->SendSMS ($body);
        $res =  $result->SendSMSResult->ResultArray[0];
        $uid =  $result->SendSMSResult->ResultArray[1];
        if(preg_match('/успешно отправлен/i', $res)) {
            dbConn()->query("INSERT INTO smsOutgoing ( phone, message, uid) VALUES ('{$phone}', '{$message}', '$uid')");
        } else {
            throw new \Exception("Error sending SMS: $res, $uid");
        }
        return ['res'=>$res,'id'=>$uid];
    }
    protected function setSMSGw() {
        $client = new \SoapClient ('http://turbosms.in.ua/api/wsdl.html');
        //Данные для авторизации
        $auth = Array (
            'login' => self::$smsConfig['login'],
            'password' => self::$smsConfig['pass']
        );
        $result = $client->Auth($auth);
        if(!$result) throw new \Exception("Ошибка аунтификации на шлюзе turboSMS");
        $this->smsGW = $client;
    }
}