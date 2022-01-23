#!/usr/bin/php
<?php
require  __DIR__ . '/../envPHP/load.php';

/**
 *
57	Домофон Аудио 20
58	Домофон Видео 40
67	Домофон Променада 15
77	Аудиодомофон M 20
78	Видеодомофон М 40
79	Домофон Аудио 0
87	Аудиодомофон M 15
88	Видеодомофон M 30
89	Аудиодомофон M 0
 */

$priceMigrations = [
    57 => 77,
    58 => 78,
    67 => 87,
    79 => 89,
];
$arrayKeys = join(",", array_keys($priceMigrations));
$data = dbConnPDO()->query("
SELECT id, agreement, price
FROM client_prices p 
WHERE price in ($arrayKeys)
and time_stop is null ");


foreach ($data->fetchAll() as $d) {
    \envPHP\classes\std::msg("Start change price on {$d['id']}");
    \envPHP\service\activations::deactivate($d['id'], 20, false);
    \envPHP\service\activations::activate($d['agreement'], $priceMigrations[$d['price']],20, false);
}