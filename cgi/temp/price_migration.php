<?php
require __DIR__ . '/../../envPHP/load.php';
$prices = [
    42 => 80,
    43 => 81,
    44 => 82,
    45 => 83,
    51 => 84,
    52 => 85,
    53 => 86,
    64 => 90,
    65 => 91,
    66 => 92,
    69 => 93,
    74 => 102,
];
$currentActivations = array_keys($prices);
$listOfActivations = dbConnPDO()
    ->query("SELECT p.id, p.agreement, p.price 
FROM client_prices p 
JOIN (
SELECT activation FROM eq_bindings
UNION 
SELECT activation FROM trinity_bindings
) act on act.activation = p.id  WHERE price in (".join(',', $currentActivations).")")
    ->fetchAll();
foreach ($listOfActivations as $activation) {
    echo "Work with {$activation['id']}, ";
    dbConnPDO()->beginTransaction();
    //Disable activating
    dbConnPDO()->exec("UPDATE client_prices SET time_stop = NOW(), deact_employee_id = 2 WHERE id = {$activation['id']}");

    //Create new activation
    dbConnPDO()->exec("INSERT INTO client_prices (agreement, price, time_start, act_employee_id) VALUES ({$activation['agreement']}, {$prices[$activation['price']]},  NOW(), 2)");
    $newActivId = dbConnPDO()->lastInsertId();

    //Update bindings
    dbConnPDO()->exec("UPDATE eq_bindings SET activation = {$newActivId} WHERE activation = {$activation['id']}");
    dbConnPDO()->exec("UPDATE trinity_bindings SET activation = {$newActivId} WHERE activation = {$activation['id']}");
    echo "new activation = $newActivId\n";
    dbConnPDO()->commit();
}