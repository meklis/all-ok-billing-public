<?php

use \envPHP\classes\std;

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

//Get unknown binding list
$unknown_bindings = $db->query("SELECT b.* 
FROM tmp.trinity_bindings b 
LEFT JOIN service.trinity_bindings sb on sb.mac = b.mac and sb.uuid = b.uuid 
WHERE sb.id is null and b.uuid not like 'http%' ORDER BY 1,2,3,4,5,6,7,8  ")->fetch_all(MYSQLI_ASSOC);


//Delete old bindings
foreach ($unknown_bindings as $binding) {
   $resp = $trinity->deleteDevice($binding['localid'], $binding['mac'], $binding['uuid']);
   \envPHP\classes\std::msg("DELETE BINDING MAC: {$binding['mac']}, UUID: {$binding['uuid']}{$binding['localid']} with resp - " . json_encode($resp));
}


$localId = 5000;
//Create new contracts for replacing
$bindingLimit =  4;

foreach ($unknown_bindings as $bind) {
    if($bindingLimit >= 4) {
        $localId++;
        std::msg("Create new contract with local id $localId");
        //Create contract
        std::msg(json_encode($trinity->createUser(1516, $localId)));
        $bindingLimit = 0;
    }

    //Add binding
    std::msg("Add binding {$bind['mac']} {$bind['uuid']} to localId $localId");
    std::msg(json_encode($trinity->addDevice($localId, $bind['mac'], $bind['uuid'])));
    $bindingLimit++;
}