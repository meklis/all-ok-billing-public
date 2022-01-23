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


$data = $db->query("SELECT distinct agreement
FROM client_prices pr 
JOIN bill_prices bp on pr.price = bp.id
JOIN trinity_bindings b on b.activation = pr.id 
WHERE name like '%Trinity%'");
while ($d = $data->fetch_assoc()) {
    \envPHP\service\TrinityServiceControl::setServiceState($d['agreement'], 74, 'PAUSE', 9);
    \envPHP\service\TrinityServiceControl::setServiceState($d['agreement'], 74, 'ENABLE', 9);
}