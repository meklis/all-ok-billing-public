<?php


return [
    'TURBO_SMS' => [
        'login' => Env()['TURBO_SMS_LOGIN'],
        'pass' => Env()['TURBO_SMS_PASS'],
        'sender' => Env()['TURBO_SMS_SENDER'],
    ],
    'TOS' => extraConf('tos'),
    'OFFICIAL_WEB_SITE' => false,
    'PERMISSIONS' => require __DIR__ . '/permissions.php',
    //'PERMISSIONS_API' => extraConf('permissions.api'),
    'BASE' => [
        'provider_name' => Env()['PROVIDER_NAME'],
        'service_addr' => Env()['SERVICE_ADDR'],
        'my_addr' => Env()['MY_ADDR'],
        'api_addr' => Env()['API_ADDR'],
        'sw_addr' => Env()['SW_ADDR'],
        'lc_user_id' => Env()['USER_ID_LC'],
        'system_user_id' => Env()['USER_ID_SYSTEM'],
        'billing_user_id' => Env()['USER_ID_BILLING'],
        'api2_front_addr' => Env()['API2_ADDR'],
        'logDir' => '/var/log/all-ok-billing/api',
        'site_addr' => Env()['SITE_ADDR'],
        'wildcore' => Env()['WILDCORE_URL'],
    ],
    'CREDIT_PERIOD_COUNT_DAYS' => 4,
    'DATABASE' => [
        'db' => [
            'host' => Env()['MYSQL_HOST'],
            'login' => Env()['MYSQL_USER'],
            'pass' => Env()['MYSQL_PASSWORD'],
            'use' => Env()['MYSQL_DATABASE'],
        ]
    ],
    'LOGGER' => [
        'name' => 'sys-billing',
        'path' => '/var/log/all-ok-billing/system.log',
        'level' => \Monolog\Logger::DEBUG,
    ],
    'LOGGER_API2' => [
        'name' => 'api',
        'path' => '/var/log/all-ok-billing/api.log',
        'level' => \Monolog\Logger::DEBUG,
    ],
    'API_TRUSTED_IPS' => require __DIR__ . '/api.trusted_ips.php',
    'PARENT_PRICES' => [
        'whiteIP' => [ //Тип ограничения
            'prices' => [46], //ID прайсов, на которые установлены ограничения
            'parent' => [42, 43, 44, 45, 51, 52, 53, 54, 55, 56, 68, 70,80,81,82,83,84,85,86,98,99,100,101] //Родительские прйсы, за которым должен цеплятся
        ],
    ],
    'SWITCHER_CORE' => extraConf('switcher_core'),
    'PICTURES' => [
        'system_path' => '/www/service/res/uploads/',
        'http_path' => '/res/uploads/',
    ],
    'SMS_TEXTS' => extraConf('sms_texts'),
    'DEFAULT_SMS_TEXT' => extraConf('default_sms_text'),

    'EQUIPMENT_PROCESS' => [
        'flags' => [
            'STATIC_ARP' => [
                'class' => '',
                'descr' => 'Прописывать статический ARP',
                'default' => false,
                'display' => true,
            ],
            'STATIC_LEASE' => [
                'class' => '',
                'descr' => 'Прописывать статический лиз (DHCP без радиуса)',
                'default' => false,
                'display' => true,
            ],
            'ADD_ADDR_LIST' => [
                'list_name' => 'billing_test',
                'class' => '',
                'descr' => 'Добавлять в адрес лист микротика',
                'default' => false,
                'display' => true,
            ],
            'SPEED_CONTROL' => [
                'exclude_speed_values' => ['100', '1000'],
                'class' => '',
                'descr' => 'Установить шейпер согласно тарифу',
                'default' => false,
                'display' => true,
            ],
            'CHANGE_VLAN_ON_PORT' => [
                'class' => '',
                'descr' => 'Изменять влан на порту',
                'default' => false,
                'display' => true,
            ],
            'SET_PORT_DESCRIPTION' => [
                'class' => '',
                'descr' => 'Добавлять описание порта на оборудовании доступа',
                'default' => true,
                'display' => true,
            ],

        ],
        'execution_queue' => [
            'activate' => ['STATIC_ARP', 'STATIC_LEASE', 'CHANGE_VLAN_ON_PORT', 'SPEED_CONTROL', 'SET_PORT_DESCRIPTION'],
            'deactivate' => ['CHANGE_VLAN_ON_PORT', 'SPEED_CONTROL', 'STATIC_LEASE', 'STATIC_ARP'],
        ],
        'sql_transaction' => true,
    ],

    'BILLING' => [
        'devices' => [
            'core_levels' => [9],
            'access_levels' => [5, 6, 7, 8, 10, 11, 12, 13],
        ],
        //Типы IP адресов с таблицы eq_kinds используемых при поиске свободного IP
        'free_ip_search' => [
            'real' => [8],
            'local' => [6],
        ],
        'vlans_types' => [
            'fake' => [5],
            'inet' => [3, 4]
        ],
        'address_lists' => [
            'use_flag' => true,
            'flag_name' => 'ADDRESS_LIST',
            'enabled' => false,
            'flag_static_ip' => false,
            'list_name' => 'billing_test',
        ],
        'simple_queue' => [
            'enabled' => true,
            'flag_static_ip' => false,
        ],
        'static_arp' => [
            'enabled' => false,
            'flag_static_ip' => true,
        ],
        'static_lease' => [
            'enabled' => false,
            'flag_static_ip' => true,
        ],
        'change_vlan_on_port' => [
            'enabled' => false,
            //Проверять, есть ли еще привязки на этом порту в случае отключения.
            //Влан не будет сменен на FAKE, в случае наличия других активных привязок
            'calculate_bindings' => true,
        ],
        'set_descr_on_port' => [
            'enabled' => true,
        ],
    ],
    'CUSTOM_PAYMENT' => [
        'types' => [
            'Абонплата',
            'Акция',
            'Привилегия',
            'Подключение',
            'Обслуживание',
        ],
    ],

    //Block extra configs
    'PDF_PRINTING' => extraConf('receipt_pdf_printing'),
    'QUESTION_RULES' => extraConf('question_rules'),
    'CERT_OF_COMPLETION' => extraConf('cert_of_completion'),
    'TRINITY' => extraConf('trinity'),
    'PAYED_QUESTIONS' => extraConf('payed_questions'),
    'OMO_SYSTEMS' => extraConf('omo_systems'),
    'RADIUS' => extraConf('radius'),

    //Payment systems
    'GW_PAYMENTS_EASYPAY' => extraConf('gw_payment_easypay'),
    'GW_PAYMENTS_BANK24' => extraConf('gw_payment_bank24'),
    'GW_PAYMENTS_GLOBAL_MONEY' => extraConf('gw_payment_global_money'),
    'LIQPAY_ACCESS' => [
        '0' =>
            [
                'id' => '',
                'private_key' => '',
                'message' => 'Оплата послуг iнтернет за договором {{agreement}}',
            ],
    ],
    'PERSONAL_AREA' => [
        //Отображать смежные договора
        'show_neighbors' => false,

        //Мультиязычность
        'multi_lang' => [
            'enabled' => true,
            'default_lang' => 'ua',
            'langs' => [
                'ua' => 'Українська',
                'ru' => 'Русский',
                'en' => 'English'
            ],
        ],

        'recover_password' => [
            'enabled' => true,
            'text' => "Код подтверждения для смены пароля в личном кабинете: %s",
        ],
        'auth_by_phone' => [
            'enabled' => true,
            'text' => "Код подтверждения для входа в личный кабинет: %s",
        ],

        //Содержимое меню
        'menu' => [
            'index' => '{{PAGE_MAIN}}',
            'payments' => '{{PAGE_PAYMENTS}}',
            'pay' => '{{PAGE_PAY}}',
            'settings' => '{{PAGE_SERVICE_CONTROL}}',
            'contacts' => '{{PAGE_CONTACTS}}',
            'questions' => '{{PAGE_QUESTIONS}}',
            'mail' => '{{PAGE_CREATE_QUESTION}}',
        ],
        'show_registered_devices' => true,
    ],
    'API' => [
        'trusted_ips' => [
            '127.0.0.1',     //Service
            '172.1.2.1', //ServiceIP 2
            '10.255.254.1',
            '10.255.254.2',
            '10.255.254.3',
            '10.255.254.4',
            '10.255.254.5',
            '10.255.254.6',
            '10.255.254.7',
            '172.18.0.1',
            '172.18.0.2',
            '172.18.0.3',
            '172.18.0.4',
            '172.18.0.5',
            '127.0.0.1',
        ],
        'proxy' => [
            'enable' => true,
            'proxyIP' => "172.1.2.1",
            'realIpHeader' => 'HTTP_X_FORWARDED_FOR',
        ],
        'allow_tokens' => true,
    ],
    'GW' => [
        'trusted_ips' => [
            '127.0.0.1',     //Service
            '93.183.196.26', //EASYPAY
            '195.230.131.50', //EASYPAY
            '93.183.196.28', //EASYPAY
            '62.149.15.210', //BANK24
            '213.186.115.164', //TIME
            '213.186.115.168', //TIME
            '213.186.115.170', //TIME
            '193.104.58.50', //2Click test
            '213.160.149.229', // iBox
            '185.46.150.122', // iBox
            '213.160.154.26', // iBox
            '185.46.148.218', // iBox
            '213.160.149.230', // iBox
            '185.46.148.219', // iBox
            '89.184.66.69', //City24
            '62.149.15.210', //City24
            '10.255.254.1',
            '10.255.254.2',
            '10.255.254.3',
            '10.255.254.4',
            '10.255.254.5',
            '10.255.254.6',
            '10.255.254.7',
            '146.120.101.251',
            '88.99.207.99',
            '213.159.247.79',
            '176.36.149.207',
        ],
        'proxy' => [
            'enable' => true,
            'proxyIP' => "172.1.2.1",
            'realIpHeader' => 'HTTP_X_FORWARDED_FOR',
        ],
        'allow_tokens' => true,
    ],
    'MEMCACHE' => [
        'host' => 'localhost',
        'port' => '11211',
    ]
];
