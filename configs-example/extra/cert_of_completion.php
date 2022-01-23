<?php
/**
 * Работа с актом выполненных работ.
 * Позволяет генерировать акт выполненных работ в PDF, а так же подписывать и скачивать.
 * Абоненту доступна загрузка PDF
 */

return [
    'enabled' => true,
    'path' =>   '/www/files/question_certs',
    'subscribed_path' =>  '/www/files/question_certs_subscribed',
    'template_path' => '/www/files/templates',
    'temporary_path' => '/tmp/php/pdf',
];
