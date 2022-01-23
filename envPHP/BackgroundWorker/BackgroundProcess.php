<?php


namespace envPHP\BackgroundWorker;


use envPHP\classes\Logger;

class BackgroundProcess
{
    public static function run($className, $arguments = [], $timeoutSec = 0)
    {
        $args = json_encode($arguments);
        $className = addslashes($className);
        $envs = "";
        foreach ($_ENV as $k=>$v) {
            $envs .= "{$k}={$v} ";
        }
        exec("{$envs}  echo '{$args}' | /www/cgi/background_process.php  {$className} {$timeoutSec} > /dev/null &", $_, $ret);
        if ($ret != 0) {
            throw new \Exception("Error start script");
        }
    }

    public static function _exec($className, $arguments)
    {
        if (!class_exists($className)) {
            Logger::get()->withName('bg-proc')->error("Class $className doesnt exists");
            throw new \Exception("Class $className doesnt exists");
        }
        $object = new $className($arguments);
        try {
            $object->run();
        } catch (\Exception $e) {
            Logger::get()->withName('bg-proc')->error("Error execute process $className: {$e->getMessage()}", $arguments);
            Logger::get()->withName('bg-proc')->debug($e->getTraceAsString());
        }
    }
}
