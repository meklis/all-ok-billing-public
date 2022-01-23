<?php
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

$ht = [
    'groups' => '',
    'prices' => '',
    'table' => '',
    'table_blc' => '',
    'quest_created' => 0,
    'quest_today' => 0,
    'quest_closed' => 0,
    'quest_me_closed' => 0,
    'unregistered_omo' => '',
    'pinger_logs' => '',
];

$data = $sql->query("SELECT id, name FROM addr_groups order by name");
while ($d = $data->fetch_assoc()) {
    $ht['groups'] .= "<OPTION value='{$d['id']}'>{$d['name']}</OPTION>";
}

$data = $sql->query("SELECT id, CONCAT(name, ' (', price_day, ')') name FROM bill_prices WHERE `show` = 1 ORDER BY name ");
while ($d = $data->fetch_assoc()) {
    $ht['prices'] .= "<OPTION value='{$d['id']}'>{$d['name']}</OPTION>";
}

$ht['quest_created'] = $sql->query("SELECT id FROM questions_full WHERE CAST(created as date) = cast(NOW() as date); ")->num_rows;
$ht['quest_today'] = $sql->query("SELECT id FROM questions_full WHERE CAST(dest_time as date) = cast(NOW() as date);")->num_rows;
$ht['quest_closed'] = $sql->query("SELECT id FROM questions_full WHERE CAST(report_time as date) = cast(NOW() as date) and report_status in  ('DONE', 'CANCEL') ;")->num_rows;
$ht['quest_me_closed'] = $sql->query("SELECT id FROM questions_full WHERE CAST(report_time as date) = cast(NOW() as date) and report_status in  ('DONE', 'CANCEL') and responsible_employee = '"._uid."' ;")->num_rows;



