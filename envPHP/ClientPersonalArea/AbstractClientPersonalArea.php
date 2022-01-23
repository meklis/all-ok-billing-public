<?php


namespace envPHP\ClientPersonalArea;


abstract class AbstractClientPersonalArea
{
    protected $config;
    protected function getConnection() {
        return dbConnPDO();
    }
    function __construct()
    {
        $this->config = getGlobalConfigVar('PERSONAL_AREA');
    }
}