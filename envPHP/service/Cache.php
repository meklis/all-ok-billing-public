<?php


namespace envPHP\service;

class Cache extends \Memcached
{
    /**
     * @var self
     */
    protected static $instance;
    public static function init($host, $port) {
        $i = new self();
        $i->addServer($host, $port);

        self::$instance = $i;
    }

    /**
     * @return self
     */
    public static function instance() {
        return self::$instance;
    }

}