<?php


namespace envPHP\classes;


use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CoreConnector;
use SwitcherCore\Switcher\Device;

class DlinkSwitcherCore
{
    protected $core;
    protected $sw_ip;
    protected $credentionals = [
        'community' => '',
        'login' => '',
        'password' => '',
    ];
    protected $error;
    protected $core_configuration;
    public function hasError() {
        return $this->error;
    }
    function __construct($ip, $community, $login, $password)
    {
        $config = getGlobalConfigVar('SWITCHER_CORE');
        $this->sw_ip = $ip;
        $this->credentionals = [
            'community' => $community,
            'login' => $login,
            'password' => $password,
        ];

        $connector = (new \SwitcherCore\Switcher\CoreConnector(Helper::getBuildInConfig()));
        $core = $connector->init(
            Device::init($ip, $community, $login, $password)
                ->set('telnetPort', $config['port_telnet'])
                ->set('mikrotikApiPort',$config['port_api'] )
                ->set('telnetTimeout', 10)
        );
        $this->core = $core;
    }
    function getSystemInfo() {
            return $this->core->action('system');
    }
    function getPortFullInfo($port_num = 0) {
        $response = [];
        $cable_diag = null;
        $rmon = null;
        $link_info = $this->core->action('link_info', ['interface'=>$port_num]);
        $counters = $this->core->action('counters', ['interface'=>$port_num]);
        $fdb = $this->core->action('fdb', ['interface'=>$port_num]);
        $vlan = $this->core->action('vlans_by_port', ['interface'=>$port_num]);

        try {
            $cable_diag = $this->core->action('cable_diag', ['interface'=>$port_num]);
        } catch (\Throwable $e) {}
        try {
            $rmon = $this->core->action('rmon', ['interface'=>$port_num]);
        } catch (\Throwable $e) {}


        foreach ($link_info as $link) {

            $response[$link['interface']['id']]['port'] = $link['interface']['id'];
            $response[$link['interface']['id']]['type'] = $link['type'];
            $response[$link['interface']['id']]['description'] = $link['description'];
            $response[$link['interface']['id']]['fdb'] = [];
            $response[$link['interface']['id']]['cable_diag'] = [];
            $response[$link['interface']['id']]['crc_align_errors'] = 'U';
            $response[$link['interface']['id']]['oversize_pkts'] = 'U';
            $response[$link['interface']['id']]['collisions'] = 'U';
            $response[$link['interface']['id']]['undersize_pkts'] = 'U';
            $response[$link['interface']['id']]['jabber'] = 'U';
            $response[$link['interface']['id']]['fragments'] = 'U';
            $response[$link['interface']['id']]['vlans'] = 'U';

            $response[$link['interface']['id']]['link'][$link['medium_type']] = [
                'medium_type' => $link['medium_type'],
                'oper_status' => $link['oper_status'],
                'admin_state' => $link['admin_state'],
                'nway_status' => $link['nway_status'],
            ];
        }
        foreach ($counters as $d) {
            $response[$d['interface']['id']]['in_octets'] = $d['hc_in_octets'];
            $response[$d['interface']['id']]['out_octets'] = $d['hc_out_octets'];
        }
        foreach ($vlan as $d) {
            $vlans = [];
            foreach ($d['untagged'] as $v) {
                $vlans[] = [
                    'type' => 'U',
                    'id' => $v['id'],
                    'name' => $v['name'],
                ];
            }
            foreach ($d['tagged'] as $v) {
                $vlans[] = [
                    'type' => 'T',
                    'id' => $v['id'],
                    'name' => $v['name'],
                ];
            }
            foreach ($d['forbidden'] as $v) {
                $vlans[] = [
                    'type' => 'F',
                    'id' => $v['id'],
                    'name' => $v['name'],
                ];
            }
            $response[$d['interface']['id']]['vlan'] = $vlans;
        }
        foreach ($fdb as $d) {
            $response[$d['interface']['id']]['fdb'][] = $d;
        }
        if($rmon) {
            foreach ($rmon as $d) {
                $response[$d['interface']['id']]['crc_align_errors'] = $d['ether_stats_crc_align_errors'];
                $response[$d['interface']['id']]['oversize_pkts'] = $d['ether_stats_oversize_pkts'];
                $response[$d['interface']['id']]['collisions'] = $d['ether_stats_collisions'];
                $response[$d['interface']['id']]['undersize_pkts'] = $d['ether_stats_undersize_pkts'];
                $response[$d['interface']['id']]['jabber'] = $d['ether_stats_jabber'];
                $response[$d['interface']['id']]['fragments'] = $d['ether_stats_fragments'];
            }
        }
        if($cable_diag) {
            foreach ($cable_diag as $d) {
                if (isset($response[$d['interface']['id']]['link']['Cooper'])) {
                    $response[$d['interface']['id']]['cable_diag'] = $d['pairs'];
                }
            }
        }
        unset($response[0]);
        return $response;
    }
    function convertSize($bytes) {
        $out = '';
        if ($bytes < 1024) $out = $bytes . "B";
        if ($bytes == 0) $out = $bytes;
        if ($bytes > 1024) $out = round($bytes/1024, 2) . "K";
        if ($bytes > 1048576) $out = round($bytes/1048576, 2) . "M";
        if ($bytes > 1073741824) $out = round($bytes/1073741824, 2) . "G";
        if ($bytes > 1099511627776) $out = round($bytes/1099511627776, 2) . "T";
        return $out;
    }
}
