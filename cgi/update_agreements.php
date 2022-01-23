<?php
require  __DIR__ . '/../envPHP/load.php';
$pdo = dbConnPDO();

foreach ($pdo->query("SELECT id FROM clients WHERE agreement > 1000000 ORDER BY id desc ")->fetchAll(PDO::FETCH_ASSOC) as $client) {
    //Обновление баланса после внесения платежей
    $pdo->prepare("UPDATE clients SET agreement = get_free_agreement() WHERE id = ?")->execute([$client['id']]);
}

function getAgreement() {
    global  $agree ;
    $agree = $agree+1;
    return $agree;
}

