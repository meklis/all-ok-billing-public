#!/usr/bin/php
<?php

use envPHP\classes\std;
use envPHP\service\bindings;

require('/www/envPHP/load.php');
require  __DIR__ . '/_help_funcs.php';


$sql = dbConn();

$data = $sql->query("SELECT agreement, binding_id, mac_in_binding, real_mac FROM walker_incorrect_binding_mac");

while ($d = $data->fetch_assoc()) {
    try {
        bindings::edit($d['binding_id'], '', $d['real_mac'], '', '', 2);
        std::msg("SUCCESS updated {$d['agreement']} from {$d['mac_in_binding']} to {$d['real_mac']}");
    }  catch (Exception $e) {
        std::msg("ERROR update binding for agreement {$d['agreement']} ");
    }
}

