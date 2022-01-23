#!/usr/bin/php
<?php
foreach ($_SERVER as $k=>$v) {
    if(!is_array($v)) {
        $_ENV[$k] = $v;
    }
}
require __DIR__ . '/../envPHP/load.php';
$content = stream_get_contents(STDIN);
$arguments = json_decode($content, true);
$className = "";
if(count($argv) > 1) {
    $className = $argv[1];
}

if(isset($argv[2])) {
    sleep($argv[2]);
}

if(!$arguments) {
    \envPHP\classes\Logger::get()->withName('bg-proc')->error("Incorrect json data for run background process: {$content}");
    throw new \Exception("Incorrect json data for run background process: {$content}");
}

if(!class_exists($className)) {
    \envPHP\classes\Logger::get()->withName('bg-proc')->error("Class $className doesnt exists");
    throw new \Exception("Class $className doesnt exists");
}

\envPHP\classes\Logger::get()->withName('bg-proc')->info("Incomming background proccess {$argv[1]}", $arguments);
echo "Start execute process";
\envPHP\BackgroundWorker\BackgroundProcess::_exec($argv[1], $arguments);
