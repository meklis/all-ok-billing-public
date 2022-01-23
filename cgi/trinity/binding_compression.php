<?php

require __DIR__ . "/../../envPHP/load.php";

/**
 * Сжимает привязки на контрактах
 * на основе привязок тринити в сервисе
 */

$db = dbConnPDO();

$updatedList = [0];
while (true) {
    $psth = dbConnPDO()->query("SELECT tb.id, c.count count_on_contract, tb.contract, tb.uuid, tb.mac, tb.activation, tb.local_playlist_id
FROM v_trinity_contract_stat c 
JOIN trinity_bindings tb on tb.contract = c.id 
WHERE (`count` in (1,2,3) or `count` > 4)  and tb.id not in (".join(',', $updatedList).")
ORDER BY 2, 3 limit 1");
    if($psth->rowCount() === 0) {
        break;
    }
    $binding = $psth->fetch();
    \envPHP\classes\std::msg("Start working with binding={$binding['id']}");
    \envPHP\service\TrinityControl::frost($binding['id']);
    \envPHP\service\TrinityControl::defrost($binding['activation'], $binding['activation'], $binding['id']);
    $updatedList[] = $binding['id'];
}