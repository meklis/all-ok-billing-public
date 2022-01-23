<?php
/**
 * Параметры платежного шлюза для easyPay
 */
return [
    'providers' => [
        1 => 1,
    ],
    'cert_path' => '/www/files/certs/EasySoftPublicKey2.pem',
    'key_path' => '/www/files/certs/provider.ppk',
    'sign_check_enabled' => true,
];