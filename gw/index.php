<?php
require_once __DIR__ . "/../envPHP/load.php";

$form = [

];



envPHP\classes\std::Request($form);
$body = file_get_contents('php://input');

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));
//Проверка доступов
$CONF = getGlobalConfigVar('GW');
if (in_array($_SERVER['REMOTE_ADDR'],$CONF['trusted_ips'])) {
    envPHP\classes\std::msg("Authorized by IP");
} elseif (isset($CONF['proxy']) && $CONF['proxy']['enable'] && $_SERVER['REMOTE_ADDR'] === $CONF['proxy']['proxyIP'] && in_array($_SERVER[$CONF['proxy']['realIpHeader']],$CONF['trusted_ips'])) {
    envPHP\classes\std::msg("Authorized by IP OVER PROXY");
} else {
    envPHP\classes\std::msg("Form: ".json_encode($form));
    envPHP\classes\std::msg("Body: ".json_encode($body));
    $realIp = isset($_SERVER[$CONF['proxy']['realIpHeader']])?$_SERVER[$CONF['proxy']['realIpHeader']]:"0.0.0.0";
    envPHP\classes\std::Response("Access denied for ip: {$_SERVER['REMOTE_ADDR']} ($realIp)", 403);
}

//Читаем RequstURI
//Ручной роутер
$response = false;
$request = @explode("?",$_SERVER['REQUEST_URI'])[0];
@list(,$className, $methodName) = @explode("/",$request);
envPHP\classes\std::msg("CLASS: $className");
envPHP\classes\std::msg("METHOD: $methodName");
envPHP\classes\std::msg("Form: ".json_encode($form));
envPHP\classes\std::msg("Body: ".json_encode($body));
try {
    if($className == 'easypay') {
         $pay = new envPHP\payments\gwEasyPay();
         $response =  $pay->go($body);
    } elseif ($className == 'city24') {
         $pay = new envPHP\payments\gwCity24();
         $response =  $pay->go($body);
    } elseif ($className == 'bank24') {
        $pay = new envPHP\payments\gwBank24();
        $response = $pay->go($body);
    } elseif ($className == 'time') {
        $pay = new envPHP\payments\gwTime();
        $response = $pay->go($form);
    } elseif ($className == 'tyme') {
        $pay = new envPHP\payments\gwTime();
        $response = $pay->go($form);
    } elseif ($className == '2click') {
        $pay = new envPHP\payments\gwTwoClick();
        $response = $pay->go($body);
    } elseif ($className == 'ibox_test') {
        $pay = (new envPHP\payments\gwIBox())->enableDebug();
        $response = $pay->go($form);
    } elseif ($className == 'ibox') {
        $pay = new envPHP\payments\gwIBox();
        $response = $pay->go($form);
    }
    if(!$response) {
        throw  new Exception("Class not exists or denied for api", 403);
    }
} catch (Exception $e) {
    envPHP\classes\std::msg("MESSAGE: ".$e->getMessage());
    envPHP\classes\std::msg("FILE: ".$e->getFile());
    envPHP\classes\std::msg("LINE: ".$e->getLine());
    envPHP\classes\std::msg("TRACE: \n".$e->getTraceAsString());
    $code = $e->getCode() != 0?$e->getCode():403;
    envPHP\classes\std::Response($e->getMessage(),$e->getCode());
}
echo $response;