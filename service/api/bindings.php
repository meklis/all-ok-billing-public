<?php
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();

//Блок для обработки формы
$form = [
    'method'=>'',
    'ip'=>'',
    'switch'=>'',
    'port'=>'',
    'mac'=>'',
    'actId'=>'',
    'employee'=>0,
    'id'=>'',
];
envPHP\classes\std::Request($form);

$bind = new envPHP\service\bindings();

if(!method_exists($bind, $form['method'])) envPHP\classes\std::Response("Method not exists", 10);

try {
    switch ($form['method']) {
        case 'getBinding': envPHP\classes\std::Response($bind->getBinding($form['id'],$form['ip'],$form['mac'],$form['switch'],$form['port']),0); break;
        case 'addBinding': envPHP\classes\std::Response($bind->addBinding($form['actId'],$form['ip'],$form['mac'],$form['switch'],$form['port'], $form['employee']),0); break;
        case 'editBinding': envPHP\classes\std::Response($bind->editBinding($form['id'],$form['ip'],$form['mac'],$form['switch'],$form['port']),0); break;
        case 'deleteBinding': envPHP\classes\std::Response($bind->deleteBinding($form['id']),0); break;
    }
} catch (Exception $e) {
    envPHP\classes\std::Response($e->getMessage(),1);
}

