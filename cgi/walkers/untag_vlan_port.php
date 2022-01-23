#!/usr/bin/php
<?php

use envPHP\classes\std;
use SwitcherCore\Modules\Helper;

require('/www/envPHP/load.php');
require (__DIR__ . '/_help_funcs.php');

$sql = dbConn();


$sql->query("DELETE FROM walkers_untag_ports");
$data = $sql->query("
SELECT 
ip, a.login, a.`password`, a.community
FROM equipment e 
JOIN equipment_models m on m.id = e.model
JOIN equipment_access a on a.id = e.access
ORDER BY 1 ");

$started_at = date("Y-m-d H:i"). ":00";

while ($d = $data->fetch_assoc()) {
    try {
        $config = getGlobalConfigVar('SWITCHER_CORE');
        $connector = (new \SwitcherCore\Switcher\CoreConnector(Helper::getBuildInConfig()));
        $core = $connector->init(
            \SwitcherCore\Switcher\Device::init($d['ip'], $d['community'], $d['login'], $d['password'])
                ->set('telnetPort', $config['port_telnet'])
                ->set('mikrotikApiPort',$config['port_api'])
                ->set('telnetTimeout', 10)
                ->set('snmpTimeoutSec', 3)
                ->set('snmpRepeats', 3)
        );
        $vlans = $core->action('vlans_by_port');
        $ports = [];
        echo "Test {$d['ip']} {$d['community']}\n";
        foreach ($vlans as $dat) {
            if(count($dat['tagged']) == 0 && count($dat['forbidden']) == 0 && count($dat['untagged']) != 0) {
              $port = $dat['port'];
              $vlan = $dat['untagged'][0]['id'];
              echo "INSERT INTO walkers_untag_ports (switch, port, vlan_id) VALUES ('{$d['ip']}', $port, $vlan)" . "\n";
              if(!$sql->query("INSERT INTO walkers_untag_ports (switch, port, vlan_id) VALUES ('{$d['ip']}', $port, $vlan)")) {
                  throw new \Exception($sql->error);
              };
            }

        }
    } catch (Exception $e) {
        std::msg("ERROR CONNECT TO {$d['ip']} with message: {$e->getMessage()}");
    }
}

