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
    'action' => '',
    'start' => date("d.m.Y", time()),
    'stop' => date("d.m.Y", time()),
];
$ht = [
    'groups' => '',
    'types' => '',
    'employees' => '',
    'group_by' => "",
    'tbl_by_employees' => '',
    'tbl_by_types' => '',
    'start' => date("d.m.Y", time()),
    'stop' => date("d.m.Y", time()),
];
$JSONS = [
        'pie_by_type' => [],
];


envPHP\classes\std::Request($form);


$data = $sql->query("SELECT id, name FROM addr_groups order by name");
$ht['groups'] .= "<OPTION value=''>Не указано</OPTION>";
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['groups']) ? "SELECTED " : "";
    $ht['groups'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

$data = $sql->query("SELECT name, id  FROM question_reason WHERE display = 'YES' order by 2 ");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['types']) ? "SELECTED " : "";
    $ht['types'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

$data = $sql->query("SELECT e.id, e.name, p.position position
FROM employees e 
JOIN emplo_positions p on p.id = e.position
WHERE e.display = 1 and p.show = 1 
ORDER BY 2,3");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['employees']) ? "SELECTED " : "";
    $ht['employees'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']} ({$d['position']})</OPTION>";
}


if ($form['action']) {
    $tableFields = [];
    $tableData = [];
    $query = "";
    $WHERE = "WHERE (cast(f.dest_time as date) >= STR_TO_DATE('{$form['start']}','%d.%m.%Y') and  cast(IFNULL(f.dest_time,NOW()) as date) <= STR_TO_DATE('{$form['stop']}','%d.%m.%Y') )";
    if ($form['types']) {
        $WHERE .= " and f.reason_id in (" . join(',', $form['types']) . ")";
    }
    if ($form['employees']) {
        $WHERE .= " and e.id in (" . join(',', $form['employees']) . ")";
    }
    $data = [];
    $tableFields = [
        'Имя',
        'Всего',
        'Закр/Отм/Процесс*',
        'Нагрузка(час)***',
        'Просроченных(кол)',
        'Просроченных(час)',
        'Просроченных(%)**',
        "<i class='fa fa-search'></i>"
    ];
    $byEmployees = dbConnPDO()->query("
            SELECT e.id employee_id, e.name, 
            count(*) count_questions,
            count(IF(f.report_status = 'DONE', 1, null)) count_done,
            count(IF(f.report_status = 'CANCEL', 1, null)) count_cancel,
            count(IF(f.report_status = 'IN_PROCESS' or f.report_status is null, 1, null)) count_in_progress,
            sum(rt.reaction_time) summary_question_load, 
            count(if(ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) > rt.reaction_time, 1, null)) count_reached_reason,
            sum(if(ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) > rt.reaction_time, ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) - rt.reaction_time , null)) summary_reached_reason,
            count(if(ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) <= rt.reaction_time, 1, null)) count_timein_reason,
            sum(if(ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) <= rt.reaction_time, ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) - rt.reaction_time , null)) summary_timein_reason
            FROM questions_full f 
            JOIN clients c on c.id = f.agreement 
            JOIN v_reaction_times as rt on rt.reason_id = f.reason_id and rt.house_id = c.house
            JOIN employees e on e.id = f.reported_employee
            $WHERE
            GROUP BY e.name, employee_id
            ORDER BY 1
            ")->fetchAll();
    if (count($byEmployees) == 0) {
        html()->addNoty('info', "По указанным параметрам ничего не найдено");
        $ht['tbl_by_employees'] = "<h4 align='center'>По запросу ничего не найдено</h4>";
    } else {
        $ht['tbl_by_employees'] .= "<table class='table table-bordered table-sm' id='tbl-by-employees'><thead><tr>";
        foreach ($tableFields as $tf) {
            $ht['tbl_by_employees'] .= "<th>{$tf}</th>";
        }
        $ht['tbl_by_employees'] .= "</tr></thead><tbody>";
        foreach ($byEmployees as $d) {
            $color = '#F0F0F0';
            $prc = 0;
            if ($d['count_cancel'] + $d['count_done'] !== 0) {
                $prc = round(($d['count_reached_reason'] / ($d['count_cancel'] + $d['count_done']) * 100), 2);
            }
            if ($prc > 80) {
                $color = "#ff3333";
            } elseif ($prc > 60) {
                $color = "#ff6666";
            } elseif ($prc > 40) {
                $color = "#ffb3b3";
            } elseif ($prc > 20) {
                $color = "#ffe6e6";
            }
            $ht['tbl_by_employees'] .= "<tr style='background: {$color}'>";
            foreach ([
                         [$d['name'], 'font-weight: bold',],
                         [$d['count_questions'], '',],
                         [$d['count_done'] . '/' . $d['count_cancel'] . '/' . $d['count_in_progress'], '',],
                         [round($d['summary_question_load'] / 60, 2), '',],
                         [$d['count_reached_reason'], 'font-weight: bold',],
                         [round($d['summary_reached_reason'] / 60, 2), '',],
                         [$prc . '%', 'font-weight: bold',],
                         ["<a href='/users/reaction_stat_detail?" . url_array_encode($form) . "&t=employee&v={$d['employee_id']}'><i class='fa fa-search'></i></a>", ''],
                     ] as $c) {
                $ht['tbl_by_employees'] .= "<td style='" . (isset($c[1]) ? $c[1] : '') . "'>{$c[0]}</td>";
            }
            $ht['tbl_by_employees'] .= "</tr>";
        }
        $ht['tbl_by_employees'] .= "</tbody></table>";
    }

    $byTypes = dbConnPDO()->query("
            SELECT qr.name, 
                   qr.id reason_id, 
            count(*) count_questions,
            count(IF(f.report_status = 'DONE', 1, null)) count_done,
            count(IF(f.report_status = 'CANCEL', 1, null)) count_cancel,
            count(IF(f.report_status = 'IN_PROCESS' or f.report_status is null, 1, null)) count_in_progress,
            sum(rt.reaction_time) summary_question_load, 
            count(if(ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) > rt.reaction_time, 1, null)) count_reached_reason,
            sum(if(ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) > rt.reaction_time, ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) - rt.reaction_time , null)) summary_reached_reason,
            count(if(ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) <= rt.reaction_time, 1, null)) count_timein_reason,
            sum(if(ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) <= rt.reaction_time, ROUND(TIME_TO_SEC(timediff(report_time,dest_time))/60) - rt.reaction_time , null)) summary_timein_reason
            FROM questions_full f 
            JOIN clients c on c.id = f.agreement 
            JOIN question_reason qr on qr.id = f.reason_id    
            JOIN v_reaction_times as rt on rt.reason_id = f.reason_id and rt.house_id = c.house
            JOIN employees e on e.id = f.reported_employee
            $WHERE
            GROUP BY qr.name
            ORDER BY 1
            ")->fetchAll();
    if (count($byEmployees) == 0) {
        html()->addNoty('info', "По указанным параметрам ничего не найдено");
        $ht['tbl_by_types'] = "<h4 align='center'>По запросу ничего не найдено</h4>";
    } else {
        $ht['tbl_by_types'] .= "<table class='table table-bordered table-sm' id='tbl-by-types'><thead><tr>";
        foreach ($tableFields as $tf) {
            $ht['tbl_by_types'] .= "<th>{$tf}</th>";
        }
        $ht['tbl_by_types'] .= "</tr></thead><tbody>";

         foreach ($byTypes as $d) {

            $color = '#F0F0F0';
            $prc = 0;
            if ($d['count_cancel'] + $d['count_done'] !== 0) {
                $prc = round(($d['count_reached_reason'] / ($d['count_cancel'] + $d['count_done']) * 100), 2);
            }
            if ($prc > 80) {
                $color = "#ff3333";
            } elseif ($prc > 60) {
                $color = "#ff6666";
            } elseif ($prc > 40) {
                $color = "#ffb3b3";
            } elseif ($prc > 20) {
                $color = "#ffe6e6";
            }
            $ht['tbl_by_types'] .= "<tr style='background: {$color}'>";
            foreach ([
                         [$d['name'], 'font-weight: bold',],
                         [$d['count_questions'], '',],
                         [$d['count_done'] . '/' . $d['count_cancel'] . '/' . $d['count_in_progress'], '',],
                         [round($d['summary_question_load'] / 60, 2), '',],
                         [$d['count_reached_reason'], 'font-weight: bold',],
                         [round($d['summary_reached_reason'] / 60, 2), '',],
                         [$prc . '%', 'font-weight: bold',],
                         ["<a href='/users/reaction_stat_detail?" . url_array_encode($form) . "&t=type&v={$d['reason_id']}'><i class='fa fa-search'></i></a>", ''],
                     ] as $c) {
                $ht['tbl_by_types'] .= "<td style='" . (isset($c[1]) ? $c[1] : '') . "'>{$c[0]}</td>";
            }
            $ht['tbl_by_types'] .= "</tr>";
        }
        $ht['tbl_by_types'] .= "</tbody></table>";
    }

