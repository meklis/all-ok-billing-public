<?php
namespace envPHP\classes;
use envPHP\NetworkCore\SearchIp\DbStore;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Core;
use SwitcherCore\Switcher\Device;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 31.08.2017
 * Time: 14:48
 */
class ping
{
    static function go($ip) {
        try {
            $access = self::getRouterAccessByIp($ip);
        } catch (\Exception $e) {
            return "Ошибка получения информации о роутере: {$e->getMessage()}";
        }
        try {
            $config = getGlobalConfigVar('SWITCHER_CORE');
            $connector = (new \SwitcherCore\Switcher\CoreConnector(Helper::getBuildInConfig()));
            $core = $connector->init(
                Device::init($access['ip'], $access['community'], $access['login'], $access['password'])
                ->set('telnetPort', $config['port_telnet'])
                ->set('mikrotikApiPort',$config['port_api'] )
                    ->set('telnetTimeout', 10)
                );
        } catch (\Exception $e) {
            return "Ошибка подключения к роутеру: {$e->getMessage()}";
        }
        try {
            $arp = $core->action('arp_info', ['ip' => $ip]);
            if(isset($arp[0])) {
                $vlan_id = $arp[0]['vlan_id'];
            } else {
                throw new \Exception("ARP не найден на роутере");
            }
        } catch (\Exception $e) {
            return "Ошибка пинга: {$e->getMessage()}";
        }
        try {
            $data = $core->action('arp_ping', ['ip' => $ip, 'vlan_id' => $vlan_id, 'count' => 4]);
        } catch (\Exception $e) {
            return "Ошибка пинга: {$e->getMessage()}";
        }
        $response = "";
        foreach ($data as $line) {
            foreach ($line as $k=>$v) {
                $response .= "{$k}={$v} ";
            }
            $response .= "\n";
        }
        return $response;
    }
    private static function getRouterAccessByIp($ip) {
        $store = new DbStore;
        return $store->getDeviceAccess($store->getRouterListByIp($ip)[0]);
    }
}