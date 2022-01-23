<?php

use envPHP\classes\std;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CoreConnector;

require __DIR__ . '/../../envPHP/load.php';


dbConnPDO()->query("UPDATE walker_fdb SET actualized=0");
$data = dbConnPDO()->query("SELECT 
ip
FROM equipment ");
$started_at = date("Y-m-d H:i"). ":00";

$swm = new \envPHP\MultiSwitcher\MultiSwitcher();
foreach ($data->fetchAll() as $d) {
    $swm->add('fdb', $d['ip']);
    std::msg("Added device {$d['ip']}");
}
std::msg("Start walking....");
$resp = $swm->process()->getResponse();
foreach ($resp as $switchIp=>$d) {
    if(!isset($d['fdb'])) {
        std::msg("Device $switchIp not returned FDB");
        \envPHP\classes\Logger::get()->withName('fdb-grabber')->warning("Device $switchIp not returned FDB");
        continue;
    }
    if(!isset($d['fdb']['data']) || count($d['fdb']['data']) == 0) {
        std::msg("Device $switchIp returned 0 rows of FDB table");
        \envPHP\classes\Logger::get()->withName('fdb-grabber')->warning("Device $switchIp returned 0 rows of FDB table");
        continue;
    }
    $count = count($d['fdb']['data']);
    std::msg("Found {$count} rows on device $switchIp");
    foreach ($d['fdb']['data'] as $row) {
        dbConnPDO()->prepare("INSERT INTO walker_fdb 
            (switch, port, mac, vlan_id, start_at, stop_at, actualized) 
            VALUES (?, ?, ?, ?, ?, null, 1)
            ON DUPLICATE KEY UPDATE actualized = 1, stop_at = null
        ")->execute([
            $switchIp,
            $row['port'],
            $row['mac'],
            $row['vlan_id'],
            $started_at
        ]);
    }
}

dbConnPDO()->query("UPDATE walker_fdb SET stop_at = '{$started_at}' WHERE actualized = 0");