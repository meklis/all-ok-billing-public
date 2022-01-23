<?php
require __DIR__ . "/../../envPHP/load.php";

/**
 * Скрипт, который вытягивает список привязок с тринити и записывает все в таблицу tmp.trinity_bindings
 * Работа происходит только с АПИ тринити
 */

$trinity_configs = getGlobalConfigVar('TRINITY');
$trinity = new Meklis\Network\TrinityTV\Api($trinity_configs['partnerID'], $trinity_configs['salt']);
$db = dbConn();


\envPHP\classes\std::msg("Try get contracts from API...");
$contracts = $trinity->listUsers();
$db->query("TRUNCATE TABLE tmp.trinity_bindings");
foreach ($contracts->subscribers as $contract=>$cdata) {
    \envPHP\classes\std::msg("Working with contract {$contract} - " . json_encode($cdata));
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