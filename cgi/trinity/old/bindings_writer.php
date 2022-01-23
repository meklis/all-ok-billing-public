<?php
require __DIR__ . "/../../envPHP/load.php";

/**
 * Этот скрипт работает в одностороннем порядке приводя локальную базу в соотвествие с базой ТРИНИТИ
 *
 * Любое управление (заморозка, разморозка, первичная регистрация через код) производится с помощью АПИ.
 * Данный скрипт необходимо использовать для приведения локальной базы в соответствие с базой тринити
 *
 */

$trinity_configs = getGlobalConfigVar('TRINITY');
$trinity = new Meklis\Network\TrinityTV\Api($trinity_configs['partnerID'], $trinity_configs['salt']);
$db = dbConn();


$contracts = $trinity->listUsers();
$db->query("TRUNCATE TABLE tmp.trinity_bindings");
foreach ($contracts->subscribers as $contract=>$cdata) {
       //Регистрация активации
    $bindings = $trinity->listDevices($contract);
    foreach ($bindings->devices as $bind) {
       if(!$bind->device_id) $bind->device_id = 0;
       if ( !$db->query("INSERT INTO 
    tmp.trinity_bindings 
    (localid, subscrid, subscrprice, subscrstatusid, contracttrinity, devicescount, contractdate, device_id, mac, uuid)
    VALUES 
    (
     {$contract},
     '{$cdata->subscrid}', 
     '{$cdata->subscrprice}', 
     '{$cdata->subscrstatusid}', 
     '{$cdata->contracttrinity}', 
     '{$cdata->devicescount}', 
     '{$cdata->contractdate}', 
     '{$bind->device_id}', 
     '{$bind->mac}', 
     '{$bind->uuid}'
     )
    ")) {
           throw new \Exception($db->error);
       } else {
           \envPHP\classes\std::msg("Writed device {$bind->mac} {$bind->uuid} with local ID $contract");
       }
    }
}
/**
 * stdClass Object
(
[subscrid] => 1516
[subscrprice] => 60
[subscrstatusid] => 0
[contracttrinity] => 822330
[devicescount] => 5
[contractdate] => 2019-04-26
)
stdClass Object
(
[device_id] => 14
[mac] => FCA47AA0866B
[uuid] => 91AA2AD83FF9292F
)
 */