<?php


namespace envPHP\classes;


use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;

class Logger
{
    protected static $logger;
    static function init($config) {
        $logger = new \Monolog\Logger($config['name']);
        $processor = new UidProcessor();
        $logger->pushProcessor($processor);
        $handler = new StreamHandler($config['path'], $config['level']);
        $logger->pushHandler($handler);
        self::$logger = $logger;
    }

    /**
     * @return \Monolog\Logger
     */
    static function get() {
        return self::$logger;
    }
}