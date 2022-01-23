<?php

require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init(true);

$form = [
  'type' => 'to_dev_from_binding',
  'binding_id' => -1,
];

envPHP\classes\std::Request($form);
$URL = conf('BASE.wildcore');
switch ($form['type']) {
    case 'to_dev_from_binding' :
        $psth = dbConnPDO()->prepare("SELECT e.ip switch, b.port 
            FROM eq_bindings b
            JOIN equipment e on e.id = b.switch
            WHERE b.id  = ?
        ");
        $psth->execute([$form['binding_id']]);
        $data = $psth->fetchAll();
        if(count($data) === 0) {
            html()->addNoty('error', "Binding with ID={$form['binding_id']} not found");
            $URL = conf('BASE.');
            break;
        }
        $client = envPHP\Wildcore\ClientInitializer::getClient();
        $device = $client->devices()->getByIp($data[0]['switch']);

        if(preg_match('#^0\/0\/[0-9]#', $data[0]['port'])) {
            $port = "pon" . $data[0]['port'];
        } else {
            $port = $data[0]['port'];
        }

        $interface = $device->getInterfaceByName($port);
        $URL .= "/info/device/{$device->getId()}/interface/{$interface->getId()}";
        break;
}
header("Location: $URL");
