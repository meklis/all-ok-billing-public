<?php
require  __DIR__ . '/../envPHP/load.php';
$pdo = dbConnPDO();

//Предустановленные параметры
$switchId = 342;
$port = 0;

$ips = [
    23 => ip2long('172.16.40.2'),
    27 => ip2long('172.16.42.2'),
];

//Загрузка данных с базы миграции
$USER_ACTIVATIONS = [];
foreach ($pdo->query("
    SELECT DISTINCT c.id nodeny_client_id, bp.id new_price_id, c.state price_state, bp.work_type
    FROM nodeny.`users_services` us 
    JOIN nodeny.services s on s.service_id = us.service_id
    JOIN nodeny._migrate_clients c on c .id = us.uid
    JOIN service.bill_prices bp on bp.`name` = s.title 
    ORDER by 1, 4 
")->fetchAll(PDO::FETCH_ASSOC) as $price) {
    $USER_ACTIVATIONS[$price['nodeny_client_id']][] = $price;
}
$USER_MAC = [];
foreach ($pdo->query("
   SELECT uid, mac, INET_NTOA(ip) old_ip, grp
FROM  nodeny.`mac_uid` m 
JOIN nodeny.users u on u.id = m.uid 
WHERE uid != 0 and ip != 0 and mac is not null ;
")->fetchAll(PDO::FETCH_ASSOC) as $data) {
    $m = strtoupper($data['mac']);
    $data['mac'] = "{$m[0]}{$m[1]}:{$m[2]}{$m[3]}:{$m[4]}{$m[5]}:{$m[6]}{$m[7]}:{$m[8]}{$m[9]}:{$m[10]}{$m[11]}";
    $data['ip'] = long2ip($ips[$data['grp']]);
    $USER_MAC[$data['uid']][] = $data;
    $ips[$data['grp']]++;
}
$COMMENTS = [];
foreach ($pdo->query("SELECT id, `comment` FROM nodeny.users")->fetchAll(PDO::FETCH_ASSOC) as $comment) {
    $COMMENTS[$comment['id']] = $comment['comment'];
}
$PAYMENTS = [];
foreach ($pdo->query("SELECT 
mid user_id,
cash,
FROM_UNIXTIME(time) `time`,
if(`comment` = '', 'Перенос платежей', `comment`) `comment`,
`reason`
 FROM nodeny.pays 
")->fetchAll(PDO::FETCH_ASSOC) as $pay) {
    $PAYMENTS[$pay['user_id']][] = $pay;
}



dbConnPDO()->beginTransaction();
$agree = 1000000;

foreach ($pdo->query("SELECT id, 
       agreement, 
       name, 
       phone, 
       passwd,
       house_id,
       floor,
       entrance,
       add_time,
       balance,
       state,
       apartment,
       enable_credit 
FROM nodeny._migrate_clients")->fetchAll(PDO::FETCH_ASSOC) as $client) {
    $userActivations = isset($USER_ACTIVATIONS[$client['id']]) ? $USER_ACTIVATIONS[$client['id']] : [];
    $comment = $COMMENTS[$client['id']];
    $bindings = isset($USER_MAC[$client['id']]) ? $USER_MAC[$client['id']] : [];
    $payments = $PAYMENTS[$client['id']];
    $descr = "Клиент UkrWeb
{$comment}
Старый договор: {$client['agreement']}
Телефон: {$client['phone']}
Баланс: {$client['balance']}

------------------------------------------
nodeny-user-id: {$client['id']}    
nodeny-uid-mac: ".json_encode($bindings,  JSON_UNESCAPED_UNICODE)."
enabled: {$client['state']}
prices: ".json_encode($userActivations, JSON_UNESCAPED_UNICODE)."
";

    //Добавление клиента
    $newId = insertClient(
        getAgreement(),
        $client['name'],
        $client['entrance'],
        $client['floor'],
        $client['apartment'],
        $client['house_id'],
        0,
        $client['add_time'],
        $descr,
        $client['enable_credit'],
        $client['passwd']
    );

    //Внесение контакта
    $pdo->prepare("INSERT INTO client_contacts (agreement_id, name, type, value, created_at, updated_at, employee_id, main) 
VALUES (?, 'Основной', 'PHONE', ?, NOW(), NOW(), 20, 1)")->execute([$newId, $client['phone']]);

    //Внесение активаций
    $bindingAdded = false;
    foreach ($userActivations as $activation) {
        if($activation['price_state'] === 'on') {
            $pdo->prepare("INSERT INTO client_prices (agreement, price, time_start, time_stop, act_employee_id, deact_employee_id)
VALUES (?, ?, NOW(), null, 20, null)")->execute([$newId, $activation['new_price_id']]);
        } else {
            $pdo->prepare("INSERT INTO client_prices (agreement, price, time_start, time_stop, act_employee_id, deact_employee_id)
VALUES (?, ?, NOW(), NOW(), 20, 20)")->execute([$newId, $activation['new_price_id']]);
        }

        //Внесение привязок по активациям
        $activationId = $pdo->lastInsertId();
        if(!$bindingAdded && $activation['work_type'] === 'inet') {
            foreach ($bindings as $binding) {
                $pdo->prepare("INSERT INTO service.eq_bindings (created, activation, switch, port, mac, ip, employee, allow_static)
VALUES (NOW(), ?, ?, ?, ?, ?, 20, 0)")->execute([$activationId, $switchId, $port, $binding['mac'], $binding['ip']]);
            }
            $bindingAdded = true;
        }
    }

    //Внесение платежей
    foreach ($payments as $payment) {
        $pdo->prepare("INSERT INTO service.paymants (money, agreement, time, `comment`, debug_info, payment_type)
VALUES (?, ?, ?, ?, ?, 'Перенос баланса')")->execute([$payment['cash'], $newId, $payment['time'], $payment['comment'], $payment['reason']]);
    }

    //Обновление баланса после внесения платежей
    $pdo->prepare("UPDATE clients SET balance = ? WHERE id = ?")->execute([$client['balance'], $newId]);
}


dbConnPDO()->commit();

function getAgreement() {
    global  $agree ;
    $agree = $agree+1;
    return $agree;
}

function insertClient($agreement, $name,  $entrance, $floor, $apartment, $house, $balance, $add_time, $descr, $enable_credit, $password) {
    if($add_time === '1970-01-01 03:00:00') $add_time = date("Y-m-d 00:00:00");
    $psth = dbConnPDO()->prepare("
    INSERT INTO service.clients (
            agreement, 
            name, 
            entrance, 
            floor, 
            apartment, 
            house, 
            balance, 
            add_time, 
            descr, 
            notice_mail, 
            notice_sms, 
            enable_credit, 
            `password`, 
            provider, 
            enable_credit_period, 
            telegram_chat_id, 
            `status`) VALUES (
            :agreement, 
            :name, 
            :entrance, 
            :floor, 
            :apartment, 
            :house, 
            :balance, 
            :add_time, 
            :description, 
            0, 
            1, 
            :enable_credit, 
            :password,                  
            1026, 
            0, 
            '', 
            'ENABLED'                            
            );
    ");
    $psth->execute([
        ':agreement' => $agreement,
        ':name' => $name,
        ':entrance' => $entrance,
        ':floor' => $floor,
        ':apartment' => $apartment,
        ':house' => $house,
        ':balance' => $balance,
        ':add_time' => $add_time,
        ':description' => $descr,
        ':enable_credit' => $enable_credit,
        ':password' => $password,
    ]);
    return dbConnPDO()->lastInsertId();
}
