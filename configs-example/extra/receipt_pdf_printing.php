<?php
return [
    'message_of_payment' => [
        'path' => "/www/files/templates/message_of_payment/message_of_payment.template.html",
        'params' => [
            'Отримувач платежу' => ' ',
            'Поточний рахунок отримувача' => '',
            'Ідентифікаційний код отримувача' => '',
            'Установа банку' => '',
            'Код бюджетної класифікації' => '',
            'Адреса платника' => '',
            'Имя' => '',
        ]
    ],
    'receipt' => [
        'path' =>  "/www/files/templates/pdf_receipt/receipt.template.html",
        'params' => [
            'Отримувач' => '',
        ],
    ],
];