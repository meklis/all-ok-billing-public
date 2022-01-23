<?php

use envPHP\service\OmoControl;
use envPHP\service\OmoLocalControl;
use envPHP\service\PSC;

$rank = 5;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


# region Check permission
if (!PSC::isPermitted('customer_show_card')) {
    pageNotPermittedAction();
}

# endregion


# region Define variables
$form = [
    'checkPort' => '',
    'id' => 0,
    'enable_credit' => 0,
    'agreement' => '',
    'ping' => '',
    'provider' => 0,
    'enable_credit_period' => '',
    'message' => '',
    'disable_limit_days' => 0,
    'sms_text' => 0,
    'apartment' => 0,
    'entrance' => 0,
    'message_type' => '',
    'omo_phone' => '',
    'floor' => 0,

];
$ht = [
    'provider' => '',
    'credit_status' => 'Неизвестно, нет данных по использованию',
    'credit_period_enabled' => '',
    'activation_name_info' => '',
    'group' => '',
    'disable_limit_info' => '',
    'trinity_binds' => '',
    'omo_systems_block' => '',
    'radius_activity' => '',
    'contacts' => '<h3 align="center">Контактов не найдено</h3>',
];

$contactTypes = [
    'PHONE' => 'Телефон',
    'EMAIL' => 'Email',
    'VIBER' => 'Viber',
    'TELEGRAM' => 'Telegram',
];

# endregion

envPHP\classes\std::Request($form);

if ($form['checkPort']) {
    $check = true;
} else $check = false;
if ($form['ping']) {
    $checkPing = $form['ping'];
} else $checkPing = false;
$test_port = '';
$ping_ip = "";
$message = '';
if ($form['message_type']) {
    $html->addNoty($form['message_type'], $form['message']);
}
$questions = '<h5>Заявок еще не оформлено</h5>';

