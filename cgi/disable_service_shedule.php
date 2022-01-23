#!/usr/bin/php



<?php
require('/www/envPHP/load.php');
$pdo = dbConnPDO();
$data = $pdo->query("SELECT p.id, bp.work_type
            FROM client_prices p 
            JOIN clients c on c.id = p.agreement and c.enable_credit = 1 
            JOIN bill_prices bp on bp.id = p.price 
            WHERE ifnull(p.disable_day_static, p.disable_day) < NOW()
            and p.time_stop is null 
");
$disableDateQuestion = (new DateTime())->format('Y-m-d 10:00:00');
$disableDateService = (new DateTime())->format('Y-m-d 10:00:00');
foreach ($data->fetchAll() as $d) {
    \envPHP\service\shedule::add(18, 'activation/frostWithCheckBalance', [
        'employee' => getGlobalConfigVar('BASE')['billing_user_id'],
        'activation' => $d['id'],
    ], $d['work_type'] == 'question' ? $disableDateQuestion : $disableDateService );
    \envPHP\classes\std::msg("Added frost task to shedule for activation: {$d['id']}");
}
