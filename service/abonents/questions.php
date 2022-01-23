<?php
$rank = 5;
$urank = 8;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(!\envPHP\service\PSC::isPermitted('question_search')) {
    pageNotPermittedAction();
}


$form = [
    'agreement' => '',
    'date1' => date('d.m.Y'),
    'date2' => date('d.m.Y'),
    'type_date' => 1,
    'city' => 0,
    'street' => 0,
    'house' => 0,
    'change_status' => '',
    'reason' => -1,
    'quest_print' => [],
    'action' => '',
    'responsible' => -1,
];


$statuses = [
    '' => "Нет отчета",
    'IN_PROCESS' => 'В процессе',
    'DONE' => 'Выполнена',
    'CANCEL' => 'Отменена',
];

if (isset($_REQUEST)) foreach ($_REQUEST as $k => $v) $form[$k] = $v;

if ($form['responsible'] == 'me') {
    $form['responsible'] = _uid;
}
$ht = [
    'reasons' => '',
    'responsibles' => '',
];

$ht['reasons'] .= "<OPTION value='-1' >Все</OPTION>";
$reasons = $sql->query("SELECT * FROM question_reason WHERE display = 'YES' ORDER  by name");
while ($reason = $reasons->fetch_assoc()) {
    $sel = $form['reason'] == $reason['name'] ? "SELECTED" : "";
    $ht['reasons'] .= "<OPTION value='{$reason['name']}' $sel >{$reason['name']}</OPTION>";
}
$ht['responsibles'] .= "<OPTION value='-1' >Все</OPTION>";
$data = $sql->query("SELECT e.id, e.name, p.position FROM employees e JOIN emplo_positions p on p.id = e.position 
WHERE display = 1 ORDER BY 2,3 ");
while ($d = $data->fetch_assoc()) {
    $sel = $form['responsible'] == $d['id'] ? "SELECTED" : "";
    $ht['responsibles'] .= "<OPTION value='{$d['id']}' $sel >{$d['name']} - {$d['position']}</OPTION>";
}

$message = '';
$table = '';

$d_types = array(1 => 'На когда', 2 => 'Создано');
$html->getHouses($form['city'], $form['street'], $form['house'], $sql);

//Выборка по типу даты 
$htype = "<SELECT name='type_date' class='form-control'>";
foreach ($d_types as $k => $v) {
    if ($form['type_date'] == $k) $sel = 'SELECTED'; else $sel = '';
    $htype .= "<OPTION value='$k' $sel>$v</OPTION>";
}
$htype .= "</SELECT>";


if ($form['action']) {
    $sqlStr = '';
    if ($form['house'] != 0) {
        $sqlStr = " and s.house  = '" . $form['house'] . "'";
    };
    if ($form['type_date'] == 1)
        $sqlStr .= " and cast(q.dest_time as date) BETWEEN STR_TO_DATE('" . $form['date1'] . "','%d.%m.%Y') and STR_TO_DATE('" . $form['date2'] . "','%d.%m.%Y') ";
    else
        $sqlStr .= " and cast(q.created as date) BETWEEN STR_TO_DATE('" . $form['date1'] . "','%d.%m.%Y') and STR_TO_DATE('" . $form['date2'] . "','%d.%m.%Y') ";
    if (trim($form['agreement']) != '') {
        $sqlStr = " and s.agreement = '" . trim($form['agreement']) . "'";
    }
    if ($form['reason'] != -1) {
        $sqlStr .= " and q.reason = '{$form['reason']}' ";
    }
    if ($form['responsible'] != -1) {
        $sqlStr .= " and q.responsible_employee = '{$form['responsible']}' ";
    }
    $data = $sql->query("SELECT 
q.id
,q.created
,e.`name` created_employee
,s.agreement
,q.`comment`
,s.id aid
,q.phone
,q.reason
,CONCAT('г.',c.name,', ', st.name, ', д.', h.`name`, ', под.',s.entrance, ', кв.', s.apartment) addr
,q.dest_time
,q.report_status status 
,q.report_comment
, re.name responsible_employee
,e2.name reported_employee 
, q.amount
FROM questions_full q 
JOIN clients s on q.agreement = s.id
JOIN addr_houses h on h.id = s.house and h.group_id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")
JOIN addr_streets st on st.id = h.street
JOIN addr_cities c on c.id = st.city
LEFT JOIN employees e on e.id = q.created_employee
LEFT JOIN employees e2 on e2.id = q.reported_employee
LEFT JOIN employees re on re.id = q.responsible_employee
WHERE 1=1 $sqlStr");
    $printable = " 
Список заявок - {$form['date1']} - {$form['date2']}<br>
";
    if ($data->num_rows != 0) {
        $table = "<table class='table  table-striped ' id='myT'><thead><tr>
                <th>ID</th>
                <th>Создана</th>
                <th>Создатель</th>
                <th style='width: 5%'>Причина</th>
                <th style='width: 3%'>Номер договора</th>
                <th style='width: 15%'>Адрес</th>
                <th>Номер телефона</th>
                <th style='width: 30%'>Коментарий</th>
                <th>На когда</th>
                <th>Ответственный</th>
                <th  style='width: 10%'>Статус</th>
                <th  style='width: 10%'>Сумма</th>
                <th>Отчет</th>
                <th><input type='checkbox' name='checkedss' onclick='checkAll(\"form\")'></th></tr></thead><tbody>";
        $printable .= "
        <table style='border-collapse: collapse' border='1'><tr>
                <th style='color: white; background: black; padding: 2px;'>ID</th>
                <th style='color: white; background: black; padding: 2px;'>Создана</th>
                <th style='color: white; background: black; padding: 2px;'>Создатель</th>
                <th style='color: white; background: black; padding: 2px;'>Причина</th>
                <th style='color: white; background: black; padding: 2px;'>Номер договора</th>
                <th style='color: white; background: black; padding: 2px;'>Адрес</th>
                <th style='color: white; background: black; padding: 2px;'>Номер телефона</th>
                <th style='color: white; background: black; padding: 2px;'>Коментарий</th>
                <th style='color: white; background: black; padding: 2px;'>На когда</th>
                <th style='color: white; background: black; padding: 2px;'>Ответственный</th>
                <th style='color: white; background: black; padding: 2px;'>Статус</th>  
                <th style='color: white; background: black; padding: 2px;'>Сумма</th>  
                </tr>
                
        ";
        while ($d = $data->fetch_assoc()) {
            switch ($d['status']) {
                case 'IN_PROCESS':
                    $color = "#ADBDFA";
                    $report = "<b>В процессе</b><br><small>{$d['reported_employee']}: {$d['report_comment']}</small>";
                    break;
                case 'DONE':
                    $color = "#AEECA0";
                    $report = "<b>Выполнена</b><br><small>{$d['reported_employee']}: {$d['report_comment']}</small>";
                    break;
                case 'CANCEL':
                    $color = "#FAADAD";
                    $report = "<b>Отменена</b><br><small>{$d['reported_employee']}: {$d['report_comment']}</small>";
                    break;
                case '':
                    $color = "#F9FAAD";
                    $report = "Без отчета";
                    break;
                default:
                    $color = "";
                    $report = "Неизвестно";
            }
            $amount = $d['amount'] ? $d['amount']  : "0.00";
                $agree = "<a style='font-weight: bold' href='/abonents/detail?id=" . $d['aid'] . "' target='_blank'>" . $d['agreement'] . "</a>";
            $table .= "<tr>
                        <td style='background: {$color}' class='t'>{$d['id']}</td>
                        <td style='background: {$color}' class='t'>{$d['created']}</td>
                        <td style='background: {$color}' class='t'>{$d['created_employee']}</td>
                        <td style='background: {$color}' class='t'>{$d['reason']}</td>
                        <td style='background: {$color}' class='t'>{$agree}</td>
                        <td style='background: {$color}' class='t'>{$d['addr']}</td>
                        <td style='background: {$color}' class='t'>{$d['phone']}</td>
                        <td style='background: {$color}' class='t'>{$d['comment']}</td>
                        <td style='background: {$color}' class='t'>{$d['dest_time']}</td>
                        <td style='background: {$color}' class='t'>{$d['responsible_employee']}</td>
                        <td style='background: {$color}' class='t'>{$report}</td>
                        <td style='background: {$color}' class='t'>{$amount}</td>
                        <td style='background: {$color}' class='t'><a  href='question_response?id={$d['id']}'>Внести отчет</a><br><a href='new_questions?id={$d['id']}'>Изменить</a></td>
                        <td style='background: {$color}' class='t'><input type='checkbox' name='quest_print[]' value='{$d['id']}' " . (in_array($d['id'], $form['quest_print']) ? 'checked' : '') . "></td>
                        ";
            if ($form['action'] == 'print' && in_array($d['id'], $form['quest_print'])) {
                $printable .= "<tr>
                        <td style='padding: 2px; background: {$color}' class='t'>{$d['id']}</td>
                        <td style='padding: 2px; background: {$color}' class='t'>{$d['created']}</td>
                        <td style='padding: 2px; background: {$color}' class='t'>{$d['created_employee']}</td>
                        <td style='padding: 2px; background: {$color}' class='t'>{$d['reason']}</td>
                        <td style='padding: 2px; background: {$color}' class='t'><b>{$d['agreement']}</b></td>
                        <td style='padding: 2px; background: {$color}' class='t'>{$d['addr']}</td>
                        <td style='padding: 2px; background: {$color}' class='t'>{$d['phone']}</td>
                        <td style='padding: 2px; background: {$color}' class='t'>{$d['comment']}</td>
                        <td style='padding: 2px; background: {$color}' class='t'>{$d['dest_time']}</td>
                        <td style='padding: 2px; background: {$color}' class='t'>{$report}</td> 
                        <td style='padding: 2px; background: {$color}' class='t'>{$amount}</td> 
                        </tr>
                        ";
            }
        }
        $table .= "</tbody></table>";
        $printable .= "</table>";
    } else {
        $html->addNoty('info', "По указанным параметрам заявок не найдено");
        $table = "<br><br><h3 align='center'>Заявок не найдено</h3>";
        $printable .= "<br><br><h3 align='center'>Заявок не найдено</h3>";
    }
}
if ($form['action'] == 'print') {
    $mpdf = new \Mpdf\Mpdf([
        'tempDir' => '/tmp/php/pdf',
        'format' => [200, 297],
        'orientation' => 'L',
        'default_font_size' => 9,
        'margin_top' => 5,
        'margin_left' => 5,
        'margin_right' => 5,
        'margin_bottom' => 5,
    ]);
    $mpdf->WriteHTML($printable);
    $mpdf->Output('questions.pdf', 'I');
    exit;
}

?>
<?= tpl('head', ['title' => '']) ?>
    <div class="row">
        <form name="form" method="GET">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Поиск заявок</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="form-horizontal form-label-left input_mask row">
                            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                                <span style="font-size: 16px; font-weight:  500">Основное</span>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                                <label class="control-label">Номер договора</label>
                                <input name='agreement' value="<?= $form['agreement'] ?>" class="form-control">
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                                <label class="control-label">Причина</label>
                                <select name="reason" class="form-control"><?= $ht['reasons'] ?></select>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-3 col-lg-2 form-group">
                                <label class="control-label">Искать по дате</label>
                                <?= $htype ?>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-3 col-lg-2 form-group">
                                <label class="control-label">Дата С</label>
                                <?= $html->formDate('date1', $form['date1']) ?>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-3 col-lg-2 form-group">
                                <label class="control-label">Дата ПО</label>
                                <?= $html->formDate('date2', $form['date2']) ?>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-3 col-lg-2 form-group">
                                <label class="control-label">Ответственный</label>
                                <select name="responsible" class="form-control"><?= $ht['responsibles'] ?></select>
                            </div>
                            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                                <div class="divider-dashed"></div>
                                <span style="font-size: 16px; font-weight:  500">Адрес</span>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                                <label class="control-label">Город</label>
                                <?= $html->listCities ?>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                                <label class="control-label">Улица</label>
                                <?= $html->listStreets ?>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                                <label class="control-label">Дом</label>
                                <?= $html->listHouses ?>
                            </div>
                            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                                <span style="font-size: 16px; font-weight:  500"></span>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                                <label class="control-label">&nbsp; </label>
                                <button class="btn btn-primary btn-block" type="submit" name='action' value="search">
                                    Найти
                                </button>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                                <label class="control-label">&nbsp; </label>
                                <button class="btn btn-primary btn-block" type="submit" name='action' value="print">
                                    Печать
                                </button>
                            </div>
                            <input name='change_status' value='' type='hidden' hidden>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-lg-12 col-md-12 col-xs-12">
                <center><?= $message ?></center>
                <div class="table-responsive-light">
                    <?= $table ?>
                </div>
            </div>
        </form>
    </div>
    <script>
        var statusCheckbox = false;

        function checkAll(formname) {
            var checkboxes = new Array();
            checkboxes = document[formname].getElementsByTagName('input');
            statusCheckbox = !statusCheckbox;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
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

                "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "Все"]]
            });
        });
    </script>
<?= tpl('footer') ?>