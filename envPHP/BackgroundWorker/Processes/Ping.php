<?php


namespace envPHP\BackgroundWorker\Processes;


use Curl\MultiCurl;
use envPHP\BackgroundWorker\AbstractProcess;
use envPHP\service\Exceptions\NotFoundException;
use envPHP\MultiSwitcher\MultiSwitcher;
use envPHP\MultiSwitcher\SearchPort;

class Ping extends AbstractProcess
{

    public function run()
    {
        exec ("ping -c {$this->args['count']} {$this->args['ip']}", $ping_output, $value);
    }

    protected function prepareArgs($args)
    {
        if(!isset($args['ip'])) throw new \Exception("Ip is required");
        if(!isset($args['count'])) {
            $args['count'] = 9999;
        }
        return $args;
    }

}