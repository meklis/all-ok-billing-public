<?php

require __DIR__ . "/../../envPHP/load.php";

/**
 * Скрипт, который удаляет несанкционированных абонентов
 * на основе привязок тринити в сервисе
 */

$trinity_configs = getGlobalConfigVar('TRINITY');
$trinity = new Meklis\Network\TrinityTV\Api($trinity_configs['partnerID'], $trinity_configs['salt']);
$db = dbConnPDO();

\envPHP\classes\std::msg("Try get contracts from API...");
$contracts = $trinity->listUsers();
$db->query("TRUNCATE TABLE tmp.trinity_bindings");
foreach ($contracts->subscribers as $contract=>$cdata) {
    $bindings = $trinity->listDevices($contract);
    foreach ($bindings->devices as $bind) {
        if($db->query("SELECT id FROM trinity_bindings WHERE mac = '{$bind->mac}' and uuid = '{$bind->uuid}'")->rowCount() === 0) {
            //Binding not exist in service, must be deleted
            \envPHP\classes\std::msg("Binding on contract $contract not exist, must be deleted - " . json_encode($bind));
            $trinity->deleteDevice($contract, $bind->mac, $bind->uuid);
        }
    }
}