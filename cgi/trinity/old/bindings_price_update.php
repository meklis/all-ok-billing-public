<?php
require __DIR__ . "/../../envPHP/load.php";

/**
 * Скрипт для восстановления ассоциации прайсов.
 * Использовать, если ассоциации в конфиг-файле тринити были изменены, например у тринити сменился ID прайса, а у нас остаются те же.
 */

$trinity_configs = getGlobalConfigVar('TRINITY');
$db = dbConn();
$associations = [];
foreach (getGlobalConfigVar('TRINITY')['services_associate'] as $assoc) {
    $associations[$assoc['local']] = $assoc['trinity'];
}

$activations = dbConnPDO()->query("
SELECT b.id binding_id, cp.id activation_id, cp.agreement, cp.price local_price_id, ct.subscr_id trinity_price_id  
FROM trinity_contracts ct 
JOIN trinity_bindings b on b.contract = ct.id
LEFT JOIN client_prices cp on cp.id = b.activation 
order by  1 
")->fetchAll();

$listToUpdatePrice = [];
foreach ($activations as $activation) {
    if($associations[(int)$activation['local_price_id']] !== (int)$activation['trinity_price_id']) {
        $activation['actual_price'] = $associations[$activation['local_price_id']];
        \envPHP\classes\std::msg("Activation {$activation['activation_id']} must be updated from {$activation['trinity_price_id']} to {$activation['actual_price']}");
        $listToUpdatePrice[] = $activation;
    }
}

$countForUpdate = count($listToUpdatePrice);
$countActivations = count($activations);
echo <<<HTML
Всего активных привязок: {$countActivations}
Необходимо обновить: {$countForUpdate}

Начинаем обновлять...
HTML;
sleep(3);
foreach ($listToUpdatePrice as $b) {
    \envPHP\classes\std::msg("Try update - " . json_encode($b));
    \envPHP\service\TrinityControl::frost($b['binding_id']);
    \envPHP\service\TrinityControl::defrost($b['activation_id'], $b['activation_id'], $b['binding_id']);
    \envPHP\classes\std::msg("Success updated binding {$b['binding_id']}");
}
