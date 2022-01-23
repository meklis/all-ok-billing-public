#!/usr/bin/php
<?php
/**
 * Скрипт для запуска через крон
 * Запускать раз в сутки, например в 23:55
 * Пример - 55  23  *   *   *  /usr/bin/php /www/cgi/calc_payed_questions.php
 *
 */

require('/www/envPHP/load.php');
$config = getGlobalConfigVar('PAYED_QUESTIONS');

if(!$config['enabled']) {
    echo "Payed question is disabled\n";
    exit(1);
}

//Получение заявок за сегодня
$data = dbConn()->query("SELECT id, agreement, reason_id, reason, amount, report_time, report_status 
FROM questions_full 
WHERE report_time > NOW() - INTERVAL 24 HOUR
and amount != 0 ");

while ($d = $data->fetch_assoc()) {
    try {
        \envPHP\classes\std::msg("Найден договор #{$d['agreement']} с платной заявкой #{$d['id']} на сумму {$d['amount']}");
        $type = $config['question_reason_to_pay_type']['~'];
        foreach ($config['question_reason_to_pay_type'] as $reasonId => $value) {
            if ($reasonId == $d['reason_id']) {
                $type = $value;
                break;
            }
        }
        
        $amount = -$d['amount'];
        $payId = \envPHP\service\payment::add($d['agreement'], $amount, $type, $d['id'], "Оплата за {$type} согласно заявке №{$d['id']}");
        \envPHP\classes\std::msg("Добавлен платеж с суммой {$amount}. ID платежа - {$payId}");

        //Получение баланса абонента
        $user = dbConn()->query("SELECT balance, ph.phone
         FROM clients c 
         LEFT JOIN (SELECT agreement_id, `value` phone FROM client_contacts WHERE main = 1 and type = 'PHONE') ph on ph.agreement_id = c.id 
         WHERE id = '{$d['agreement']}'")->fetch_assoc();
        if ($user['balance'] <= 0) {
            //Создадим заявку на проверку оплаты
            if ($config['check_question']['create']) {
                $date = dbConn()->query("SELECT {$config['check_question']['date']} as date")->fetch_assoc()['date'];
                \envPHP\service\Question::create(
                    (new \envPHP\structs\Client())->fillById($d['agreement']),
                    $config['check_question']['reason_id'],
                    (new \envPHP\structs\Employee())->fillById( getGlobalConfigVar('BASE')['billing_user_id']),
                    $user['phone'],
                    $config['check_question']['comment'],
                    $date
                );
            }

            //Включаем кредитный период
            if ($config['enable_credit']) {
                \envPHP\service\creditPeriod::enableCredit($d['agreement'], getGlobalConfigVar('BASE')['billing_user_id']);
            }
        }
    } catch (Exception $e) {
        \envPHP\classes\std::msg("Error calculate agreement with ID {$d['agreement']}: ". $e->getMessage());
        \envPHP\classes\std::msg($e->getTraceAsString());
    }
}