//Выборка логов с пингера
$logs = dbConnPDO()->query("
SELECT e.id, a.full_addr, e.ip, e.entrance, m.`name`, e.ip, s.down, s.up, TIMEDIFF(up,down) down_time, if(s.up is null, NOW(), s.down) order_date 
FROM v_eq_ping_status s 
JOIN equipment e on e.id = s.equipment
JOIN equipment_models m on m.id = e.model 
JOIN addr a on a.id = e.house
ORDER BY order_date desc 
LIMIT 400 
")->fetchAll();
foreach ($logs as $log) {
    $ht['pinger_logs'] .= <<<HTML
    <tr>
        <td>{$log['full_addr']}, под. {$log['entrance']}</td>
        <td>{$log['name']}</td>
        <td><a href="/equipment/show?id={$log['id']}">{$log['ip']}</a></td>
        <td>{$log['down']}</td>
        <td>{$log['up']}</td>
        <td>{$log['down_time']}</td>
    </tr>
HTML;
}
if($ht['pinger_logs']) {
    $ht['pinger_logs'] = <<<HTML
<table class="table table-striped table-bordered table-sm table-hover" style="font-size: 95%">
    <tr>
        <th style="min-width: 300px">Адрес</th>
        <th style="min-width: 150px">Имя</th>
        <th>IP</th>
        <th style="min-width: 150px">Время падения</th>
        <th style="min-width: 150px">Время поднятия</th>
        <th >Время простоя</th>
    </tr>
    {$ht['pinger_logs']}
</table>
HTML;


}

if($tos = getGlobalConfigVar('TOS')) {
    $TOS_START = $tos['hours_schedule']['start'];
    $TOS_END = $tos['hours_schedule']['end'];
}

use Composer\Autoload\ClassLoader;?>
<?=tpl('head', ['title' => 'Главная'])?>
    <link rel="stylesheet" href="/res/schedule-calendar/calendar.css">
    <script src="/res/schedule-calendar/calendar.js"></script>
    <script src="/res/js/popper.js"></script>

    <div class='row' >
        <div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Состояние</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content ">
                        <div class="col-lg-3 col-md-3 col-xs-12 col-sm-3" style="margin-bottom: 10px">
                            Создано за сегодня: <b><?=$ht['quest_created']?></b><br>
                            Заявок на сегодня: <b><?=$ht['quest_today']?></b><br>
                            Закрыто: <b><?=$ht['quest_closed']?></b><br>
                            Закрыто мною: <b><?=$ht['quest_me_closed']?></b><br>
                        </div>

                    <div class="col-lg-4 col-md-4 col-xs-12 col-sm-4">
                        <a href="/abonents/questions?action=search" class="btn btn-block btn-primary btn-lg">Заявки на сегодня</a>
                        <a href="/abonents/questions?action=search&responsible=me" class="btn btn-block btn-primary btn-lg">Мои заявки</a>
                    </div>
                    <div class="col-lg-4 col-md-4 col-xs-12 col-sm-4">

                    </div>
                    <div class="col-lg-4 col-md-4 col-xs-12 col-sm-4">
                        <?=$ht['unregistered_omo']?>
                    </div>

                 </div>
            </div>
        </div>
    </div>

    <div class='row'  >
        <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Поиск абонентов<small> по номеру телефона / номеру договора / адресу </small></h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form class="form-horizontal form-label-left input_mask row" method="GET" action="/abonents/search">
                            <div class=" col-xs-6 col-sm-4 col-md-4 col-lg-2">
                                <label class="control-label">Номер договора</label>
                                <input type="text" class="form-control has-feedback-left" name="agreement" value="" id="agreement" placeholder="например, 1404">
                            </div>
                            <div class=" col-xs-6  col-sm-4  col-md-4  col-lg-3">
                                <label class="control-label">Группа</label>
                                <select name='group_id[]' multiple="multiple" id="group_id" class="form-control btn-block"><?=$ht['groups']?></select>
                            </div>
                            <div class=" col-xs-6  col-sm-4  col-md-4  col-lg-3 ">
                                <label class="control-label">Активный прайс</label>
                                <select name='price_id[]' multiple="multiple" id="price_id" class="form-control"  ><?=$ht['prices']?></select>
                            </div>
                            <div class=" col-xs-6  col-sm-6  col-md-3  col-lg-2 ">
                                <label class="control-label">Адрес/Имя</label>
                                <input name='search' id="search" value = '' class="form-control" placeholder="Ленина 10, 15">
                            </div>
                            <div class=" col-xs-6  col-sm-6  col-md-4  col-lg-2 ">
                                <label class="control-label">Телефон/Email</label>
                                <input name='contact' id="contact" value = '' class="form-control" placeholder="0440000000">
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12 col-sm-6 col-md-6  col-lg-1 " style="margin-top: 10px">
                                    <button type="submit" name="action" value="search" class="btn btn-block btn-primary">Поиск</button>
                                 </div>
                            </div>


                        </form>
                    </div>
            </div>
        </div>
    </div>
    <div class='row'  >
        <div class="col-xs-12 col-sm-12 col-md-10 col-lg-8">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Пингер<small> (логи падений)</small></h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <a href="/equipment/pinger" class="btn btn-primary btn-sm ">Перейти к пингер/свитчер</a>
                    <div class="table-responsive-light" style="max-height: 300px">
                        <?=$ht['pinger_logs']?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='row'  >
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>График дежурств</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form class="form-inline row" onsubmit="updateDates(); load(); return false;">
                        <div class="form-group col-sm-3 col-lg-2 col-xs-5">
                            <input class="form-control" id="time-start" style="margin-top: 5px; margin-bottom: 10px">
                        </div>
                        <div class="form-group col-sm-3 col-lg-2 col-xs-5">
                            <input class="form-control" id="time-end" style="margin-top: 5px;; margin-bottom: 10px">
                        </div>
                        <div class="form-group col-sm-3 col-lg-2 col-xs-2">
                            <button type="submit" class="btn btn-default" style="margin-top: 5px;; margin-bottom: 10px"><i class="fa fa-calendar"></i></button>
                        </div>
                    </form>
                    <div style="overflow: scroll; width: 100%; height: 310px">
                        <div id="preload-calendar">
                            <div style="width: 100%; height: 100%; text-align: center">
                                <img src="/res/img/spinner-blue.gif" style="margin-top: 100px;width: 56px">
                            </div>
                        </div>
                        <div id="schedule-table" style="display: none">

                        </div>

                    </div>
                    <?php
                    if(isset($TOS_START) && isset($TOS_END)) {
                        echo "
                            <div><b>НРВ</b> - Начало рабочего времени (c $TOS_START утра)</div>
                        <div><b>КРВ</b> - Конец рабочего времени (до $TOS_END вечера)</div>
                        ";
                    } else {
                        echo "
                            <div><b>НРВ</b> - Начало рабочего времени</div>
                        <div><b>КРВ</b> - Конец рабочего времени</div>
                        ";
                    }

                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#agreement').on("change paste keyup",function(){
            if($('#agreement').val() !== "") {
                $('#group_id').attr('disabled', 'disabled');
                $('#price_id').attr('disabled', 'disabled');
                $('#search').attr('disabled', 'disabled');
            } else {
                $('#group_id').removeAttr('disabled');
                $('#price_id').removeAttr('disabled');
                $('#search').removeAttr('disabled');
            }
        })
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
            if($('#agreement').val() !== "") {
                $('#group_id').attr('disabled', 'disabled');
                $('#price_id').attr('disabled', 'disabled');
                $('#search').attr('disabled', 'disabled');
            } else {
                $('#group_id').removeAttr('disabled');
                $('#price_id').removeAttr('disabled');
                $('#search').removeAttr('disabled');
            }
        })
    </script>



    <script>
        const BASE_URL = "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>"
        $.ajaxSetup({
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        });
        var days = {
            'start': moment().format('YYYY-MM-DD'),
            'end': moment().add(14, 'days').format('YYYY-MM-DD'),
        };
        var employees = [];


        $(function () {
            $('#time-start').datetimepicker({
                language: 'ru',
                pickTime: false,
                defaultDate: moment().format('DD.MM.YYYY')
            });
        });
        $(function () {
            $('#time-end').datetimepicker({
                language: 'ru',
                pickTime: false,
                defaultDate: moment().add(14, 'days').format('DD.MM.YYYY')
            });
        });

        function updateDates() {
            $('#schedule-table').hide()
            $('#preload-calendar').show()
            days = {
                start: moment(dateParser($('#time-start').val())).format('YYYY-MM-DD'),
                end: moment(dateParser($('#time-end').val())).format('YYYY-MM-DD'),
            }
        }

        function dateParser(dateStr) {
            var elements = dateStr.split(".")
            console.log(elements)
            return new Date(elements[2], elements[1] - 1, elements[0])
        }

        $(document).ready(() => {
            $('#schedule-table').hide()
            $('#preload-calendar').show()
            $.get(BASE_URL + '/v2/private/employees/responsible_list').success((r) => {
                employees = r.data
                load()
            });
        });

        function load() {
            $.get(BASE_URL + `/v2/private/employees/schedule?start=${days.start} 00:00:00&end=${days.end} 23:59:59`).success((r) => {
                let schedules = []
                r.data.forEach(elem => {
                    let grs = ''
                    if (elem.groups.length === 0) {
                        grs = '<b>Вся территория</b>'
                    } else {
                        elem.groups.forEach(g => {
                            grs += `<li>${g.name}</li>`
                        })
                    }
                    schedules.push({
                        id: elem.id,
                        start: elem.start,
                        end: elem.end,
                        employee: elem.employee.id,
                        work_type: elem.calendar.work_type,
                        description: `
                        <b>${elem.title}</b><br>
                        <i>С: ${elem.start}<br>По: ${elem.end}</i><br>
                        Группы:
                        <div style='margin-left: 2px'>
                            ${grs}
                        </div>
                       `,
                    })
                })
                let calendar = new ScheduleCalendar();
                <?php
                    if(isset($TOS_START) && isset($TOS_END)) {
                    //    echo "calendar.setHourLimit($TOS_START, $TOS_END);\n";
                    }
                ?>
                calendar.setDates(days.start, days.end).setEmployees(employees).setSchedules(schedules).render('#schedule-table').createPopper()
                $('#preload-calendar').hide()
                $('#schedule-table').show()
            });

        }

    </script>
<?=tpl('footer', ['provider'=> ''])?>
