<?php

use SwitcherCore\Switcher\Core;


/**
 * @param $core Core
 */
function getModulesList($core) {
    $modules = [];
    $modulesList = $core->getModulesData();
    foreach ($modulesList as $module) {
        $modules[] = $module['name'];
    }
    return $modules;
}

/**
 * @param $core Core
 */
function getAbonPorts($core) {
    $vlans = $core->action('vlans_by_port');
    $ports = [];
    foreach ($vlans as $port) {
        if(count($port['tagged']) == 0 && count($port['forbidden']) == 0 && count($port['untagged']) != 0) {
            $ports[] = $port['port'];
        }
    }
    return $ports;
}