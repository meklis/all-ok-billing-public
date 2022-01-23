<?php


namespace envPHP\libs;


use Meklis\Network\TrinityTV\Api;

class TrinityBilling
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * TrinityBilling constructor.
     * @param Api $trinity
     */
    function __construct(Api $trinity)
    {
        $this->api = $trinity;
    }

    function getSubscriptions() {
        $subscriptions = $this->api->listUsers();
        $devices = [];
    }
}