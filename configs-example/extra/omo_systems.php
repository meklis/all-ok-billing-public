<?php
use envPHP\service\OmoControl;

/**
 * Организация рабоыт с OMO Systems
 */
$OMO_SYSTEMS = [
    'enabled' => true,
    'access_addr' => 'https://ryy1xcx7jh.execute-api.eu-west-1.amazonaws.com/v6/access-token',
    'api_addr' => 'https://api.omo.systems',
    'secret' => Env()['OMO_SECRET'],
    'username' => Env()['OMO_USERNAME'],
    'password' => Env()['OMO_PASSWORD'],
    'user_can_share_device' => true,
    'user_as_owner' => false,
    'set_device_description' => true,
];
//Установка конфигурации для класса контроллера
OmoControl::setConfig($OMO_SYSTEMS);
return $OMO_SYSTEMS;