$checkHouseAllow = function () use ($form) {
    $psth = dbConnPDO()->prepare("
            SELECT c.id
            FROM clients c 
            JOIN (SELECT h.id from addr_houses h JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")) houses on houses.id = c.house
            WHERE c.id = ? or c.agreement = ?
    ");
    $psth->execute([$form['id'], $form['agreement']]);
    return $psth->rowCount() > 0;
};
if(!$checkHouseAllow()) {
    pageNotPermittedAction();
}

# region Action block
//Изменения абонента
if (isset($form['action'])) {
    switch ($form['action']) {
        case 'price_stop':
            if (PSC::isPermitted('customer_stop_service')) {
                $test = @json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/activation/deactivate", ['activation' => $form['price'], 'employee' => _uid]));
                if (!$test) {
                    $html->addNoty('error', "Неопознаная ошибка при работе с API");
                } elseif ($test->code != 0) {
                    $html->addNoty('error', "Ошибка деактивации({$test->errorMessage})");
                } else {
                    $html->addNoty('success', "Прайс деактивирован");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'price_start':
            if (PSC::isPermitted('customer_start_service')) {
                $raw = envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/activation/activate", ['agreement' => $form['id'], 'price' => $form['price'], 'employee' => _uid]);
                $test = @json_decode($raw);
                if (!$test) {
                    $html->addNoty('error', "Неопознаная ошибка при работе с API ($raw)");
                } elseif ($test->code != 0) {
                    $html->addNoty('error', "Ошибка первичной активации ({$test->errorMessage})");
                } else {
                    header("Location: ?id={$form['id']}&message_type=success&message=Прайс успешно добавлен, ID: {$test->data}");
                    exit;
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'frost':
            if (PSC::isPermitted('customer_pause_service')) {
                $resp = envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/activation/frost", ['activation' => $form['price'], 'employee' => _uid]);
                $test = @json_decode($resp);
                if (!$test) {
                    $html->addNoty('error', "Неопознаная ошибка при работе с API.");

                } elseif ($test->code != 0) {
                    $html->addNoty('error', "Ошибка заморозки (" . addslashes($test->errorMessage) . ")");
                } else {
                    $html->addNoty('success', "Прайс изменен");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'defrost':
            if (PSC::isPermitted('customer_resume_service')) {
                $test = @json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/activation/defrost", ['activation' => $form['price'], 'employee' => _uid]));
                if (!$test) {
                    $html->addNoty('error', "Неопознаная ошибка при работе с API");
                } elseif ($test->code != 0) {
                    $html->addNoty('error', "Ошибка активации (" . addslashes($test->errorMessage) . ")");
                } else {
                    header("Location: ?id={$form['id']}&message_type=success&message=Прайс успешно добавлен");
                    exit;
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'rename':
            if (PSC::isPermitted('customer_change_name')) {
                $test = $sql->query("UPDATE clients SET name = %1 WHERE id = %2", $form['name'], $form['id']);
                if ($test) {
                    $html->addNoty('success', "Имя успешно изменено");
                } else {
                    $html->addNoty('error', "Ошибка при изменении: {$sql->error}");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'descr':
            if (PSC::isPermitted('customer_change_description')) {
                $test = $sql->query("UPDATE clients SET descr = %1 WHERE id = %2", $form['descr'], $form['id']);
                if ($test) {
                    $html->addNoty('success', "Описание изменено");
                } else {
                    $html->addNoty('error', "Ошибка при изменении: " . $sql->error . "");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'notice':
            if (PSC::isPermitted('customer_change_notification')) {
                $test = $sql->query("UPDATE clients SET notice_sms = %1, notice_mail = %2 WHERE id = %3", $form['notice_sms'], $form['notice_mail'], $form['id']);
                if ($test) {
                    $html->addNoty('success', "Настройка уведомлений изменена");
                } else {
                    $html->addNoty('error', "Ошибка при изменении: " . $sql->error . "");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'password':
            if (PSC::isPermitted('customer_change_password')) {
                $test = $sql->query("UPDATE clients SET password = %1 WHERE id = %2", $form['password'], $form['id']);
                if ($test) {
                    $html->addNoty('success', "Пароль изменен");
                } else {
                    $html->addNoty('error', "Ошибка при изменении: " . $sql->error . "");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'edit_credit':
            if (PSC::isPermitted('customer_change_ack')) {
                $test = $sql->query("UPDATE clients SET enable_credit = %1 WHERE id = %2", $form['enable_credit'], $form['id']);
                if ($test) {
                    $html->addNoty('success', "Параметры изменены");
                } else {
                    $html->addNoty('error', "Ошибка при изменении: " . $sql->error . "");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'edit_provider':
            if (PSC::isPermitted('customer_change_provider')) {
                $test = $sql->query("UPDATE clients SET provider = %1 WHERE id = %2", $form['provider'], $form['id']);
                if ($test) {
                    $html->addNoty('success', "Провайдер сохранен");
                } else {
                    $html->addNoty('error', "Ошибка при изменении: " . $sql->error . "");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'disable_credit_period':
            if (PSC::isPermitted('customer_change_ack')) {
                $test = $sql->query("UPDATE clients SET enable_credit_period = 0 WHERE id = %1", $form['id']);
                if ($test) {
                    $html->addNoty('success', "Параметры изменены");
                } else {
                    $html->addNoty('error', "Ошибка при изменении: " . $sql->error . "");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'enable_credit_period':
            if (PSC::isPermitted('customer_change_ack')) {
                $test = $sql->query("UPDATE clients SET enable_credit_period = 1 WHERE id = %1", $form['id']);
                if ($test) {
                    $html->addNoty('success', "Параметры изменены");
                } else {
                    $html->addNoty('error', "Ошибка при изменении: " . $sql->error . "");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'activate_credit_period':
            if (PSC::isPermitted('customer_change_ack')) {
                $test = json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/creditPeriod/enableWithDefrost", ['agreement' => $form['id'], 'employee' => _uid]));
                if (!$test) {
                    $html->addNoty('error', "Неопознаная ошибка при работе с API");
                } elseif ($test->code != 0) {
                    $html->addNoty('error', "Ошибка предоставления кредитного периода ({$test->errorMessage})");
                } else {
                    $html->addNoty('success', "Кредитный период активирован");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;

        case 'deactivate_credit_period':
            if (PSC::isPermitted('customer_change_ack')) {
                $test = @json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/creditPeriod/disable", ['agreement' => $form['id'], 'employee' => _uid]));
                if (!$test) {
                    $html->addNoty('error', "Неопознаная ошибка при работе с API");
                } elseif ($test->code != 0) {
                    $html->addNoty('error', "Ошибка деактивации кредитного периода {$test->errorMessage})");
                } else {
                    $html->addNoty('success', "Кредитный период деактивирован");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'set_custom_day_limit':
            if (PSC::isPermitted('customer_change_ack')) {
                try {
                    $res = \envPHP\service\LimitControlDeactivateDays::set(
                        (new \envPHP\structs\Client())->fillById($form['id']),
                        $form['disable_limit_days'],
                        (new \envPHP\structs\Employee())->fillById(_uid)
                    );
                    $html->addNoty('success', "Дни отключения изменены");
                } catch (\Exception $e) {
                    $html->addNoty('error', "Ошибка изменения лимита {$e->getMessage()})");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'send_sms':
            if (PSC::isPermitted('customer_change_notification')) {
                try {
                    //Получение номера телефона
                    $phone = dbConn()->query("SELECT `value` phone FROM client_contacts WHERE agreement_id = '{$form['id']}' and type='PHONE' and main = 1")->fetch_assoc()['phone'];
                    \envPHP\service\notification::sendSMS($phone, $form['sms_text'], false);
                    $html->addNoty('success', "Отправлено!");
                } catch (\Exception $e) {
                    $html->addNoty('error', "{$e->getMessage()}");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'change_addr':
            if (PSC::isPermitted('customer_change_addr')) {
                $test = $sql->query("UPDATE clients SET apartment = %1, entrance= %2, floor= %3  WHERE id = %4", $form['apartment'], $form['entrance'], $form['floor'], $form['id']);
                if ($test) {
                    $html->addNoty('success', "Изменения адреса сохранены");
                } else {
                    $html->addNoty('error', "Ошибка при изменении: {$sql->error}");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'omo_bind_phone':
            if (PSC::isPermitted('customer_change_addr')) {
                try {
                    $omo = new OmoControl();
                    $id = $omo->addPhone($form['omo_phone'], $form['id']);
                    if ($id) {
                        $html->addNoty('success', "Пользователь OMO успешно добавлен. ID: $id");
                    } else {
                        $html->addNoty('error', "Возникли ошибки при внесении");
                    }
                } catch (Exception $e) {
                    $html->addNoty('error', "Ошибка работы с OMO: {$e->getMessage()}");
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
        case 'disable_client':
            if (PSC::isPermitted('customer_disable_agreement')) {
                if (dbConn()->query("UPDATE clients SET status='DISABLED' WHERE id = '{$form['id']}'")) {
                    $html->addNoty('success', 'Договор успешно отключен');
                } else {
                    $html->addNoty('error', "Ошибка отключения договора, SQL ERR: " . dbConn()->error);
                }
            } else $html->addNoty('error', "Недостаточно прав для изменений");
            break;
    }
}

# endregion

# region general customer info and fill $form variable
//Получение инфы о абоненте
$data = $sql->query("
SELECT s.`status` client_status, s.entrance, s.floor, gr.name group_name, s.provider, s.id, s.balance, s.agreement, s.password, s.apartment, s.notice_mail, s.notice_sms, s.add_time, s.name,    ha.name house, sa.name street, ca.name city, s.descr, s.house houseId, enable_credit, enable_credit_period
FROM clients s 
JOIN addr_houses ha on ha.id = s.house
JOIN addr_streets sa on sa.id = ha.street
JOIN addr_cities ca on ca.id = sa.city 
LEFT JOIN addr_groups gr on gr.id = ha.group_id
WHERE s.id = %1 or s.agreement = '{$form['agreement']}'", $form['id'])->fetch_assoc();
if ($data['id'] == 0) {
    echo "<br><br><br>";
    echo "<h1 align='center'>Указанного договора не существует</h1>";
    exit;
}
foreach ($data as $k => $v) {
    $form[$k] = $v;
}
# endregion

# region Формирование текста СМС
if (!$form['sms_text']) {
    $text = getGlobalConfigVar("DEFAULT_SMS_TEXT");
    foreach ($form as $key => $value) {
        if (is_string($value)) {
            $text = str_replace("{{" . $key . "}}", $value, $text);
        }
    }
    $form['sms_text'] = $text;
}
#endregion

$ht['group'] = $form['group_name'] ? " ({$form['group_name']})" : " (без группы)";


#region Получение инфы по кредитному периоду
$credit_period_info = $sql->query("SELECT c.created, c.closed_date, c.`status`, ec.`name` closed_employee , eo.`name` created_employee
FROM client_credit c 
LEFT JOIN employees ec on ec.id = closed_employee 
LEFT JOIN employees eo on eo.id = created_employee 
WHERE client_id = '{$form['id']}' 
ORDER BY c.id desc LIMIT 1 ")->fetch_assoc();
if ($credit_period_info['status'] == 'OPEN' && $form['balance'] <= 0) {
    $ht['credit_status'] = "Состояние: <span style='color: red; font-weight: bold;'>АКТИВИРОВАН, ИСПОЛЬЗУЕТСЯ</span> <br>Активировал: {$credit_period_info['created_employee']}, {$credit_period_info['created']}";
    $ht['credit_status'] .= "<button disabled class='btn btn-primary btn-block btnPdn'>Выключение кредитного периода запрещено</button>";
} else if (in_array($credit_period_info['status'], ['', 'CLOSED', 'CANCEL', 'DIACTIVATED'])) {
    $ht['credit_status'] = "Состояние: <span style='color: gray; font-weight: bold;'>НЕ АКТИВЕН</span> <br>Последняя активация: {$credit_period_info['created_employee']}, {$credit_period_info['created']}";
    $ht['credit_status'] .= "<button name='action' value='activate_credit_period' class='btn btn-primary btn-block btnPdn btn-sm  '>Активировать кредитный период</button> <small>Услуги будут автоматически возобновлены, если на данный момент приостановлены</small>";
} else if (in_array($credit_period_info['status'], ['OPEN'])) {
    $ht['credit_status'] = "Состояние: <span style='color: darkgreen; font-weight: bold;'>АКТИВИРОВАН</span> <br>Последняя активация: {$credit_period_info['created_employee']}, {$credit_period_info['created']}";
    $ht['credit_status'] .= "<button name='action' value='deactivate_credit_period' class='btn btn-primary btn-block btnPdn btn-sm  '>Деактивировать кредитный период</button>";
} else {
    $ht['credit_status'] = "Состояние: НЕ ИЗВЕСТНО, ЧТО ТО ПОШЛО НЕ ТАК";
}
$ht['credit_period_enabled'] = $form['enable_credit_period'] > 0 ? "CHECKED" : "";
#endregion

#region Получение инфы по лимиту отключения
try {
    $limit = \envPHP\service\LimitControlDeactivateDays::get(
        (new \envPHP\structs\Client())->fillById($form['id'])
    );

    $ht['disable_limit_info'] = "Установлено количество дней: <b>{$limit->getDays()}</b>
    ";
    if ($limit->getEmployee()->getName() != 'System') {
        $ht['disable_limit_info'] .= "<br>Установил: <b>{$limit->getEmployee()->getName()}, {$limit->getCreated()}</b>";
    }
    $form['disable_limit_days'] = $limit->getDays();
} catch (\Exception $e) {
    $ht['disable_limit_info'] = "Нет информации по лимитам. Необходимо активировать услугу или проставить лимит вручную.";
}
#endregion

#region Выборка платежей
$paymants = $sql->query("SELECT money, cast(time as date) time, comment, payment_type FROM paymants WHERE agreement = %1 order by time desc ", $form['id']);
if ($paymants->num_rows == 0) $hpay = "<h4 align='center'>Платежей еще не было</h4>"; else {
    $hpay = "<table class='table table-sm table-striped table-bordered'  id='table_payments'>
            <thead>
               <tr>
                  <th style='min-width: 90px'>Сумма</th>
                  <th style='min-width: 90px'>Время</th>
                  <th>Источник</th>
                  <th>Коментарий</th>
               </tr>
               </thead><tbody>
                  ";
    while ($p = $paymants->fetch_assoc()) {
        $hpay .= "<tr>
           <td style='width: 40px'>" . $p['money'] . "</td><td style='width: 80px'>" . $p['time'] . "</td><td>{$p['payment_type']}<td>" . $p['comment'] . "</td></tr>";
    }
    $hpay .= "</tbody></table>";
}
$all = $sql->query("SELECT sum(money) money FROM paymants WHERE agreement = %1", $form['id'])->fetch_assoc()['money'];

$hpay .= "<div style='color: green'>Всего платежей на сумму: <b>$all грн</b></div>";
#endregion

#region Выборка провайдеров
$provResult = $sql->query("SELECT id, name FROM service.providers ORDER BY  2 ");
while ($d = $provResult->fetch_assoc()) {
    $sel = $d['id'] == $form['provider'] ? "SELECTED" : "";
    $ht['provider'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}
#endregion

#region Просмотр баланса
if ($form['balance'] >= 0) $balance = "<span style='color: green'>" . $form['balance'] . "</span>"; else $balance = "<span style='color: red'>" . $form['balance'] . "</span>";
$paid_to = \envPHP\service\BillingDisableDay::getByAgreement($form['id']);
if ($paid_to == '') $paid_to = "<span style='color: darkgray'>Нет активаций</span>";
#endregion


#region Просмотр заявок

$statuses = [
    '' => "Нет отчета",
    'Нет отчетов' => "Нет отчета",
    'IN_PROCESS' => 'В процессе',
    'DONE' => 'Выполнена',
    'CANCEL' => 'Отменена',
];
$data = $sql->query("SELECT q.created, q.reason, q.`comment`, q.dest_time, e.name created_employee, report_status
FROM `questions_full` q
JOIN employees e on e.id = q.created_employee
 WHERE agreement = %1 order by created desc", $form['id']);
if ($data->num_rows != 0) {
    $questions = "<table class='table table-sm table-striped table-bordered' id='table_questions'>
        <thead>
           <tr>
             <th>Создана</th>
             <th>Создатель</th>
             <th>Причина</th>
             <th>На когда</th>
             <th>Коментарий</th>
             <th>Статус</th>
             </tr></thead><tbody>";
    while ($q = $data->fetch_assoc()) {
        if (isset($QUESTIONS['statuses'][$q['report_status']])) {
            $q['report_status'] = $QUESTIONS['statuses'][$q['report_status']];
        }
        $q['report_status'] = $q['report_status'] ? $q['report_status'] : "Нет отчетов";
        $questions .= "<tr><td>" . $q['created'] . "<td>" . $q['created_employee'] . "<td>{$q['reason']} <td>{$q['dest_time']}<td>{$q['comment']}<td>" . $statuses[$q['report_status']];
    }
    $questions .= "</tbody></table>";
}
#endregion

#region Просмотр прайсов
$data = $sql->query("SELECT cast(p.disable_day_static as date) disable_day_static, cast(p.disable_day as date) disable_day, p.id, cast(p.time_start as date) time_start,cast(p.time_stop as date) time_stop, b.`name`, b.price_day, binds, b.price_month, b.recalc_time
FROM client_prices p 
JOIN bill_prices b on b.id  = p.price 
LEFT JOIN (
SELECT DISTINCT activation,  '*'  binds FROM eq_bindings GROUP BY activation
UNION 
SELECT DISTINCT activation,  '*'  binds FROM trinity_bindings GROUP BY activation
) bd on bd.activation = p.id 
WHERE agreement = %1 order by id desc ", $form['id']);
$hprices = "<table class='table table-striped' style='width: 100%' id='table_prices'><thead><tr><th>ID</th><th>Услуга</th><th>Период</th><th>Прайс</th><th>Управление</th><tbody>";
while ($d = $data->fetch_assoc()) {
    $hrefF = '';
    $hrefD = '';
    if ($d['time_stop'] == '') {
        $hrefD = "<br><a onclick='confirm(\"Уверены, что хотите отключить услугу? При отключении услуги привязка будет удалена, абонент на оборудовании будет отключен\")?priceAction(\"price_stop\",{$d['id']}):false;' href='#'>Отключить</a>";
        $color = "green";
        if ($d['disable_day_static']) {
            $time_stop = "<span style='color: red'>{$d['disable_day_static']}</span>";
        } elseif ($d['disable_day']) {
            $time_stop = "<span style='color: gray'>{$d['disable_day']}</span>";
        } else {
            $time_stop = '---------------';
        }
    } elseif ($d['time_stop'] && $d['binds']) {
        $hrefF = "<a onclick='confirm(\"Уверены, что хотите активировать услугу?\")?priceAction(\"defrost\", {$d['id']}):false;' href='#'>Разморозить</a>";
        $color = 'darkred';
        $time_stop = $d['time_stop'];
    } else {
        $color = 'darkred';
        $time_stop = $d['time_stop'];
    }
    if (!$d['time_stop'] && $d['binds']) {
        $hrefF = "<a onclick='confirm(\"Уверены, что хотите приостановить услугу?\")?priceAction(\"frost\",{$d['id']}):false;' href='#'>Приостановить</a>" . "<br>";
    }
    $cost = $d['price_day'] . ' грн/сут';
    if ($d['recalc_time'] == 'month') {
        $cost = $d['price_month'] . ' грн/мес';
    }
    $hprices .= "<tr style='color: $color'>
<td style='color: $color'>{$d['id']}
<td style='color: $color'>{$d['name']}{$d['binds']}$hrefD
<td style='color: $color'>{$d['time_start']} - $time_stop
<td style='color: $color'>{$cost}
<td style='color: $color'>$hrefF";
}
$hprices .= "</tbody></table>";
#endregion

#region Получения списка прайсов
$price_list = "<SELECT name='price' class='form-control'>";
$data = $sql->query("SELECT id, name, price_month, price_day, recalc_time FROM bill_prices WHERE `show` = 1 and provider = '{$form['provider']}' order by name");
while ($d = $data->fetch_assoc()) {
    $cost = "({$d['price_day']} грн/сут)";
    if ($d['recalc_time'] == 'month') {
        $cost = "({$d['price_month']} грн/мес)";
    }
    $price_list .= "<OPTION value='{$d['id']}'>{$d['name']} {$cost}</OPTION>";
}
$price_list .= "</SELECT>";
#endregion

#region Получение инфы по последней активации
$d = $sql->query("SELECT p.time_start, p.time_stop, e.`name` activated, ed.`name` deactivated 
FROM client_prices p 
LEFT JOIN employees e on e.id = p.act_employee_id 
LEFT JOIN employees ed on ed.id = p.deact_employee_id
WHERE agreement = '{$form['id']}' 
ORDER BY p.id desc 
LIMIT 1 ")->fetch_assoc();
if ($d['time_start']) {
    if ($d['time_start']) {
        $ht['activation_name_info'] .= "<span style='color: darkgreen;'>Активировал - <b>{$d['activated']}</b></span><br>";
    }
    if ($d['time_stop']) {
        $ht['activation_name_info'] .= "<span style='color: darkred'>Деактивровал - <b>{$d['deactivated']}</b></span><br>";
    }
}
#endregion

#region Получение привязок оборудования
$HTML_binds = "<h5>Привязок по договору не найдено</h5>";
/*if (getGlobalConfigVar('RADIUS') && getGlobalConfigVar('RADIUS')['enabled']) {
    $bindings = $sql->query("
SELECT
 if(p.time_stop is not null, 'FROSTED', if(eq.ping > 0, 'OK', 'EQ_DOWN')) binding_status,
       b.id,pr.name price,eq.ip switch, b.port, b.ip, b.mac, a.updated_at last_requested_action, rs.real_mac, rs.hostname, rs.attached_ip, rs.active
FROM eq_bindings b
JOIN client_prices p on p.id = b.activation
JOIN bill_prices pr on pr.id = p.price
JOIN equipment eq on eq.id = b.switch
LEFT JOIN eq_bindings_activity a on a.binding_id = b.id
LEFT JOIN radius_binding_status rs on rs.binding_id = b.id
 WHERE agreement =  {$form['id']}");
    if ($bindings->num_rows > 0) $HTML_binds = "<table class='table  table-striped table-bordered'><thead>
<tr><th>Услуга<th>Свитч<th>Порт<th>IP<th>MAC<th>Диагностика<th><i class='fa fa-pencil-square-o'  ></i></th></tr></thead><tbody>";
    while ($b = $bindings->fetch_assoc()) {
        $notification = "";
        if ($b['real_mac'] !== '') {
            $notification .= "<tr><td colspan='7' style='padding-top: 1px'>";
            if ($b['real_mac'] && $b['mac'] !== $b['real_mac'] && $b['active'] == 'active') {
                $notification .= "<div style='color: darkred; font-weight: bold; font-size: 90%'>MAC-адрес абонента и MAC привязки не совпадает. Текущий MAC: {$b['real_mac']}</div>";
            }
            if ($b['attached_ip'] && $b['ip'] !== $b['attached_ip'] && $b['active'] == 'active') {
                $notification .= "<div style='color: darkred; font-weight: bold; font-size: 90%'>IP отличается от привязки: {$b['attached_ip']}</div>";
            }
            if ($b['real_mac'] && $b['attached_ip'] && $b['active'] !== 'active') {
                $notification .= "<div style='color: darkblue; font-weight: bold; font-size: 90%'>Последняя активность была {$b['active']} с MAC: {$b['real_mac']} (выдан IP - {$b['attached_ip']})</div>";
            }
            if ($b['hostname']) {
                $notification .= "<div style='color: darkblue; font-size: 90%'>Имя устройства: {$b['hostname']}</div>";
            }
            $notification .= "</td></tr>";
        }
        $style = 'color: black';
        if($b['binding_status'] === 'FROSTED') {
            $style = 'color: darkblue';
        }
        if($b['binding_status'] === 'EQ_DOWN') {
            $style = 'color: darkred';
        }
        $HTML_binds .= "<tr>
        <td style='{$style}'>{$b['price']}</td>
        <td style='{$style}'><a href='/sw/#/switcher/ports_info?ip={$b['switch']}' target='_blank'>{$b['switch']}</a></td>
        <td style='{$style}'>{$b['port']}</td>
        <td style='{$style}'>{$b['ip']}</td>
        <td style='{$style}'>{$b['mac']}<br><small>{$b['last_requested_action']}</small></td>
        <td style='{$style}'><a href='?id={$form['id']}&checkPort={$b['id']}'>Проверить</a><br><a href='?id={$form['id']}&ping={$b['ip']}'>Пропинговать</a></td>
        <td style='{$style}'><a href='/equipment/bindings?binding={$b['id']}&search=1'><i class='fa fa-pencil-square-o' style='font-size: 26px'></i> </a></td>
        </tr>
        $notification

";
    }
} else {*/
    $bindings = $sql->query("SELECT b.id,pr.name price,eq.ip switch, b.port, b.ip, b.mac, a.updated_at last_requested_action
FROM eq_bindings b 
JOIN client_prices p on p.id = b.activation
JOIN bill_prices pr on pr.id = p.price
JOIN equipment eq on eq.id = b.switch
LEFT JOIN eq_bindings_activity a on a.binding_id = b.id 
 WHERE agreement = {$form['id']}");
    if ($bindings->num_rows > 0) $HTML_binds = "<table class='table  table-striped table-bordered'><thead>
<tr><th>Услуга<th>Свитч<th>Порт<th>IP<th>MAC<th>Диагностика<th><i class='fa fa-pencil-square-o'  ></i></th></tr></thead><tbody>";
    while ($b = $bindings->fetch_assoc()) {
        $HTML_binds .= "<tr>
        <td>{$b['price']}</td>
        <td><a href='".conf('BASE.wildcore')."/info/device?ip={$b['switch']}' target='_blank'>{$b['switch']}</a></td>
        <td>{$b['port']}</td>
        <td>{$b['ip']}</td>
        <td>{$b['mac']}<br><small>{$b['last_requested_action']}</small></td>
        <td><a href='?id={$form['id']}&checkPort={$b['id']}'>Проверить</a><br><a href='?id={$form['id']}&ping={$b['ip']}'>Пропинговать</a></td>
        <td><a href='/equipment/bindings?binding={$b['id']}&search=1'><i class='fa fa-pencil-square-o' style='font-size: 26px'></i> </a></td>
";
    }
//}
$HTML_binds .= "</tbody></table>";
#endregion

#region Получение привязок trinity
$ht['trinity_binds'] = "<h5>Привязок TrinityTV по договору не найдено</h5>";
$bindings = $sql->query("SELECT b.id,pr.name price, b.mac, b.uuid, b.local_playlist_id
FROM trinity_bindings b 
JOIN client_prices p on p.id = b.activation
JOIN bill_prices pr on pr.id = p.price
 WHERE agreement = {$form['id']}");
if ($bindings->num_rows > 0) $ht['trinity_binds'] = "<table class='table  table-striped table-bordered'><thead>
<tr><th>ID<th>Услуга<th>MAC<th>UUID</th><th><i class='fa fa-pencil-square-o'  ></i></th></tr></thead><tbody>";
while ($b = $bindings->fetch_assoc()) {
    $uuid = $b['uuid'];
    if ($b['local_playlist_id']) {
        $url = getGlobalConfigVar('BASE')['api2_front_addr'] . '/playlist/' . $b['local_playlist_id'];
        $uuid = "<a href='$url' target='_blank'>$url</a>";
    }
    $ht['trinity_binds'] .= "<tr>
        <td>{$b['id']}
        <td>{$b['price']}
        <td>{$b['mac']}
        <td>{$uuid}
        <td><a href='/trinity/bindings?binding={$b['id']}&search=1'><i class='fa fa-pencil-square-o' style='font-size: 26px'></i> </a>
";
}
$ht['trinity_binds'] .= "</tbody></table>";
#endregion

#region Диагностика порта по привязке
//Проверка порта
if ($check) {
//Проверка порта
    $bindInfo = $sql->query("SELECT b.id, eq.ip switch, b.port, b.ip, b.mac , ac.community 
FROM eq_bindings b  
JOIN equipment eq on eq.id = b.switch 
JOIN equipment_access ac on ac.id = eq.access 
WHERE b.id = {$form['checkPort']}")->fetch_assoc();
    try {
        $switcher = new \envPHP\classes\DlinkSwitcherCore($bindInfo['switch'], $bindInfo['community'], "", "");
        $data = $switcher->getPortFullInfo($bindInfo['port']);
        $test_port = "<h4>Диагностика порта по привязке</h4>
        <div style='overflow-x: auto; width: 100%'><table class='table table-striped table-bordered'>
            <tr>
                <th style='min-width: 150px'>Состояние порта
                <th style='min-width: 150px'>Влан
                <th>Ошибки<br><small>(CRC/JB/FRG/Col)</small>
                <th style='min-width: 100px'>Kаб. диаг<th>MAC
                </th>
            </tr><tr><td>";
        $value = $data[$bindInfo['port']];
        foreach ($value['link'] as $medium_type => $link_data) {
            if ($medium_type == "Fiber") $test_port .= "(F)";
            $test_port .= "{$link_data['admin_state']}/{$link_data['oper_status']}/{$link_data['nway_status']}<br>";
        }
        $test_port .= "<td>";
        foreach ($value['vlan'] as $vl) {
            $test_port .= "{$vl['id']}: {$vl['name']} ({$vl['type']})<br>";
        }
        $test_port .= "<td>{$value['crc_align_errors']}/{$value['undersize_pkts']}/{$value['oversize_pkts']}/{$value['fragments']}/{$value['jabber']}/{$value['collisions']}";
        $test_port .= "<td>";
        foreach ($value['cable_diag'] as $pair) {
            switch ($pair['status']) {
                case 'NoCable':
                    $color = "blue";
                    break;
                case 'OK':
                    $color = "darkgreen";
                    break;
                case 'Short':
                    $color = "red";
                    break;
                default:
                    $color = "black";
            }
            $test_port .= "<span style='color: {$color}'>";
            $test_port .= "{$pair['number']}: {$pair['status']}";
            if ($pair['status'] != 'NoCable') {
                $test_port .= " - {$pair['length']}M";
            }
            $test_port .= "</span><br>";
        }
        $test_port .= "<td>";
        foreach ($value['fdb'] as $val) {
            $test_port .= "{$val['mac']}({$val['vlan_id']})<br>";
        }
        $test_port .= "</table></div>";

    } catch (\Exception $e) {
        $test_port = "Возникла ошибка при опросе оборудования<br>";
        $test_port .= "<pre>
Ошибка детальнее: {$e->getMessage()}
{$e->getTraceAsString()}</pre>";
    }
}
if ($checkPing) {
    $out = envPHP\classes\ping::go($checkPing);
    $ping_ip = "<pre>$out</pre>";
}
#endregion


# region Another agreements
$ht['another_agree'] = "";
$data = $sql->query("SELECT cl.agreement, cl.id, cl.`name`, IFNULL(PRnames, 'Нет активных прайсов') prices 
                        FROM `clients` cl 
                        LEFT JOIN (SELECT agreement, GROUP_CONCAT(DISTINCT pr.`name`) PRnames 
                        FROM client_prices cl 
                        LEFT JOIN bill_prices pr on pr.id = cl.price 
                        WHERE time_stop is null 
                        GROUP BY agreement) pr on pr.agreement = cl.id 
                        WHERE house = {$form['houseId']} and apartment = '{$form['apartment']}' and cl.status = 'ENABLED'
                        and cl.agreement != '{$form['agreement']}';");
if ($data->num_rows == 0) {
    $ht['another_agree'] = "<br><b>Смежных договоров не найдено</b>";
} else {
    while ($d = $data->fetch_assoc()) {
        $ht['another_agree'] .= "<a href='detail?id={$d['id']}'>{$d['agreement']}</a> - {$d['name']} - {$d['prices']}<br>";
    }
}
#endregion


#region Omo control
$omo = new OmoLocalControl();
$devices = $omo->deviceFindByHouseEntrance($form['houseId'], $form['entrance']);
if (count($devices) == 0) {
    $ht['omo_systems_block'] = "<h4>Устройств по данному адресу не найдено</h4>";
} else {
    $ht['omo_systems_block'] = "
    <table class='table table-bordered table-striped table-sm'>
        <thead>
           <tr>
                <th>Тип устройства</th>            
                <th>Статус</th>            
                <th>Детали установки</th>            
                <th><i class='fa fa-pencil-square-o'></i></th>            
</tr></thead><tbody>";
    foreach ($devices as $dev) {
        $detail = "<small>";
        if ($dev['entrance']) $detail .= "Подьезд: {$dev['entrance']}<br>";
        if ($dev['floor']) $detail .= "Этаж: {$dev['floor']}<br>";
        if ($dev['apartment']) $detail .= "Квартира: {$dev['apartment']}<br>";
        $detail .= "</small>";
        $ht['omo_systems_block'] .= "
            <td>{$dev['type']}</td>
            <td>{$dev['status']}</td>
            <td>{$detail}</td>
            <td><a href='#' onclick='omoModal({$dev['id']}); return false;'><i class='fa fa-pencil-square-o'></i></a></td>
        ";
    }

    $ht['omo_systems_block'] .= "</tbody></table>";
}
#endregion


#region Extra contacts
$contacts = \envPHP\structs\ClientContact::getAllContacts($form['id']);
if (count($contacts) !== 0) {
    $ht['contacts'] = <<<HTML
<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Тип</th>
            <th>Имя</th>
            <th>Контакт</th>
            <th>Осн.</th>
        </tr>
    </thead>
    <tbody>
HTML;
    foreach ($contacts as $contact) {
        $checked = $contact->isMain() ? "checked" : "";
        $contactLink = $contact->getValue();
        if ($contact->getType() == 'EMAIL') {
            $contactLink = "<a href='to:{$contact->getValue()}'>{$contact->getValue()}</a>";
        } elseif (in_array($contact->getType(), ['PHONE', 'VIBER', 'TELEGRAM'])) {
            $contactLink = "<a href='tel:{$contact->getValue()}'>{$contact->getValue()}</a>";
        }
        $ht['contacts'] .= <<<HTML
     <tr>
        <td>{$contactTypes[$contact->getType()]}</td>   
        <td>{$contact->getName()}</td>   
        <td>{$contactLink}</td>   
        <td><INPUT type='checkbox' class='form-control' $checked onclick="return false;"></td>   
     </tr>
HTML;
    }
    $ht['contacts'] .= <<<HTML
</tbody></table> 
HTML;

}
# endregion


?>
<?= tpl('head', ['title' => '']) ?>
<script type="text/javascript">
    window.onload = function () {
        setTimeout(function () {
            $('#message_success').fadeOut()
        }, 2000);
        setTimeout(function () {
            $('#message_fail').fadeOut()
        }, 10000);
    };
</script>

<!-- ОМО device control modal window -->
<div class="modal" tabindex="-1" role="dialog" id="diag-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Диагностика интерфейса</h4>
            </div>
            <div id="diag-preload">
                <div class="modal-body">
                    <h3 align="center">Ожидайте, проводится диагностика...<br><img src="/res/img/spinner-blue.gif"
                                                                         style="height: 64px; width: 64px; margin-top: 10px">
                    </h3>
                </div>
            </div>
            <div class="modal-body" id="diag-body" style="display: none">
                <div class="container-fluid">
                    <div class="row">

                    </div>
                </div>
            </div>
            <div class="modal-footer" id="diag-footer" style="display: none">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>


<!-- ОМО device control modal window -->
<div class="modal" tabindex="-1" role="dialog" id="omoModal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Управление доступом OMO</h4>
            </div>
            <div id="omoPreload">
                <div class="modal-body">
                    <h3 align="center">Ожидайте, работа с API...<br><img src="/res/img/spinner-blue.gif"
                                                                         style="height: 64px; width: 64px; margin-top: 10px">
                    </h3>
                </div>
            </div>
            <div class="modal-body" id="omoBody" style="display: none">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-8 col-sm-12 col-md-8 col-xs-12" id="omoDeviceInfo">
                        </div>
                        <div class="col-lg-4 col-sm-12 col-md-4 col-xs-12">
                            <form onsubmit="omoAddDevice(); return false" id="omoFormAddPhone">
                                <input name="agreement_id" value="<?= $form['id'] ?>" hidden type="hidden">
                                <input name="device_id" value="" hidden type="hidden" id="omoDeviceId">
                                <input name="phone" value="" class="form-control" style="margin-bottom: 5px"
                                       data-inputmask="'mask': '+99 (999) 999-99-99'"
                                       pattern="^\+\d{2} \(\d{3}\) \d{3}-\d{2}-\d{2}$" required>
                                <button type="submit" class="btn btn-primary btn-block" style="margin-bottom: 5px">
                                    Закрепить новый телефон
                                </button>
                            </form>
                        </div>
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12" id="omoPhonesList">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="omoModalFooter" style="display: none">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Contacts control window -->
<div class="modal" tabindex="-1" role="dialog" id="contactModal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="vue-contact-modal">
            <div class="modal-header">
                <h4 class="modal-title">Управление контактами</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row" v-if="loading">
                        <div id="contactPreload">
                            <div class="modal-body">
                                <h3 align="center">Ожидайте, работа с API...<br><img src="/res/img/spinner-blue.gif"
                                                                                     style="height: 64px; width: 64px; margin-top: 10px">
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="row" v-if="!loading">
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                            <table style="width: 100%">
                                <thead>
                                <th style="padding-left: 10px">Тип</th>
                                <th style="padding-left: 10px">Имя</th>
                                <th style="padding-left: 10px">Контакт</th>
                                <th style="padding-left: 10px">Осн</th>
                                <th style="padding-left: 10px">X</th>
                                </thead>
                                <tbody id="contacts-list">
                                <tr v-for="(contact, index) in currentContacts" :key="contact.id">
                                    <td style="padding: 5px">
                                        <SELECT v-model="currentContacts[index].type" class="form-control"
                                                style="margin: 5px">
                                            <OPTION v-for="type in contactTypes" :key="type.key" :value="type.key">
                                                {{type.value}}
                                            </OPTION>
                                        </SELECT>
                                    </td>
                                    <td style="padding: 5px">
                                        <INPUT v-model="currentContacts[index].name" class="form-control"
                                               style="width: 100%" placeholder="Имя контакта">
                                    </td>
                                    <td style="padding: 5px">
                                        <INPUT v-model="currentContacts[index].value" class="form-control" :placeholder="contact.placeholder"
                                               style="width: 100%">
                                        <div style="font-size: 90%; color: darkred" v-if="contact.error !== ''">
                                            {{contact.error}}
                                        </div>
                                    </td>
                                    <td style="padding: 5px">
                                        <INPUT type="checkbox" v-model="currentContacts[index].main"
                                               class="form-control">
                                    </td>
                                    <td style="padding: 5px">
                                        <button v-on:click="deleteContact(contact.id)" class="btn btn-danger btn-sm">X
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td style="padding: 5px">
                                        <button v-on:click="addContact()" class="btn btn-success btn-sm">+</button>
                                    </td>
                                </tr>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="omoModalFooter" v-if="!loading">
                <button type="button" class="btn btn-primary" v-on:click="saveContacts();">Сохранить изменения</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
        <div class="x_panel">
            <div class="x_title">
                <h2>Договор: <b><?= $form['agreement'] ?> </b>
                    <small>от <?= $form['add_time'] ?></small>
                </h2>
                <a class='btn btn-default' href='?id=<?= $form['id'] ?>' target="_blank" style=" float: right">
                    <i class="fa fa-file-text-o" aria-hidden="true"
                       style="padding: 0; padding-left: 10px; padding-right: 10px;  font-size: 18px"></i>
                </a>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="post">
                    <?php if ($form['client_status'] != 'ENABLED') echo "<h2 style='color: red'>Внимание, договор отключен!!!</h2>" ?>
                    <div>
                        <b>Обращение</b>: <input name='name' class="form-control" value="<?= $form['name'] ?>"
                                                 style='display: inline; width: 300px'>
                        <button style='display: inline; margin-top: 3px' type="submit" name="action"
                                class="btn btn-primary" value='rename'><i class="fa fa-floppy-o"
                                                                          style="padding: 0; font-size: 18px"></i>
                        </button>
                    </div>
                    <div>
                        <b>Провайдер</b>:
                        <SELECT name="provider" class="form-control" style='display: inline; width: 200px'>
                            <?= $ht['provider'] ?>
                        </SELECT>
                        <button style='display: inline;  ' type="submit" name="action" class="btn btn-primary"
                                value='edit_provider'><i class="fa fa-floppy-o" style="padding: 0; font-size: 18px"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
        <div class="x_panel">
            <div class="x_title">
                <h2>Адрес</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="post">
                    <div class="form-horizontal form-label-left input_mask row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group">
                            <span style="font-size: 14px; font-weight: bold"> г. <?= $form['city'] ?>,  <?= $form['street'] ?>, д.  <?= $form['house'] ?><?= $ht['group'] ?>,<b> под.<?= $form['entrance'] ?>, кв.<?= $form['apartment'] ?> </b></span>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2 form-group">
                            <label class="control-label">Подьезд</label>
                            <input name='entrance' class="form-control" value='<?= $form['entrance'] ?>' placeholder="1"
                                   pattern="[0-9]{1,3}">
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2 form-group">
                            <label class="control-label">Этаж</label>
                            <input name='floor' class="form-control" value='<?= $form['floor'] ?>' required
                                   placeholder="1">
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2 form-group">
                            <label class="control-label">Квартира</label>
                            <input name='apartment' class="form-control" value='<?= $form['apartment'] ?>' required
                                   placeholder="1">
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2 form-group">
                            <label class="control-label">&nbsp; </label>
                            <button class="btn btn-primary btn-block" type="submit" name='action' value="change_addr"><i
                                        class="fa fa-floppy-o" style="padding: 0; font-size: 18px"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
        <div class="x_panel">
            <div class="x_title">
                <h2>Услуги</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <form action="?id=<?= $form['id'] ?>" id="priceForm" method="post">
                        <input name="price" id="priceId" type="hidden" hidden>
                        <input name="action" id="priceAction" type="hidden" hidden>
                        <div style='font-size: 16px; font-weight: bold'>Баланс: <?= $balance ?><span
                                    style="font-weight: normal"> грн, оплачено до: <?= $paid_to ?>*</span></div>
                        <?= $ht['activation_name_info'] ?>
                        <div class="table-responsive-light">
                            <?= $hprices ?>
                            <br>
                            <small>* "Оплачено до" берется из минимального дня отключения любой из активированных
                                услуг
                            </small>
                            <br>
                            <small>** День отключения услуги расчитывается на основе баланса, активированных услуг, дней
                                смещения отключений и кредитного периода и может изменятся. Дни отключения услуг
                                абонентам не сообщаем(дабы не заводить в заблуждение), можно сообщать только "Оплачено
                                до"
                            </small>
                            <br>
                        </div>
                        <div class="col-sm-12">
                            <div class="divider-dashed"></div>

                        </div>
                    </form>
                </div>
                <form action="?id=<?= $form['id'] ?>" method="post"
                      class="form-horizontal form-label-left input_mask row">
                    <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8 form-group">
                        <label class="control-label">Добавить прайс</label>
                        <?= $price_list ?>
                    </div>
                    <div class="col-xs-4 col-sm-4 col-md-4  col-lg-4 form-group">
                        <label class="control-label">&nbsp; </label>
                        <button method="POST" name="action" value="price_start" class="btn btn-primary btn-block">
                            Добавить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php if (PSC::isPermitted('payment_show')) { ?>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">

            <div class="x_panel">
                <div class="x_title">
                    <h2>Платежи</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="row">
                        <div class="table-responsive-light"><?= $hpay ?></div>
                        <a href='/paymants/add?agreement=<?= $form['agreement'] ?>' class="btn btn-sm btn-primary">Внести
                            платеж </a>
                        <a href='/paymants/search?agreement=<?= $form['agreement'] ?>&action=search'
                           class="btn btn-sm btn-primary">Просмотреть все платежи</a>
                        <a href='/abonents/purpose_of_payment?search=<?= $form['agreement'] ?>&action=search'
                           class="btn btn-sm btn-primary">Печать квитанций</a>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
        <div class="x_panel">
            <div class="x_title">
                <h2>Привязки оборудования
                    <small>работа с оборудованием</small>
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form action="?id=<?= $form['id'] ?>" method="post">
                    <div class="table-responsive-light"><?= $HTML_binds ?></div>
                    <?= $test_port ?>
                    <?= $ping_ip ?>
                    <a href="/equipment/bindingsAdd?agreement=<?= $form['agreement'] ?>" class="btn-primary btn">Добавить
                        привязку</a>
                </form>
            </div>
        </div>

        <?php if (getGlobalConfigVar('TRINITY') && getGlobalConfigVar('TRINITY')['enabled']) { ?>
            <div class="x_panel">
                <div class="x_title">
                    <h2>Привязки OTT
                        <small>работа с TrinityTV (sweet.tv)</small>
                    </h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form action="?id=<?= $form['id'] ?>" method="post">
                        <div class="table-responsive-light"><?= $ht['trinity_binds'] ?></div>
                        <a href="/trinity/add_binding?agreement=<?= $form['agreement'] ?>" class="btn-primary btn">Добавить
                            привязку</a>
                    </form>
                </div>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">

                <div class="x_panel">
                    <div class="x_title">
                        <h2>Уведомления абонента</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form action="?id=<?= $form['id'] ?>" method="post">
                            Уведомление по
                            Email <?= $html->formCheckbox('notice_mail', $form['notice_mail'], $style = '') ?><br>
                            Уведомление по
                            SMS <?= $html->formCheckbox('notice_sms', $form['notice_sms'], $style = '') ?><br>
                            <button name='action' value="notice" class="btn btn-primary btn-block btnPdn" type="submit">
                                Сохранить параметры
                            </button>
                        </form>
                        <form action="?id=<?= $form['id'] ?>" method="post">
                            <br>
                            <h5>Отправка CMC сообщения</h5>
                            <small>Текст сообщения</small>
                            <textarea class="form-control" style="width: 100%; height: 120px;"
                                      name="sms_text"><?= $form['sms_text'] ?></textarea>

                            <button name='action' value="send_sms" class="btn btn-primary btn-block btnPdn btn-sm"
                                    type="submit">Отправить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">


                <div class="x_panel">
                    <div class="x_title">
                        <h2>Автокредитование</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <h4>Автоматическая система кредитования</h4>
                        <form action="?id=<?= $form['id'] ?>" method="post">
                            <?php
                            if (@$form['enable_credit']) {

                                $credit_enable = "CHECKED";
                            } else {
                                $credit_enable = "";
                            }

                            ?>
                            Включена: <input name="enable_credit" type="checkbox" value="1"
                                             style="width: 26px; margin: 5px; padding-bottom: -10px" <?= $credit_enable ?> ><br>
                            <button name='action' value="edit_credit" class="btn btn-primary btn-block btnPdn"
                                    type="submit">Сохранить
                            </button>
                            <div class="divider-dashed"></div>
                            <h4>Кредитный период</h4>
                            <?= $ht['credit_status'] ?><br>


                            <div class="divider-dashed"></div>
                            <h4>Лимиты на отключение при долге</h4>
                            <?= $ht['disable_limit_info'] ?><br>
                            <br>Установить дней:
                            <input class="slider form-control" type="text" name="disable_limit_days"
                                   value="<?= $form['disable_limit_days'] ?>">
                            <button name='action' value="set_custom_day_limit" type="submit"
                                    class="btn btn-primary btn-block btnPdn btn-sm">Сохранить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
        <div class="row">

            <?php if (PSC::isPermitted('question_show')) { ?>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Заявки </h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <div class="table-responsive-light"><?= $questions ?></div>
                            <div class="clearfix" style="margin-top: 10px"></div>
                            <div class="row">
                                <div class="col-md-5 col-sm-5 col-lg-4">
                                    <a href='/abonents/new_questions?agree=<?= $form['agreement'] ?>&noClear'
                                       class="btn btn-primary btn-sm btn-block">Внести новую заявку</a><br>
                                </div>
                                <div class="col-md-5 col-sm-5 col-lg-5">
                                    <a href='/abonents/questions?agreement=<?= $form['agreement'] ?>&action'
                                       class="btn btn-primary btn-sm btn-block">Посмотреть все заявки по
                                        договору</a><br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="x_panel">
                    <div class="x_title">
                        <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10">
                            <h2>Контакты</h2>
                        </div>
                        <?php if(PSC::isPermitted('customer_change_contacts')) {?>
                        <div class="col-sm-2 col-xs-2 col-lg-2 col-md-2">
                            <button onclick="editContacts('<?= $form['id'] ?>')" style="height: 30px;"
                                    class="btn btn-primary btn-sm btn-block"><i class="fa fa-pencil-square-o"
                                                                                style="font-size: 20px;   margin-left: 3px"></i>
                            </button>
                        </div>
                        <?php } ?>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12 col-md-12 col-lg-12">
                                <div class="table-responsive">
                                    <?= $ht['contacts'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Описание абонента</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form action="?id=<?= $form['id'] ?>" method="post">
                            <textarea name='descr' class="form-control"
                                      style='width: 100%;height: 150px'><?= $form['descr'] ?></textarea>
                            <button name='action' value="descr" type="submit" class="btn btn-primary btn-block btnPdn">
                                Сохранить описание
                            </button>
                        </form>
                    </div>
                </div>
                <?php if (getGlobalConfigVar('OMO_SYSTEMS') && getGlobalConfigVar('OMO_SYSTEMS')['enabled'] && PSC::isPermitted('omo_display')) { ?>
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>OMO Systems</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <div style='overflow-x: auto; width: 100%'>
                                <?= $ht['omo_systems_block'] ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php if (PSC::isPermitted('customer_related')) { ?>
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Смежные договора</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <?= $ht['another_agree'] ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <?php if (PSC::isPermitted('customer_change_password')) { ?>
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Пароль в ЛК</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <form action="?id=<?= $form['id'] ?>" method="post"
                                  class="form-horizontal form-label-left input_mask row">
                                <div class="col-xs-8 col-sm-8 col-md-9 col-lg-9 form-group">
                                    <label for="password" class="control-label">Новый пароль</label>
                                    <input name='password' type="password" class="form-control"
                                           value='<?= $form['password'] ?>'>
                                </div>
                                <div class="col-xs-4 col-sm-4 col-md-3  col-lg-3 form-group">
                                    <label for="action" class="control-label">&nbsp;</label>
                                    <button name='action' value="password" type="submit"
                                            class="btn btn-primary btn-block"><i class='fa fa-pencil-square-o'></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>
                <?php if ($form['client_status'] != 'DISABLED' && PSC::isPermitted('customer_disable_agreement')) { ?>
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Управление договором</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <form action="?id=<?= $form['id'] ?>" method="post"
                                  class="form-horizontal form-label-left input_mask row">
                                <div class="col-xs-12 col-sm-12 col-md-12  col-lg-12 form-group">
                                    <label for="action" class="control-label">Отключение договора </label>
                                    <br>
                                    <small>Отключение договора приведет к блокировке личного кабинета абонента,
                                        невозможности провести по нему оплаты, а так же договор не будет отображаться в
                                        поиске
                                    </small>
                                    <br>
                                    <button name='action' value="disable_client" type="submit" class="btn btn-danger "
                                            onclick="if(!confirm('Уверены, что хотите отключить договор?')) return false; else return true;">
                                        Отключить договор
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" type="text/less" href="/res/slider/less/slider.less"/>
<link rel="stylesheet" type="text/css" href="/res/slider/css/slider.css"/>
<script src="/res/slider/js/bootstrap-slider.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    const defaultOptions = {
        baseURL: "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>",
        headers: {
            'Content-Type': 'application/json',
            'X-Auth-Key': getApiToken(),
        },
    };
    AXI = axios.create(defaultOptions);
    var app = new Vue({
        el: '#vue-contact-modal',
        data: {
            changesExist: false,
            message: 'test',
            currentContacts: [],
            oldContacts: [],
            apiKey: '',
            loading: true,
            agreement_id: 0,
            contactTypes: [
                {key: 'PHONE', value: 'Телефон'},
                {key: 'EMAIL', value: 'Email'},
            ],
        },

        watch: {
            currentContacts: {
                deep: true,
                handler(n) {
                    console.log("Detect changes in contact form");
                    n.forEach((elem, k) => {
                        if (!this.oldContacts[k]) return;
                        if (elem.main !== this.oldContacts[k].main) {
                            console.log("Detected changes in main checkbox");
                            this.mainSetter(k, elem.main);
                        }
                        if (elem.type !== this.oldContacts[k].type) {
                            this.currentContacts[k].placeholder = this.getPlaceholder(elem.type);
                            this.currentContacts[k].main = false;
                            this.mainSetter(k, false);
                        }
                    })
                    this.oldContacts = JSON.parse(JSON.stringify(n));
                }
            },
            errors: [],
        },
        methods: {
            saveContacts() {
                let promises = [];
                this.loading = true;
                var contacts = this.currentContacts;

                contacts.forEach(el => {
                    console.log(el);
                    if(!el.type) return;
                    if(el.id <= 0) {
                        promises.push(AXI.post("/v2/private/customers/contacts", {
                            "type": el.type,
                            "value": el.value,
                            "agreement_id": this.agreement_id,
                            "name": el.name,
                            "main": el.main,
                        }))
                    } else {
                        promises.push(AXI.put("/v2/private/customers/contacts/" + el.id, {
                            "type": el.type,
                            "value": el.value,
                            "name": el.name,
                            "main": el.main,
                        }))
                    }
                })
                var hasErrors = false;
                Promise.all(promises).catch(err => {
                    hasErrors = true;
                    this.showNoty('error', err.response.data.error.description);

                }).finally(() => {
                    this.loadContacts();
                })
                if(!hasErrors) {
                    this.showNoty('success', 'Контакты успешно обновлены');
                }


            },
            getPlaceholder(type) {
                let placeholder = '';
                if(type === 'PHONE') {
                    placeholder = '+3800634190768'
                } else if (type === 'EMAIL') {
                    placeholder = 'my.mail@example.com';
                }
                return placeholder;
            },
            mainSetter(keyOfMain, mainValue) {
                setTimeout(() => {
                var type = this.currentContacts[keyOfMain].type;
                var contacts = [];
                this.currentContacts.forEach((elem, k) => {
                    if (type !== elem.type) return;
                    contacts.push({main: elem.main, id: k});
                });
                //Значение установлено в true - уберем все остальные галочки
                if (mainValue) {
                    contacts.forEach(elem => {
                        if (elem.id === keyOfMain) return;
                        this.currentContacts[elem.id].main = false;
                    });
                }
                //Ищем хотя бы один основной элемент
                var mainElementSetted = false;
                contacts.forEach(elem => {
                    if (elem.main) {
                        mainElementSetted = true;
                    }
                    ;
                });
                contacts.forEach(elem => {
                    if (mainElementSetted) return;
                    this.currentContacts[elem.id].main = true;
                    mainElementSetted = true;
                });
                }, 10);
            },
            loadContacts() {
                console.log("Start loading contacts");
                this.loading = true;
                this.currentContacts = [];
                AXI.get("/v2/private/customers/contacts?agreement_id=" + this.agreement_id).then((r) => {
                    r.data.data.forEach(c => {

                        this.currentContacts.push({
                            id: c.id,
                            name: c.name,
                            type: c.type,
                            value: c.value,
                            error: '',
                            placeholder: this.getPlaceholder(c.type),
                            regex: '',
                            main: c.main,
                        });
                    })
                    this.loading = false;
                })
            },
            deleteContact(id) {
                if (confirm("Do you really want to delete?")) {
                    AXI.delete("/v2/private/customers/contacts/" + id).finally(() => {
                        this.loadContacts();
                    })
                }
            },
            addContact() {
                this.currentContacts.push({
                    id: this.getMinId(),
                    type: '',
                    name: '',
                    value: '',
                    main: false,
                })
            },
            getMinId() {
                var minId = 0;
                this.currentContacts.forEach(e => {
                    if (e.id < minId) {
                        minId = e.id;
                    }
                });
                return minId - 1;
            },
            showNoty(type, text) {
                if (typeof Noty !== 'undefined') {
                    new Noty({
                            type: type,
                            layout: 'topRight',
                            theme: 'metroui',
                            text: text,
                            timeout: '4000',
                            progressBar: true,
                            closeWith: ['clock'],
                        }
                    ).show();
                } else {
                    setTimeout(() => {
                        this.showModal(type, text)
                    }, 500)
                }
            }
        },
    })

    function editContacts(agreement_id) {
        $('#contactBody').hide();
        $('#contactPreload').show();
        $('#contactModal').modal('show');
        app.agreement_id = agreement_id;
        app.loadContacts();
    }
</script>
<script>
    function omoAddDevice() {
        var formData = {};
        $('#omoBody').hide();
        $('#omoPreload').show();
        $("#omoFormAddPhone").find("input[name]").each(function (index, node) {
            formData[node.name] = node.value;
        });
        $.ajax({
            "url": "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>/omo/user/share",
            "method": "POST",
            "dataType": 'json',
            "data": JSON.stringify(formData),
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        }).done(function (data) {
            reloadTable();
            $('#omoBody').show();
            $('#omoPreload').hide();
        }).error(function (data) {
            console.log(data);
            alert(data.responseJSON.error.description);
        });
    }

    function removePhone(phone) {
        var formData = {};
        $("#omoFormAddPhone").find("input[name]").each(function (index, node) {
            formData[node.name] = node.value;
        });
        formData['phone'] = phone;
        $.ajax({
            "url": "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>/omo/user/revoke",
            "method": "POST",
            "dataType": 'json',
            "data": JSON.stringify(formData),
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        }).done(function (data) {
            reloadTable();
        }).error(function (data) {
            console.log(data);
            alert(data.responseJSON.error.description);
        });
    }

    function reloadTable() {
        var formData = {};
        $("#omoFormAddPhone").find("input[name]").each(function (index, node) {
            formData[node.name] = node.value;
        });
        $.ajax({
            "url": "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>/omo/user?agreement_id=" + formData['agreement_id'] + "&device_id=" + formData['device_id'],
            "method": "GET",
            "dataType": 'json',
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        }).done(function (data) {
            var table = "<table class='table table-bordered table-striped'><thead><tr><th>Добавлен</th><th>Номер телефона</th><th>Статус</th><th>Удалить</th></tr></thead><tbody>";
            data.data.forEach(function (item, i, arr) {
                stat = "Не подтвержден";
                if (item.uid !== "") {
                    stat = "Подтвержден";
                }
                table += "<tr>" +
                    "<td>" + item.created_at + "</td>" +
                    "<td>" + item.phone + "</td>" +
                    "<td>" + stat + "</td>" +
                    "<td><a href='#' onclick='if(confirm(\"Уверен?\")) removePhone(\"" + item.phone + "\");'>Удалить</td>";

            });
            table += "</table>";
            $('#omoPhonesList').html(table);
        }).error(function (data) {
            console.log(data);
            alert(data.responseJSON.error.description);
        });
    }


    // With JQuery
    $('.slider').slider({
        min: -10,
        max: 90,
        step: 1,
        orientation: 'horizontal',
        value: <?=$form['disable_limit_days']?>,
        selection: 'before',
        tooltip: 'show'
    });

    function priceAction(action, priceId) {
        $('#priceAction').val(action);
        $('#priceId').val(priceId);
        $('#priceForm').submit();
    }

    function omoModal(id) {
        $('#omoDeviceId').val(id);
        $('#omoModalFooter').hide();
        $('#omoBody').hide();
        $('#omoPreload').show();
        $('#omoModal').modal('show');
        var settings = {
            "url": "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>/omo/device/" + id,
            "method": "GET",
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        };
        reloadTable();
        $.ajax(settings).success(function (response) {
            $('#omoDeviceInfo').html("" +
                "Тип устройства: <b>" + response.data.type + "</b><br>\n" +
                "Адрес установки: <b>" + response.data.description + "</b><br>\n" +
                "Детали установки (этаж/квартира): <b>" + response.data.floor + "/" + response.data.apartment + "</b><br>\n" +
                "Статус: <b>" + response.data.status + "</b><br>\n"
            );
            $('#omoModalFooter').show();
            $('#omoBody').show();
            $('#omoPreload').hide();
        });
    }
</script>
<script>

    $(document).ready(function () {
        $('#table_prices').DataTable({
            "language": {
                "lengthMenu": "Отображено _MENU_ записей на странице",
                "zeroRecords": "К сожалению, записей не найдено",
                "info": "Показана  страница _PAGE_ с _PAGES_",
                "infoEmpty": "Нет записей",
                "infoFiltered": "(filtered from _MAX_ total records)",
                "search": "Живой фильтр:",
                "paginate": {
                    "first": "Первая",
                    "last": "Последняя",
                    "next": "Следующая",
                    "previous": "Предыдущая"
                },
            },
            "order": [[0, 'desc']],
            "ordering": false,
            "searching": false,
            "scrollX": true,
            "bLengthChange": false,
            "lengthMenu": [[10, 30, 100, -1], [10, 30, 100, "Все"]]
        });
    });
    $(document).ready(function () {
        $('#table_payments').DataTable({
            "language": {
                "lengthMenu": "Отображено _MENU_ записей на странице",
                "zeroRecords": "К сожалению, записей не найдено",
                "info": "Показана  страница _PAGE_ с _PAGES_",
                "infoEmpty": "Нет записей",
                "infoFiltered": "(filtered from _MAX_ total records)",
                "search": "Живой фильтр:",
                "paginate": {
                    "first": "Первая",
                    "last": "Последняя",
                    "next": "Следующая",
                    "previous": "Предыдущая"
                },
            },
            "order": [[1, 'desc']],
            "ordering": false,
            "searching": false,
            "scrollX": true,
            "bLengthChange": false,
            "lengthMenu": [[16, 30, 100, -1], [16, 30, 100, "Все"]]
        });
    });
    $(document).ready(function () {
        $('#table_questions').DataTable({
            "language": {
                "lengthMenu": "Отображено _MENU_ записей на странице",
                "zeroRecords": "К сожалению, записей не найдено",
                "info": "Показана  страница _PAGE_ с _PAGES_",
                "infoEmpty": "Нет записей",
                "infoFiltered": "(filtered from _MAX_ total records)",
                "search": "Живой фильтр:",
                "paginate": {
                    "first": "Первая",
                    "last": "Последняя",
                    "next": "Следующая",
                    "previous": "Предыдущая"
                },
            },
            "order": [[0, 'desc']],
            "searching": false,
            "ordering": false,
            "scrollX": true,
            "bLengthChange": false,
            "lengthMenu": [[10, 30, 100, -1], [10, 30, 100, "Все"]]
        });
    });
</script>


<script>


</script>
<?= tpl('footer') ?>
