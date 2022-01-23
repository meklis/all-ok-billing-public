<?php
$rank = 5;
ini_set('pcre.backtrack_limit', 500000000);
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();
if (isset($_COOKIE['last_page'])) $page = $_COOKIE['last_page']; else $page = '';

if (!\envPHP\service\PSC::isPermitted('employees_reaction_stat')) {
    pageNotPermittedAction();
}

$form = [
    'groups' => [],
    'types' => [],
    'employees' => [],
    't' => '',
    'v' => '',
    'start' => date("d.m.Y", time()),
    'stop' => date("d.m.Y", time()),
];
$ht = [
    'table' => '',
    'start' => date("d.m.Y", time()),
    'stop' => date("d.m.Y", time()),
    'description' => '',
];


envPHP\classes\std::Request($form);

$WHERE = "WHERE (cast(f.dest_time as date) >= STR_TO_DATE('{$form['start']}','%d.%m.%Y') and  cast(IFNULL(f.dest_time,NOW()) as date) <= STR_TO_DATE('{$form['stop']}','%d.%m.%Y') )";
if ($form['types']) {
    $WHERE .= " and f.reason_id in (" . join(',', $form['types']) . ")";
}
if ($form['employees']) {
    $WHERE .= " and e.id in (" . join(',', $form['employees']) . ")";
}

if ($form['t']) {
    if ($form['t'] == 'type') $WHERE .= ' and f.reason_id = ' . $form['v'];
    if ($form['t'] == 'employee') $WHERE .= ' and e.id = ' . $form['v'];
}
$type = '';
$employeee = '';
$byEmployees = dbConnPDO()->query("
        SELECT f.id,
        f.reason,
        f.dest_time,
        f.report_time,
        ROUND(TIME_TO_SEC(timediff(f.report_time,f.dest_time))/60) real_reaction_time,
        rt.reaction_time reglament_time,
        if(ROUND(TIME_TO_SEC(timediff(f.report_time,f.dest_time))/60)  - rt.reaction_time > 0, ROUND(TIME_TO_SEC(timediff(f.report_time,f.dest_time))/60)  - rt.reaction_time, null) reached_reaction,
        f.report_status,
        e.`name` employee_name,
        e.id employee_id, 
        f.`report_comment`
        FROM questions_full f 
        JOIN clients c on c.id = f.agreement 
        JOIN v_reaction_times as rt on rt.reason_id = f.reason_id and rt.house_id = c.house
        JOIN employees e on e.id = f.reported_employee
        $WHERE
        ORDER BY 1 desc 
        ")->fetchAll();
if(count($byEmployees)) {
    $ht['table'] .= "
<table id='tbl_ex' class='table table-bordered ' >
    <thead>
            <tr>
                <td>Id</td>    
                <td>Причина</td>    
                <td>Назначено на</td>    
                <td>Закрыть до</td>    
                <td>Закрыто в</td>    
                <td>Статус</td>       
                <td>Закрыл</td>    
                <td>Коментарий к заявке</td>    
            </tr>
    </thead>
    <tbody>
    ";
    foreach ($byEmployees as $e) {
        $type = $e['reason'];
        $employee = $e['employee_name'];
        $reportStatus = 'Без отчета';
        switch ($e['report_status']) {
            case 'CANCEL': $reportStatus = 'Отменена'; break;
            case 'DONE': $reportStatus = 'Выполнена'; break;
            case 'IN_PROCESS': $reportStatus = 'В процессе'; break;
        }
        $reactionTime = (new DateTime($e['dest_time']))->add(new DateInterval("PT{$e['reglament_time']}M"))->format("Y-m-d H:i:s");
        $closedAt = $e['report_time'];
        $trStyle = '';
        if($e['reached_reaction']) {
            $closedAt .= "<br>+{$e['reached_reaction']} мин.";
        }
        if($e['reached_reaction']) {
            $trStyle = 'background: #ffb3b3; font-weight: bold';
        }
       $ht['table'] .= "
            <tr style='$trStyle'>
                <td ><a href='/abonents/question_response?id={$e['id']}'>{$e['id']}</a></td>
                <td style='$trStyle'>{$e['reason']}</td>
                <td style='font-weight: bold; $trStyle'>{$e['dest_time']}</td>
                <td style='$trStyle'>{$reactionTime}</td>
                <td  style='font-weight: bold; $trStyle'>{$closedAt}</td>
                <td style='$trStyle'>{$reportStatus}</td>
                <td style='$trStyle'>{$e['employee_name']}</td>
                <td style='$trStyle'>{$e['report_comment']}</td>
            
</tr>
       ";
    }
    $ht['table'] .= "
        </tbody>
        </table>
    ";
} else {
  $ht['table'] = '
    <h3 align="center">Данных не найдено, похоже форма неккоректна</h3>
  ';
}

if($form['t'] == 'type') {
    $ht['description'] .= "
    <div style='font-size: 110%; color: #1d3e81'>Детально по типу: <b>{$type}</b></div>   
";
} else {
    $ht['description'] .= "
    <div style='font-size: 110%; color: #1d3e81'>Детально по сотруднику: <b>{$employee}</b></div>   
";
}
$ht['description'] .= "
    <div style=''>Даты:  <b>с {$form['start']} по {$form['stop']}</b></div>
";
$ht['description'] .= "
    <div style=''>Количество записей: <b> ".count($byEmployees)." </b></div>    
";
?><?= tpl('head', ['title' => ""]) ?>
<div class="row">
    <div class="col-xs-12 col-md-3 col-lg-2 col-sm-3"><a href="/users/reaction_stat?<?= url_array_encode($form)?>" class="btn btn-primary btn-block">Назад к сводной</a></div>
    <div class="col-xs-12 col-md-6 col-lg-4 col-sm-6"><?=$ht['description']?></div>
</div>
<div class="row" style="margin-top: 20px">
    <div class="col-sm-12">
        <?= $ht['table'] ?>
    </div>
</div>
<script>
    $(function () {
        $('#start').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?=$ht['start']?>'});
        $('#stop').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?=$ht['stop']?>'});
    });
    $('#start').change(function () {
        $('#period').val($('#start').val() + ' - ' + $('#stop').val());
    });

    $('#stop').change(function () {
        $('#period').val($('#start').val() + ' - ' + $('#stop').val());
    });
    $(document).ready(function () {
        $('#tbl_ex').DataTable({
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
            "order": [[6, 'desc']],

            "searching": true,
            "ordering": true,
            "scrollX": true,
            "bLengthChange": true,
            "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "Все"]]
        });

    });
</script>
<?= tpl('footer') ?>
