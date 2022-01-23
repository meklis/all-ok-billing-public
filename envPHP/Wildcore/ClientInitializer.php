<?php

namespace envPHP\Wildcore;

use Meklis\WildcoreApiClient\WildcoreApiClient;

class ClientInitializer
{
    /**
     * @var WildcoreApiClient
     */
    protected static $client;

    /**
     * @return WildcoreApiClient
     */
    public static function getClient()
    {
        return self::$client;
    }

    public static function init()
    {
        $wildcoreUrl = conf('BASE.wildcore');

        self::$client = new WildcoreApiClient('', $wildcoreUrl . '/api/v1/');
    }
}
