<?php

namespace envPHP\classes;

use meklis\network\Telnet;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\Core;
use SwitcherCore\Switcher\CoreConnector;
use SwitcherCore\Switcher\Device;
use SwitcherCore\Switcher\Objects\TelnetLazyConnect;

/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 05.08.2017
 * Time: 0:36
 */
class AccessSwitcherCore implements Idlink
{
    //const TELNET_PORT = 23;
    //const ERR_TELNET = 1;
    /**
     * @var Core
     */
    protected $connection;
    protected $ip;
    protected $community;
    protected $login;
    protected $password;
    protected $mustSave = false;

    function __construct($ip, $community, $login, $password)
    {
        $this->ip = std::checkParam('ip', $ip);
        $this->community = $community;
        $this->login = $login;
        $this->password = $password;

    }

    private function getTelnet()
    {
        std::msg("Open connection with switch: {$this->ip}");
        if ($this->connection) return $this->connection;
        $config = getGlobalConfigVar('SWITCHER_CORE');
        $connector = (new \SwitcherCore\Switcher\CoreConnector(Helper::getBuildInConfig()));
        $core = $connector->init(
            Device::init($this->ip, $this->community, $this->login, $this->password)
                ->set('telnetPort', $config['port_telnet'])
                ->set('mikrotikApiPort',$config['port_api'] )
                ->set('telnetTimeout', 10)
        );
        $core->getContainer()->get(TelnetLazyConnect::class)->disableMagicControl();
        $this->connection = $core;
        return $this->connection;
    }

    public function getPortsNum()
    {
        return $this->getTelnet()->getDeviceMetaData()['ports'];
        //return 28;
    }

    public function getVlans()
    {
        $vlans = $this->getTelnet()->action('vlans');
        $returned = [];
        foreach ($vlans as $vlan) {
            $returned[$vlan['name']] = $vlan['id'];
        }
        return $returned;
    }

    public function setUntagVidOnPort($port, $vlanId)
    {
        //Перед установкой новых вланов - необходимо удалить старые с порта
        $vlans = $this->getVlanIdsOnPort($port);
        std::msg("Defined uptag vlans on port for del: " . json_encode($vlans));
        foreach ($vlans as $vlanId4Del) {
            $res = $this->getTelnet()->action('ctrl_vlan_port', [
                'id' => $vlanId4Del,
                'interface' => $port,
                'action' => 'delete',
            ]);
            std::msg("Result of vlan $vlanId deleting:" . json_encode($res));
        }

        $this->getTelnet()->action('ctrl_vlan_port', [
            'id' => $vlanId,
            'interface' => $port,
            'type' => 'untagged',
            'action' => 'add',
        ]);
        $this->mustSave = true;
    }

    public function setDescription($port, $description)
    {
        $telnet = $this->getTelnet();
        $telnet->action("ctrl_port_descr", [
            'interface' => $port,
            'description' => $description,
        ]);
        $this->mustSave = true;
        return true;
    }

    protected function getVlanIdsOnPort($port)
    {

        $data = $this->getTelnet()->action('vlans_by_port', [
            'interface' => $port,
        ]);
        if (count($data) > 1) {
            std::msg("SwitcherCore GetVlans on port returned: " . json_encode($data));
            throw new \Exception("Модуль вернул неккоректное значение для порта $port");
        } else if (count($data) == 0) {
            throw new \InvalidArgumentException("Указан неккоректный порт - $port");
        } else if (count($data[0]['tagged']) != 0) {
            throw new \LogicException("Порт с тагированным вланом нельзя использовать для работы с абонентом");
        }
        $vlans = [];
        foreach ($data[0]['untagged'] as $untag) {
            $vlans[] = $untag['id'];
        }
        return $vlans;


    }

    function __destruct()
    {
        $this->saveConfig();
    }

    public function saveConfig()
    {
        if ($this->mustSave && $this->connection) {
            $this->getTelnet()->action("save_config");
            $this->mustSave = false;
        }
    }
}
