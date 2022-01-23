#!/usr/bin/php
<?php

use envPHP\classes\std;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CoreConnector;
use SwitcherCore\Switcher\Device;

require('/www/envPHP/load.php');
require __DIR__ . '/_help_funcs.php';
$sql = dbConn();
$cfg = getGlobalConfigVar('SWITCHER_CORE');

$sql->query("DELETE FROM walkers_arps");
$data = $sql->query("SELECT 
ip, a.login, a.`password`, a.community
FROM equipment e 
JOIN equipment_models m on m.id = e.model
JOIN equipment_access a on a.id = e.access");

$started_at = date("Y-m-d H:i"). ":00";
while ($d = $data->fetch_assoc()) {
    try {
        $config = getGlobalConfigVar('SWITCHER_CORE');
        $connector = (new \SwitcherCore\Switcher\CoreConnector(Helper::getBuildInConfig()));
        $core = $connector->init(
            Device::init($d['ip'], $d['community'], $d['login'], $d['password'])
                ->set('telnetPort', $config['port_telnet'])
                ->set('mikrotikApiPort',$config['port_api'] )
                ->set('telnetTimeout', 10)
        );
        //Check methods
        $modules = getModulesList($core);
        $count_writed = 0;
        if(in_array('arp_info', $modules)) {
            $arps = $core->action('arp_info');
            foreach ($arps as $arp) {
                if(!$arp['mac']) continue;
                if(!$arp['ip']) continue;
                if(!$arp['vlan_id']) continue;
                if(!$sql->query("INSERT INTO walkers_arps (created_at, router, ip, mac, vlan) VALUES 
                    ('$started_at', '{$d['ip']}', '{$arp['ip']}', '{$arp['mac']}', '{$arp['vlan_id']}')")){
                    throw new \Exception("SQL ERR: {$sql->error}");
                };
                $count_writed++;
            }
            std::msg("Success writed arps from {$d['ip']}. Found - ". count($arps) . ", writed - $count_writed");
            continue;
        }
        std::msg("NOT found arp module for device {$d['ip']}");
    } catch (Exception $e) {
        std::msg("ERROR CONNECT TO {$d['ip']} with message: {$e->getMessage()}");
    }
}
