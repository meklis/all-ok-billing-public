<?php

return [
    'snmp-proxy' => [
        'url' => Env()['SNMP_PROXY_ADDR'],
        'use-cache' => false,
        'timeout' => 2,
        'repeats' => 3,
    ],
    'telnet-proxy' => [
        'address' => Env()['TELNET_PROXY_ADDR'],
        'timeout' => 30,
    ],
    'core' => [
        'config' =>   '/www/vendor/meklis/switcher-core/configs',
    ],
    'port_telnet' => 23,
    'port_api' => 55055,
    'proxy_path_config' =>  "/www/configs/proxies.yml",
];