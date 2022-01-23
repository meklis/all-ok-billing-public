<?php

namespace envPHP\classes;

use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CoreConnector;
use SwitcherCore\Switcher\Core;
use SwitcherCore\Switcher\Device;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 15.08.2017
 * Time: 11:23
 */
class CoreSwitcherCore implements Imikrotik
{
    protected $ip = "";
    protected $community;
    protected $login;
    protected $password;
    /**
     * @var Core[]
     */
    static protected $connections = [];

    function __construct($ip, $community, $login, $password)
    {
        $this->ip = std::checkParam('ip', $ip);
        $this->community = $community;
        $this->login = $login;
        $this->password = $password;
    }

    function addArpIp($mac, $ip, $interface, $agreement)
    {
        try {
            $this->getAPI()->action('ctrl_static_arp', [
                'action' => 'add',
                'ip' => $ip,
                'vlan_name' => $interface,
                'mac' => $mac,
                'comment' => $agreement,
            ]);
        } catch (\Exception $e) {
            std::msg("Error work with switcherCore: {$e->getMessage()}");
            throw new \Exception($e->getMessage());
        }
        std::msg(__METHOD__ . "-> addARP $mac, $ip, $interface, $agreement ");
        return true;
    }

    function addStaticLease($mac, $ip, $dhcp_server, $agreement)
    {

        try {
            $this->getAPI()->action('ctrl_static_lease', [
                'action' => 'add',
                'ip' => $ip,
                'dhcp_server' => $dhcp_server,
                'mac' => $mac,
                'comment' => $agreement,
            ]);
        } catch (\Exception $e) {
            std::msg("Error work with switcherCore: {$e->getMessage()}");
            throw new \Exception($e->getMessage());
        }
        std::msg(__METHOD__ . "-> addStaticLease $mac, $ip, $dhcp_server, $agreement ");
        return true;
    }

    function delArpIp($ip, $interface = "")
    {
        try {
            std::msg("Try delete ARP with ip=$ip, interface=$interface");
            if ($interface) {
                std::msg("[RouterOS-API Response] remove by IP" . json_encode($this->getAPI()->action('ctrl_static_arp', [
                        'action' => 'remove',
                        'ip' => $ip,
                        'vlan_name' => $interface,
                    ])));
            } else {
                std::msg("[RouterOS-API Response] remove by IP" . json_encode($this->getAPI()->action('ctrl_static_arp', [
                        'action' => 'remove',
                        'ip' => $ip,
                    ])));
            }
        } catch (\Exception $e) {
            std::msg("Error work with switcherCore: {$e->getMessage()}");
            if ($e->getCode() != 404) {
                throw new \Exception($e->getMessage());
            }
        }
        std::msg(__METHOD__ . "-> delARP  $ip");
        return true;
    }

    function delStaticLease($mac, $ip)
    {
        try {
            std::msg("Try delete static lease with parameters ip=$ip");
            std::msg("[DEL DHCP STATIC LEASE] " . json_encode($this->getAPI()->action('ctrl_static_lease', [
                    'action' => 'remove',
                    'ip' => $ip,
                ])));
        } catch (\Exception $e) {
            std::msg("Error work with switcherCore: {$e->getMessage()}");
        }
        try {
            std::msg("Try delete static lease with parameters mac=$mac");
            std::msg("[DEL DHCP STATIC LEASE] " . json_encode($this->getAPI()->action('ctrl_static_lease', [
                    'action' => 'remove',
                    'mac' => $mac,
                ])));
        } catch (\Exception $e) {
            std::msg("Error work with switcherCore: {$e->getMessage()}");
        }
        std::msg(__METHOD__ . "-> deleted Static Lease $mac-$ip");
        return true;
    }

    function setQueueSpeed($ip, $speed = 0, $comment = "")
    {
        try {
            std::msg("Try add simple queue for IP $ip with speed {$speed}M");
            $this->getAPI()->action('simple_queue_ctrl', [
                'action' => 'add',
                'name' => $ip,
                'target' => $ip . "/32",
                'max-limit' => "{$speed}M/{$speed}M",
                'comment' => $comment,
            ]);
        } catch (\Exception $e) {
            std::msg("Error work with switcherCore: {$e->getMessage()}");
            throw new \Exception($e->getMessage());
        }
        std::msg(__METHOD__ . "Simple queue success added for ip $ip with speed {$speed}M");
        return true;
    }

    function removeQueueSpeed($ip, $speed = 0)
    {
        try {
            std::msg("Try remove simple queue for IP $ip with speed {$speed}M");
            $this->getAPI()->action('simple_queue_ctrl', [
                'action' => 'remove',
                'name' => $ip,
            ]);
        } catch (\Exception $e) {
            std::msg("Error work with switcherCore: {$e->getMessage()}");
            if ($e->getCode() != 404) {
                throw new \Exception($e->getMessage());
            }
        }
        std::msg(__METHOD__ . "Simple queue success removed for ip $ip with speed {$speed}M");
        return true;
    }

    function addToAddressList($listName, $ip, $comment = "")
    {
        try {
            std::msg("Try add $ip to address list $listName with comment $comment");
            $this->getAPI()->action('address_list_ctrl', [
                'action' => 'add',
                'name' => $listName,
                'address' => $ip,
                'comment' => $comment,
            ]);
        } catch (\Exception $e) {
            std::msg("Error add $ip to address list $listName, error from switcherCore: {$e->getMessage()}");
            throw new \Exception($e->getMessage());
        }
        std::msg(__METHOD__ . "Success added $ip to list $listName with comment $comment");
        return true;
    }

    function removeFromAddressList($addressList, $ip)
    {
        try {
            std::msg("Try delete $ip from address list $addressList");
            $this->getAPI()->action('address_list_ctrl', [
                'action' => 'remove',
                'address' => $ip,
                'name' => $addressList,
            ]);
        } catch (\Exception $e) {
            std::msg("error delete $ip from address list $addressList: {$e->getMessage()}");
            if ($e->getCode() != 404) {
                throw new \Exception($e->getMessage());
            }
        }
        std::msg(__METHOD__ . " success delete $ip from address list $addressList");
        return true;
    }

    /**
     * @return Core
     */
    protected function getAPI()
    {
        if (isset(self::$connections[$this->ip])) return self::$connections[$this->ip];
        $config = getGlobalConfigVar('SWITCHER_CORE');
        try {
            $connector = (new \SwitcherCore\Switcher\CoreConnector(Helper::getBuildInConfig()));
            $core = $connector->init(
                Device::init($this->ip, $this->community, $this->login, $this->password)
                    ->set('telnetPort', $config['port_telnet'])
                    ->set('mikrotikApiPort', $config['port_api'])
                    ->set('telnetTimeout', 10)
            );
            self::$connections[$this->ip] = $core;
        } catch (\Exception $e) {
            std::msg("Ошибка работы с модулем SwitcherCore: {$e->getMessage()}");
            $this->error = $e;
            throw new \Exception("Ошибка работы с модулем SwitcherCore: {$e->getMessage()}");
        }
        return self::$connections[$this->ip];
    }
}

