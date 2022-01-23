<?php
require __DIR__ . "/../../envPHP/load.php";


$trinity_configs = getGlobalConfigVar('TRINITY');
$trinity = new Meklis\Network\TrinityTV\Api($trinity_configs['partnerID'], $trinity_configs['salt']);
$db = dbConn();


$data = $db->query("SELECT distinct agreement
FROM client_prices pr 
JOIN bill_prices bp on pr.price = bp.id
JOIN trinity_bindings b on b.activation = pr.id 
WHERE name like '%Trinity%'");
while ($d = $data->fetch_assoc()) {
    \envPHP\service\TrinityServiceControl::setServiceState($d['agreement'], 74, 'PAUSE', 9);
    \envPHP\service\TrinityServiceControl::setServiceState($d['agreement'], 74, 'ENABLE', 9);
}