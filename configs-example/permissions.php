<?php

return [
    'enabled' => true,
    'access_list' =>
        [
            [
                'name' => 'Абоненты',
                'key' => 'customer',
                'rules' => [
                    ['key' => 'customer_create', 'name' => 'Создание договора', 'display' => true],
                    ['key' => 'customer_mass_messages', 'name' => 'Массовая отправка СМС', 'display' => true],
                    ['key' => 'customer_report_certs', 'name' => 'Печать квитанций', 'display' => true],
                    ['key' => 'customer_deptors', 'name' => 'Работа с должниками', 'display' => true],
                    ['key' => 'customer_search', 'name' => 'Поиск абоентов', 'display' => true],
                    ['key' => 'customer_show_card', 'name' => 'Просмотр карточки абонента ', 'display' => true],
                    ['key' => 'customer_change_name', 'name' => 'Изменение имени', 'display' => true],
                    ['key' => 'customer_change_provider', 'name' => 'Изменение провайдера', 'display' => true],
                    ['key' => 'customer_change_addr', 'name' => 'Изменение адреса ', 'display' => true],
                    ['key' => 'customer_stop_service', 'name' => 'Отключение услуги ', 'display' => true],
                    ['key' => 'customer_start_service', 'name' => 'Включение услуги', 'display' => true],
                    ['key' => 'customer_pause_service', 'name' => 'Приостановка услуги', 'display' => true],
                    ['key' => 'customer_resume_service', 'name' => 'Возобновление услуги', 'display' => true],
                    ['key' => 'customer_change_description', 'name' => 'Изменение описания', 'display' => true],
                    ['key' => 'customer_disable_agreement', 'name' => 'Отключение договора ', 'display' => true],
                    ['key' => 'customer_change_notification', 'name' => 'Управление уведомлениями', 'display' => true],
                    ['key' => 'customer_change_ack', 'name' => 'Управление АСК ', 'display' => true],
                    ['key' => 'customer_change_contacts', 'name' => 'Управление контактами', 'display' => true],
                    ['key' => 'customer_change_password', 'name' => 'Изменение пароля', 'display' => true],
                    ['key' => 'customer_related', 'name' => 'Отображать смежные договора', 'display' => true],
                    ['key' => 'customer_purpose_of_payment', 'name' => 'Печать квитанций', 'display' => true],
                ],
            ],
            [
                'name' => 'Заявки',
                'key' => 'questions',
                'rules' => [
                    ['key' => 'question_create', 'name' => 'Создание заявки', 'display' => true],
                    ['key' => 'question_search', 'name' => 'Поиск заявок', 'display' => true],
                    ['key' => 'question_change', 'name' => 'Изменение заявки', 'display' => true],
                    ['key' => 'question_show', 'name' => 'Просмотр заявки', 'display' => true],
                    ['key' => 'question_report_change', 'name' => 'Внесение отчета по заявке ', 'display' => true],
                    ['key' => 'question_report_show', 'name' => 'Просмотр отчета по заявке', 'display' => true],
                ],
            ],
            [
                'name' => 'Платежи',
                'key' => 'payments',
                'rules' => [
                    ['key' => 'payment_search', 'name' => 'Поиск/просмотр платежей', 'display' => true],
                    ['key' => 'payment_delete', 'name' => 'Удаление платежа', 'display' => true],
                    ['key' => 'payment_show', 'name' => 'Отображать платежи в карточке абонента', 'display' => true],
                    ['key' => 'payment_create', 'name' => 'Внесение платежа ', 'display' => true],
                    ['key' => 'payment_source', 'name' => 'Просмотр источников платежей ', 'display' => true],
                    ['key' => 'payment_summary_source', 'name' => 'Просмотр сводных по платежам ', 'display' => true],
                    ['key' => 'payment_summary_price', 'name' => 'Просмотр сводных по прайсам', 'display' => true],
                    ['key' => 'payment_liqpay', 'name' => 'Ручная проверка LiqPay ', 'display' => true],
                ],
            ],
            [
                'name' => 'Привязки оборудования ',
                'key' => 'eq_bindings',
                'rules' => [
                    ['key' => 'eq_binding_search', 'name' => 'Поиск привязок ', 'display' => true],
                    ['key' => 'eq_binding_delete', 'name' => 'Удаление привязки', 'display' => true],
                    ['key' => 'eq_binding_change_mac', 'name' => 'Изменение MACа привязки', 'display' => true],
                    ['key' => 'eq_binding_change_ip', 'name' => 'Изменение IP привязки', 'display' => true],
                    ['key' => 'eq_binding_change_static', 'name' => 'Установка статического IP ', 'display' => true],
                    ['key' => 'eq_binding_change_port', 'name' => 'Изменение свитча/порта привязки ', 'display' => true],
                    ['key' => 'eq_binding_create', 'name' => 'Внесение привязки ', 'display' => true],
                ],
            ],
            [
                'name' => 'Оборудование',
                'key' => 'equipments',
                'rules' => [
                    ['key' => 'eq_models', 'name' => 'Просмотр/изменение моделей ', 'display' => true],
                    ['key' => 'eq_access', 'name' => 'Просмотр/изменение доступов ', 'display' => true],
                    ['key' => 'eq_group', 'name' => 'Просмотр/изменение групп ', 'display' => true],
                    ['key' => 'eq_pinger', 'name' => 'Пингер/свитчер', 'display' => true],
                    ['key' => 'eq_show', 'name' => 'Просмотр железки', 'display' => true],
                    ['key' => 'eq_list', 'name' => 'Список железки', 'display' => true],
                    ['key' => 'eq_edit', 'name' => 'Изменение железки', 'display' => true],
                    ['key' => 'eq_create', 'name' => 'Внесение железок', 'display' => true],
                    ['key' => 'eq_delete', 'name' => 'Удаление железки ', 'display' => true],
                    ['key' => 'eq_change_vlan', 'name' => 'Изменение влана на железке', 'display' => true],
                ],
            ],
            [
                'name' => 'Сеть ',
                'key' => 'network_vlan',
                'rules' => [
                    ['key' => 'vlan_show', 'name' => 'Просмотр вланов', 'display' => true],
                    ['key' => 'vlan_change', 'name' => 'Изменение вланов ', 'display' => true],
                    ['key' => 'network_show', 'name' => 'Просмотр подсети', 'display' => true],
                    ['key' => 'network_edit', 'name' => 'Изменение подсети ', 'display' => true],
                ],
            ],
            [
                'name' => 'Персонал',
                'key' => 'employees',
                'rules' => [
                    ['key' => 'employees_show', 'name' => 'Просмотр персонала', 'display' => true],
                    ['key' => 'employees_group', 'name' => 'Просмотр/изменение групп ', 'display' => true],
                    ['key' => 'employees_add', 'name' => 'Внесение/изменение пользователя', 'display' => true],
                    ['key' => 'employees_notification', 'name' => 'Управление уведомлениями', 'display' => true],
                    ['key' => 'employees_reaction_stat', 'name' => 'Статистика по реакции', 'display' => true],
                    ['key' => 'employees_schedule_show', 'name' => 'Просмотр графиков дежурств', 'display' => true],
                    ['key' => 'employees_schedule_edit', 'name' => 'Изменения графиков дежурств', 'display' => true],
                    ['key' => 'question_loading', 'name' => 'Загрузка по территориям', 'display' => true],
                ],
            ],
            [
                'name' => 'Система',
                'key' => 'sys',
                'rules' => [
                    ['key' => 'sys_question_reason', 'name' => 'Управление причинами заявок', 'display' => true],
                ],
            ],
            [
                'name' => 'Тринити',
                'key' => 'trinity',
                'rules' => [
                    ['key' => 'trinity_binding_add', 'name' => 'Внесение привязки ', 'display' => true],
                    ['key' => 'trinity_contracts', 'name' => 'Просмотр списка контрактов', 'display' => true],
                    ['key' => 'trinity_search', 'name' => 'Поиск привязок ', 'display' => true],
                    ['key' => 'trinity_delete', 'name' => 'Удаление привязки', 'display' => true],
                ]
            ],

            [
                'name' => 'OMO systems',
                'key' => 'omo',
                'rules' => [
                    ['key' => 'omo_display', 'name' => 'Отображать ОМО', 'display' => true],
                    ['key' => 'omo_control', 'name' => 'Управление ОМО', 'display' => true],
                ]
            ],

        ]
];