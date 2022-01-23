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

foreach ($contracts->subscribers as $contract=>$contract_data) {
    //Check contract if exist
    if($db->query("SELECT id FROM trinity_contracts WHERE id = '{$contract}'")->num_rows == 0) {
        \envPHP\classes\std::msg("Contract $contract not found in database");
        continue;
    }
    if($db->query("SELECT id from clients WHERE agreement = '{$contract}'")->num_rows == 0) {
        \envPHP\classes\std::msg("Agreement $contract not found in database");
        continue;
    }

    $agreeId = $db->query("SELECT id from clients WHERE agreement = '{$contract}'")->fetch_assoc()['id'];
    //Регистрируем активацию
    try {
    $actId = \envPHP\service\TrinityServiceControl::setServiceState($agreeId, getGlobalConfigVar('TRINITY')['services_associate']['maximal']['local'],'ENABLE', 9);
    } catch (Exception $e) {
        $actId = \envPHP\service\TrinityServiceControl::getServiceStatus($agreeId)['activation'];
        \envPHP\classes\std::msg($e->getMessage());
    }

    //Регистрация активации
    $bindings = $trinity->listDevices($contract);
    foreach ($bindings->devices as $bind) {
        if(preg_match('/http/', $bind->uuid)) {
            continue;
        }
        $test = dbConn()->query("SELECT * FROM trinity_bindings WHERE mac = '{$bind->mac}' or uuid = '{$bind->uuid}'")->num_rows;
        if($test == 0) {
            dbConn()->query("INSERT INTO trinity_bindings (created, activation, contract, mac, uuid, employee) 
                VALUES
            (NOW(), '{$actId}', '{$contract}', '{$bind->mac}', '{$bind->uuid}', '9');");
        }
    }
}