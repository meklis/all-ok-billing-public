<?php

use envPHP\service\OmoControl;
use envPHP\service\TrinityServiceControl;

require_once __DIR__ . "/../envPHP/load.php";
$form = [
    'agreement'=>0,
    'activation'=>0,
    'binding'=>0,
    'employee'=>0,
    'ip'=>'',
    'mac'=>'',
    'switch'=>'',
    'port'=>'',
    'price'=>0,
    'TOKEN_ID'=>'',
    'id'=>"NULL",
    'code'=>"NULL",
    'response'=>"",
    'money'=>0,
    'payment'=>0,
    'paymentType'=>'custom',
    'payment_id'=>'unknown',
    'comment'=>'',
    'debug_info'=>'',
    'reason' => 0,
    'phone' => "",
    'dest_time' => '',
    'uuid' => '',
    'state' => '',
    'real_ip' => false,
    'allow_static' => false,
    'group' => '',
    'data'=>'',
];


header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));

//std::msg(json_encode($_SERVER, JSON_PRETTY_PRINT));
//std::msg(json_encode($_REQUEST, JSON_PRETTY_PRINT));
envPHP\classes\std::Request($form);
//Проверка доступов

$CONF = getGlobalConfigVar('API');

if($CONF['allow_tokens'] && $form['TOKEN_ID'] && envPHP\classes\memory::get(md5($_SERVER['REMOTE_ADDR'] . $form['TOKEN_ID']))) {
    envPHP\classes\std::msg("Authorized by token");
} elseif (in_array($_SERVER['REMOTE_ADDR'],$CONF['trusted_ips'])) {
    envPHP\classes\std::msg("Authorized by IP");
} elseif (isset($CONF['proxy']) && $CONF['proxy']['enable'] && $_SERVER['REMOTE_ADDR'] === $CONF['proxy']['proxyIP'] && in_array($_SERVER[$CONF['proxy']['realIpHeader']],$CONF['trusted_ips'])) {
    envPHP\classes\std::msg("Authorized by IP OVER PROXY");
} else {
    envPHP\classes\std::Response("Access denied for ip: {$_SERVER['REMOTE_ADDR']}", 403);
}




