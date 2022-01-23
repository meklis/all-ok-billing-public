<?php
$rank = 5;


$paid_day = array(-30, -29, -28, -27, -26, -25, -24, -23, -22, -21, -20, -19, -18, -17, -16, -15, -14, -13, -12, -11, -10, -9, -8, -7, -6, -5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
$actions = [
    'no_action' => 'Не выбрано',
    'sms_noty' => 'Уведомить через СМС',
    'create_question' => 'Создать заявку',
];

require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


if (!envPHP\service\PSC::isPermitted('customer_deptors')) {
    pageNotPermittedAction();
}

function generSMS($a)
{
    $message = "Шановний абонент, на Вашому рахунку " . $a['agreement'] . "  " . $a['balance'] . "грн. Для подальшого користування необхідно поповнити рахунок";
    return rus2lat($message);
}

$form = [
    'search_action' => '',
    'paid_to' => 0,
    'group_id' => [],
    'price_id' => [],
    'marked' => [],
    'action_type' => '',
    'question_type' => -1,
    'question_comment' => '',
    'table' => '',
    'show_disabled' => 0,
    'page_no' => '',
    'checked' => [],
    'action_btn' => '',
    'agreements' => '',
];

$ht = [
    'sel_paid_to' => '',
    'groups' => '',
    'prices' => '',
    'table' => '',
    'actions' => '',
];

\envPHP\classes\std::Request($form);

if ($form['action_btn']) {
    $form['search_action'] = 'search';
}

$data = $sql->query("SELECT id, name FROM addr_groups WHERE id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).") order by name");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['group_id']) ? "SELECTED " : "";
    $ht['groups'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

$data = $sql->query("SELECT id, CONCAT(name, ' (', price_day, ')') name FROM bill_prices WHERE `show` = 1 ORDER BY name ");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['price_id']) ? "SELECTED " : "";
    $ht['prices'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

foreach ($paid_day as $v) {
    if ($form['paid_to'] == $v) $sel = "SELECTED"; else $sel = '';
    $ht['sel_paid_to'] .= "<OPTION value='$v' $sel>$v</OPTION>";
}
$ht['sel_paid_to'] .= "<OPTION value='255'>16 и больше</OPTION>";

foreach ($actions as $k => $v) {
    if ($k == $form['action_type']) $sel = "SELECTED"; else $sel = '';
    $ht['actions'] .= "<OPTION value='$k' $sel>$v</OPTION>";

}

//Отправка уведомлений
if ($form['search_action'] && $form['action_btn'] && $form['action_type'] !== 'no_action') {
    if ($form['checked']) {
        $keys = array_keys($form['checked']);
        $data = dbConnPDO()->query("SELECT 
            c.id,
            c.agreement,
            c.name,
            c.balance,
            c.provider,
            c.descr,
            cp.`value` phone,
            ce.`value` email 
            FROM clients c 
            LEFT JOIN client_contacts cp on cp.agreement_id = c.id and cp.type = 'PHONE' and cp.main = 1 
            LEFT JOIN client_contacts ce on ce.main = 1 and ce.type = 'EMAIL' and ce.agreement_id = c.id
            JOIN (SELECT h.id from addr_houses h JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")) houses on houses.id = c.house
            WHERE c.id in (" . join(',', $keys) . ")");
        $count = $data->rowCount();
        if ($form['action_type'] == 'sms_noty') {
            foreach ($data->fetchAll() as $e) {
                envPHP\service\shedule::add(envPHP\service\shedule::SOURCE_NOTIFICATION_GENERATOR, 'notification/sendSMS', ['phone' => $e['phone'], 'message' => generSMS($e)]);
            }
            html()->addNoty('info', "Отправлено СМС: <b>$count</b>");
        }
        if ($form['action_type'] == 'create_question') {
            $form['agreements'] = join(',', $keys);
        }
        $form['checked'] = [];
    } else {
        html()->addNoty('warning', "Укажите хотя бы одного абонента");
    }
}


if ($form['search_action']) {
    $having = '';
    $where = "";
    if ($form['group_id']) {
        $where .= " and h.group_id in (" . join(',', $form['group_id']) . ") ";
    }
    if ($form['price_id']) {
        $elems = join(",", $form['price_id']);
        $where .= " and p.id in ($elems)";
    }


    $having = " day <= " . $form['paid_to'];
    if ($form['paid_to'] == 255) $having = " day >= 16 ";
    if ($form['show_disabled']) $having .= " OR day is null";


    $data = $sql->query("
       SELECT 
c.id
, c.agreement
, c.`name`
, c.apartment
, c.balance
, ph.phone         
, em.email            
, round(c.balance / sum(p.price_month / 30)) day 
, CURDATE() + INTERVAL round(c.balance / sum(p.price_month / 30)) DAY paid_to
, round(sum(p.price_month / 30),2) price
, CONCAT('г.',city.name,', ', s.name, ', д.', h.name, ', кв.', apartment) addr
, (SELECT max(time) FROM paymants WHERE agreement = c.id) last_pay
FROM clients c 
JOIN addr_houses h on h.id = c.house
JOIN addr_streets s on s.id = h.street
JOIN addr_cities city on city.id = s.city
JOIN (SELECT h.id from addr_houses h JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")) houses on houses.id = c.house

LEFT JOIN client_prices pr on pr.agreement = c.id
LEFT JOIN bill_prices p on p.id = pr.price
LEFT JOIN (SELECT agreement_id, `value` phone FROM client_contacts WHERE main = 1 and type = 'PHONE') ph on ph.agreement_id = c.id 
LEFT JOIN (SELECT agreement_id, `value` email FROM client_contacts WHERE main = 1 and type = 'EMAIL') em on em.agreement_id = c.id 
WHERE (pr.time_stop is null)  $where 
GROUP BY c.id
HAVING $having
order by day 
");
    if ($data->num_rows == 0) $ht['table'] = "<h4 align='center'>По запросу ничего не найдено</h4>"; else {
        $ht['table'] = "<table class='table table-striped' id='myT' style='min-width: 600px;'>
                    <thead>
                        <tr>
                            <th>Договор</th>
                            <th>Имя</th>
                            <th>Адрес</th>
                            <th>Телефон</th>
                            <th>Почта</th>
                            <th>Оплачено до</th>
                            <th>Дней до оплаты</th>
                            <th>Посл. платеж</th>
                            <th>Баланс</th>
                            <th>Кредитует в день</th>
                            <th><input id=\"ch\" type=\"checkbox\" name=\"one\" value=\"all\" onclick=\"checkAll('form')\"></th>
                        </tr>
                    </thead>
                <tbody>";
        while ($d = $data->fetch_assoc()) {
            $href = "<a href = 'detail?id=" . $d['id'] . "'>" . $d['agreement'] . "</a>";
            if (isset($form['checked'][$d['id']])) $sel = 'CHECKED'; else $sel = '';
            $send = "<INPUT class='checkboxes' type='checkbox' name='checked[" . $d['id'] . "]' $sel>";
            $ht['table'] .= "<tr>
                <td>$href</td>
                <td>" . $d['name'] . "</td>
                <td>" . $d['addr'] . "</td>
                <td>" . $d['phone'] . "</td>
                <td>" . $d['email'] . "</td>
                <td>" . $d['paid_to'] . "</td>
                <td>" . $d['day'] . "</td>
                <td>" . $d['last_pay'] . "</td>
                <td>" . $d['balance'] . "</td>
                <td>" . $d['price'] . "</td>
                <td>$send</td>
                </tr>";
        }
        $ht['table'] .= "</tbody></table>";
    }
}
?>
<?= tpl('head', ['title' => '']) ?>
    <!-- OPEN WINDOW -->
<?php if ($form['agreements']) { ?>
    <form id="questionForm" method="post" action="/abonents/new_mas_questions" >
        <input type="hidden" name="agreements" value="<?= $form['agreements'] ?>"/>
    </form>
    <script type="text/javascript">
        $(document).ready(function () {
            document.getElementById('questionForm').submit();
        })
    </script>
<?php } ?>

    <form method="POST" name="form">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Работа с должниками</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="form-horizontal form-label-left input_mask row">
                            <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                                <label class="control-label">Дней до оплаты</label>
                                <SELECT name="paid_to" class="form-control">
                                    <?= $ht['sel_paid_to'] ?>
                                </SELECT>
                            </div>
                            <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-3 form-group">
                                <label class="control-label">Прайс</label>
                                <select name='price_id[]' multiple="multiple" id="price_id"
                                        class="form-control"><?= $ht['prices'] ?></select>
                            </div>
                            <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-3 form-group">
                                <label class="control-label">Группы</label>
                                <select name='group_id[]' multiple="multiple" id="group_id"
                                        class="form-control btn-block"><?= $ht['groups'] ?></select>
                            </div>
                            <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-3 form-group">
                                <label class="control-label">Показать отключенных</label>
                                <input type="checkbox" name="show_disabled" class="form-control" <?= $form['show_disabled'] ? 'checked' : '' ?>>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12 col-sm-6 col-md-6  col-lg-1 ">
                                    <label class="control-label">&nbsp;</label>
                                    <button type="submit" name="search_action" value="search"
                                            class="btn btn-block btn-primary">Поиск
                                    </button>
                                </div>
                            </div>
                            <?php if ($form['search_action']) { ?>
                            <div class=" col-xs-12  col-sm-12  col-md-12  col-lg-12 form-group">
                                <div class="divider-dashed"></div>
                            </div>
                        </div>
                        <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-3 form-group">
                            <label class='control-label'>С отмеченными</label>
                            <select name='action_type' class='form-control'><?= $ht['actions'] ?></select>
                        </div>
                        <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-3 form-group">
                            <label class='control-label'>&nbsp;</label>
                            <button type='submit' name='action_btn' value='go' class='btn btn-primary btn-block'>
                                Выполнить
                            </button>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-sm-12 col-lg-12 col-md-12 col-xs-12">
                <?= $ht['table'] ?>
            </div>
        </div>
    </form>
    </div>
    <script>
        $(document).ready(function () {
            $('#group_id').multiselect({
                includeSelectAllOption: true,
                maxHeight: 300,
                enableFiltering: true,
            });
            $('#price_id').multiselect({
                includeSelectAllOption: true,
                maxHeight: 300,
                enableFiltering: true,
            });
        })

        function checkAll(formname) {
            let checkboxes = document[formname].getElementsByClassName('checkboxes');
            let statusCheckbox = ($('#ch').is(":checked"));
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type === 'checkbox') {
                    checkboxes[i].checked = statusCheckbox;
                }
            }
        }

        window.onload = function () {
            setTimeout(function () {
                $('#message_success').fadeOut()
            }, 3000);
            setTimeout(function () {
                $('#message_fail').fadeOut()
            }, 3000);
        };
        $(document).ready(function () {
            $('#myT').DataTable({
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
                "order": [[3, 'asc']],
                "scrollX": true,

                "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "Все"]]
            });
        });
    </script>
<?= tpl('footer') ?>