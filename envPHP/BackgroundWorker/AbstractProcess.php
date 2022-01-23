<?php


namespace envPHP\BackgroundWorker;


use envPHP\classes\Logger;

abstract class AbstractProcess implements ProcessInterface
{
    protected $args = [];
    function __construct($arguments) {
        $this->args = $this->prepareArgs($arguments);
    }

    /**
     * @return \Monolog\Logger
     */
    function log() {
        return Logger::get()->withName('bg-proc');
    }

    abstract protected function prepareArgs($args);

}

