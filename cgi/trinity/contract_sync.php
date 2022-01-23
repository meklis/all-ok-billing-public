#!/usr/bin/php
<?php
require __DIR__ . "/../../envPHP/load.php";

/**
 * Синхронизует спиоск подписок в локальной базе с реальными, в тринити.
 * Так же подписки без привязок переводит на прайс тест, для того, что бы не платить за простаивающие подписки
 *
 */

$conf = getGlobalConfigVar('TRINITY');
$trinity = new Meklis\Network\TrinityTV\Api($conf['partnerID'], $conf['salt']);
$db = dbConn();

//Sync contracts
$contracts = $trinity->listUsers();
foreach ($contracts->subscribers as $contract=>$contract_data) {
    $status = $db->query("INSERT INTO trinity_contracts (id, subscr_id, subscr_price, subscr_status_id, contract_trinity, devices_count, contract_date)
        VALUES ('$contract', 
                '{$contract_data->subscrid}', 
                '{$contract_data->subscrprice}', 
                '{$contract_data->subscrstatusid}', 
                '{$contract_data->contracttrinity}', 
                '{$contract_data->devicescount}', 
                '{$contract_data->contractdate}') 
        ON DUPLICATE KEY UPDATE 
        subscr_id = '{$contract_data->subscrid}', 
        subscr_price = '{$contract_data->subscrprice}',  
        subscr_status_id = '{$contract_data->subscrstatusid}', 
        contract_trinity = '{$contract_data->contracttrinity}', 
        devices_count = '{$contract_data->devicescount}', 
        contract_date = '{$contract_data->contractdate}';
    ");
    if(!$status) {
        \envPHP\classes\std::msg($db->error);
    }
    echo ".";
    if($contract_data->devicescount == 0 && $contract_data->subscrid != $conf['trinity_disabled_price_id']) {
        echo "\n";
        $data = $trinity->createUser($conf['trinity_disabled_price_id'], $contract);
        echo "Change price on contract $contract to 0UAH, not found users - " . json_encode($data) . "\n";
    }
}
echo "\nUpdate contract finished!\n";