#region Расчеты для графиков
    $summaryQuestions = 0;
    $summaryReached = 0;
    foreach ($byTypes as $d) {
        $summaryQuestions += $d['count_cancel'] + $d['count_done'] + $d['count_in_progress'];
        $summaryReached += $d['count_reached_reason'];

    }
    $JSONS['pie_by_status'][] = [
        'name' => 'Просрочено',
        'y' => ($summaryReached) / $summaryQuestions * 100,
    ];
    $JSONS['pie_by_status'][] = [
        'name' => 'Вовремя',
        'y' => ($summaryQuestions - $summaryReached) / $summaryQuestions * 100,
    ];
    foreach ($byTypes as $d) {
        if($summaryQuestions) {
            $JSONS['pie_by_type'][] = [
                'name' => $d['name'],
                'y' => ($d['count_cancel'] + $d['count_done'] + $d['count_in_progress']) / $summaryQuestions * 100,
            ];
        }
    }
    foreach ($byEmployees as $d) {
        if($summaryQuestions) {
            $JSONS['pie_by_employees'][] = [
                'name' => $d['name'],
                'y' => ($d['count_cancel'] + $d['count_done'] + $d['count_in_progress']) / $summaryQuestions * 100,
            ];
        }
    }

#endregion
}




?><?= tpl('head', ['title' => '']) ?>
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Сводные по реакции</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-horizontal form-label-left input_mask row">
                    <form method="GET">
                        <div class=" col-xs-6 col-sm-3 col-md-3 col-lg-2">
                            <label class="control-label">Начало</label>
                            <input class="form-control" name="start" id="start" value="<?= $form['start'] ?>">
                        </div>
                        <div class=" col-xs-6 col-sm-3 col-md-3 col-lg-2">
                            <label class="control-label">Конец</label>
                            <input class="form-control" name="stop" id="stop" value="<?= $form['stop'] ?>">
                        </div>
                        <div class=" col-xs-6 col-sm-3 col-md-3 col-lg-2">
                            <label class="control-label">Группа</label>
                            <select id="types" name='types[]' multiple="multiple"><?= $ht['types'] ?></select>
                        </div>
                        <div class=" col-xs-6 col-sm-3 col-md-3 col-lg-2">
                            <label class="control-label">Персонал</label>
                            <select id="employees" name="employees[]"
                                    multiple="multiple"><?= $ht['employees'] ?></select>
                        </div>
                        <div class=" col-xs-6 col-sm-3 col-md-3 col-lg-2">
                            <label class="control-label"> &nbsp</label>
                            <button type="submit" name="action" class="btn btn-primary btn-block" value="search">
                                Отобразить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php if ($form['action']) { ?>

        <div class="col-sm-12">
            <h3>Общее</h3>
        </div>
        <div class="col-lg-4 col-sm-4 col-md-6 col-xs-12">
            <div id="pie-by-type"></div>
        </div>
        <div class="col-lg-4 col-sm-4 col-md-6 col-xs-12">
            <div id="pie-by-employees"></div>
        </div>
        <div class="col-lg-4 col-sm-4 col-md-6 col-xs-12">
            <div id="pie-by-status"></div>
        </div>

        <div class="col-sm-12">
            <h3>По персоналу</h3>
            <?= $ht['tbl_by_employees'] ?>
        </div>
        <div class="col-sm-12">
            <h3>По типу</h3>
            <?= $ht['tbl_by_types'] ?>
        </div>

        <script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script>
            $(document).ready(function () {
                // Radialize the colors
                Highcharts.setOptions({
                    colors: Highcharts.map(Highcharts.getOptions().colors, function (color) {
                        return {
                            radialGradient: {
                                cx: 0.5,
                                cy: 0.3,
                                r: 0.7
                            },
                            stops: [
                                [0, color],
                                [1, Highcharts.color(color).brighten(-0.3).get('rgb')] // darken
                            ]
                        };
                    })
                });

                // Build the chart
                Highcharts.chart('pie-by-type', {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: 'Заявки по типу',
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: false,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                connectorColor: 'silver'
                            }
                        }
                    },
                    series: [{
                        name: 'Заявок',
                        data: <?=json_encode($JSONS['pie_by_type'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)?>
                    }]
                });

                // Build the chart
                Highcharts.chart('pie-by-employees', {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: 'Заявки по сотрудникам',
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: false,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                connectorColor: 'silver'
                            }
                        }
                    },
                    series: [{
                        name: 'Заявок',
                        data: <?=json_encode($JSONS['pie_by_employees'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)?>
                    }]
                });

                // Build the chart
                Highcharts.chart('pie-by-status', {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: 'Просроченные',
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: false,
                                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                                connectorColor: 'silver'
                            }
                        }
                    },
                    series: [{
                        name: 'Всего',
                        data: <?=json_encode($JSONS['pie_by_status'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)?>
                    }]
                });


            });
        </script>
        <div class="col-sm-12">
            <b>Всего</b> - Количество заявок за период <br>
            <b>Вып/Отм/Процесс</b> - Детализация заявок (Выполнено/Отменено/В Процессе) <br>
            <b>Нагрузка(час)</b> - Предполагаемая затрата часов на основе типа заявок<br>
            <b>Просроченных(кол)</b> - Количество просроченных заявок. Время с последнего отчета превышает время реакции
            <br>
            <b>Просроченных(час)</b> - Сумарное просроченное время в часах<br>
            <b>Просроченных(%)</b> - Процент просроченных заявок. Высчитается по: Всего заявок / Закрытых заявок (заявки
            со статусом в процессе или без отчетов не учитываюся)<br>
        </div>
    <?php } else { ?>
        <div class="col-sm-12">
            <h3 align="center" style="margin-top: 50px">Укажите фильтр и нажмите "отобразить"</h3>
        </div>
    <?php } ?>
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
        $('#employees').multiselect({
            includeSelectAllOption: true,
        });
        $('#types').multiselect({
            includeSelectAllOption: true,
        });
        $('#period').val($('#start').val() + ' - ' + $('#stop').val());
    });
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

    $(document).ready(function () {
        $('#tbl-by-types').DataTable({
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

            "searching": false,
            "ordering": true,
            "scrollX": true,
            "bLengthChange": true,
            "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "Все"]]
        });
        $('#tbl-by-employees').DataTable({
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

            "searching": false,
            "ordering": true,
            "scrollX": true,
            "bLengthChange": true,
            "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "Все"]]
        });
    });
</script>
<?= tpl('footer') ?>