//Читаем RequstURI
//Ручной роутер
$response = false;
@list(,$className, $methodName) = @explode("/",$_SERVER['REQUEST_URI']);
$methodName = @explode("?",$methodName)[0];
envPHP\classes\std::msg("CLASS: $className");
envPHP\classes\std::msg("METHOD: $methodName");
envPHP\classes\std::msg(json_encode($form));
try {
    if($className == 'activation') {
        if($methodName == 'frost') $response = envPHP\service\activations::frost($form['activation'], $form['employee']);
        if($methodName == 'frostWithCheckBalance') $response = envPHP\service\activations::frostWithCheckBalance($form['activation'], $form['employee']);
        if($methodName == 'changePrice') $response = envPHP\service\activations::changePrice($form['activation'], $form['price'], $form['employee']);
        if($methodName == 'defrost')  {
                $response = envPHP\service\activations::defrost($form['activation'], $form['employee']);
        }
        if($methodName == 'deactivate')   $response = envPHP\service\activations::deactivate($form['activation'], $form['employee']);
        if($methodName == 'activate')  $response = envPHP\service\activations::activate($form['agreement'], $form['price'], $form['employee']);
        if($methodName == 'getActiveInetPrices')  $response = envPHP\service\activations::getActivePrices($form['agreement']);
    } elseif ($className == 'binding') {
        if($methodName == 'add')  $response = envPHP\service\bindings::add($form['activation'], $form['ip'], $form['mac'],$form['switch'],$form['port'],$form['employee'], $form['real_ip'], $form['allow_static']);
        if($methodName == 'edit') $response = envPHP\service\bindings::edit($form['binding'], $form['ip'], $form['mac'],$form['switch'],$form['port'],$form['employee']);
        if($methodName == 'delete')  $response = envPHP\service\bindings::delete($form['binding']);
        if($methodName == 'get')  $response = envPHP\service\bindings::get($form['binding']);
    } elseif ($className == 'shedule') {
        if($methodName == 'add') $response = envPHP\service\shedule::add($form['generator'], $form['method'], json_decode($form['request']), $form['startTime']);
        if($methodName == 'update') $response = envPHP\service\shedule::update($form['id'], $form['code'], json_decode($form['response'], true));
        if($methodName == 'get') {
            $response = envPHP\service\shedule::get();
            if($response === false) {
               throw new \Exception("New tasks not found", 204);
            }
        }
    } elseif ($className == 'payment') {
        if($methodName == 'add') $response = envPHP\service\payment::add($form['agreement'],$form['money'], $form['paymentType'], $form['payment_id'],$form['comment'],$form['debug_info']);
        if($methodName == 'delete') $response = envPHP\service\payment::delete($form['payment']);
    } elseif ($className == "notification") {
        if($methodName == 'sendSMS') $response = envPHP\service\notification::sendSMS($form['phone'],$form['message']);
    } elseif ($className == "creditPeriod") {
        if($methodName == 'enable') $response = envPHP\service\creditPeriod::enableCredit($form['agreement'],$form['employee']);
        if($methodName == 'disable') $response = envPHP\service\creditPeriod::disableCredit($form['agreement'],$form['employee']);
        if($methodName == 'enableWithDefrost') $response = envPHP\service\creditPeriod::enableCreditWithDefrost($form['agreement'],$form['employee']);
    } elseif ($className == "question") {
        if($methodName == 'create') $response = envPHP\service\Question::create(
            (new \envPHP\structs\Client())->fillById($form['agreement']),
            $form['reason'],
            (new \envPHP\structs\Employee())->fillById($form['employee']),
            $form['phone'],
            $form['comment'],
            $form['dest_time']
            );
    } elseif ($className == "trinity") {
        if($methodName == 'device_add') $response = envPHP\service\TrinityControl::reg($form['activation'], $form['employee'], $form['mac'], $form['uuid']);
        if($methodName == 'device_add_by_code') $response = envPHP\service\TrinityControl::regByCode($form['activation'], $form['code'], $form['employee']);
        if($methodName == 'device_delete') $response = envPHP\service\TrinityControl::deregBindById($form['binding']);
     //   if($methodName == 'add') $response = envPHP\service\TrinityServiceControl::regDeviceByMAC($form['activation'], $form['employee'], $form['mac'], $form['uuid']);
      //  if($methodName == 'add_by_code') $response = envPHP\service\TrinityServiceControl::regDeviceByCode($form['activation'], $form['code'], $form['employee']);
      //  if($methodName == 'delete') $response = envPHP\service\TrinityServiceControl::deleteDevice($form['binding']);
      //  if($methodName == 'service_control') $response = envPHP\service\TrinityServiceControl::setServiceState($form['agreement'], $form['price'], $form['state'], $form['employee']);
      //  if($methodName == 'get_service') $response = envPHP\service\TrinityServiceControl::getServiceStatus($form['agreement']);
      //  if($methodName == 'get_devices') $response = envPHP\service\TrinityServiceControl::getRegisteredDevices($form['agreement']);
    } elseif ($className == "omo") {
        $omo = new OmoControl();
        if($methodName == 'phone_add') $response = $omo->addPhone($form['agreement_id'], $form['phone']);
        if($methodName == 'phone_delete') $response = $omo->deletePhone($form['phone'],$form['agreement_id']);
        if($methodName == 'device_share') $response = $omo->shareDevice($form['user_id'],$form['device_id']);
        if($methodName == 'device_revoke') $response = $omo->revokeDevice($form['user_id'],$form['device_id']);
    } elseif ($className == 'event') {
        \envPHP\EventSystem\EventRepository::getSelf()->notify($form['group'], json_decode($form['data']));
        $response = 'event created';
    }
    if(!$response) {
        throw  new Exception("Class not exists or denied for api - {$className}.{$methodName}", 403);
    }
} catch (Exception $e) {
    envPHP\classes\std::msg("MESSAGE: ".$e->getMessage());
    envPHP\classes\std::msg("FILE: ".$e->getFile());
    envPHP\classes\std::msg("LINE: ".$e->getLine());
    envPHP\classes\std::msg("TRACE: \n".$e->getTraceAsString());
    $code = $e->getCode() != 0?$e->getCode():403;
    envPHP\classes\std::Response($e->getMessage(), $code);
}
envPHP\classes\std::Response($response);


