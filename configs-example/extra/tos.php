<?php
//Configuration for time management system
return [
    //Размер временного слота = 15мин
    'slot_time' => 15,
    //Типы заявок, которые берут участие в расчете временных слотов
    'question_reasons' => [
        2, 3, 4, 5, 6, 10, 11, 12
    ],
    //Время на которое можно создавать заявки
    'hours_create_question' => [
        'start' => 9,
        'end' => 19,
    ],
    //Рабочее время
    'hours_schedule' => [
        'start' => 9,
        'end' => 21,
    ],
    'display_add_days' => 14,
];