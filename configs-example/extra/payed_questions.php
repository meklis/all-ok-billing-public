<?php
$PAYED_QUESTIONS = [
    'enabled' => true,
    'check_question' => [
        'create' => true,
        'reason_id' => 10,
        'comment' => 'Проверить оплату по платной заявке',
        //Дата создания заявки указывается в SQL, переменная подставляется БЕЗ экранирования
        'date' => 'CAST(NOW() + INTERVAL 3 DAY as DATE) + INTERVAL 9 HOUR'
    ],
    'enable_credit' => true,
    'question_reason_to_pay_type' => [
        '2' => 'подключение',
        '~' => 'обслуживание',
    ],
];
return $PAYED_QUESTIONS;