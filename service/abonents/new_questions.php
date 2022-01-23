<?php
$rank = 5;
$urank = 8;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if (!envPHP\service\PSC::isPermitted('question_show')) {
    pageNotPermittedAction();
}

$info = [];

$form = [
    'phone' => '',
    'comment' => '',
    'agree' => '',
    'time' => '',
    'id' => 0,
    'reason' => 0,
    'responsible' => 0,
    'floor' => 0,
    'entrance' => 0,
];
if (isset($_REQUEST)) foreach ($_REQUEST as $k => $v) $form[$k] = $v;

$test_port = '';
$message = '';
$agree = '';

$ht = [
    'reasons' => '',
    'old_comment' => '',
    'message' => '',
    'responsible' => '',
    'auto_choosed' => false,
];

if (isset($form['action'])) {
    //check agreement is exist
    $agreeId = $sql->query("SELECT c.id FROM clients c 
        JOIN (SELECT h.id from addr_houses h JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")) houses on houses.id = c.house
        WHERE agreement = %1 LIMIT 1", $form['agree'])->fetch_assoc()['id'];

    if (!$agreeId) {
        $html->addNoty('warning', 'Указанный номер договора не найден');
    } else {
        $notifyType = 'updated';
        if ($form['id']) {
            if (!\envPHP\service\PSC::isPermitted('question_change')) {
                $html->addNoty('error', 'Недостаточно прав для изменения заявки');
                goto AFTER_ACTION;
            }
            $questionId = $form['id'];
        } else {
            if (!\envPHP\service\PSC::isPermitted('question_create')) {
                $html->addNoty('error', 'Недостаточно прав для создания заявки');
                goto AFTER_ACTION;
            }
            $notifyType = 'created';
            $test = $sql->query("INSERT INTO questions (agreement, created, phone, reason) 
              VALUES (%1,NOW(),%2, %3)", $agreeId, $form['phone'], $form['reason']);
            $questionId = dbConn()->insert_id;
        }
        $test = $sql->query("INSERT INTO question_comments (created_at, question, dest_time, `comment`, employee, responsible_employee, entrance, floor)
VALUES (NOW(), %1, STR_TO_DATE(%2,'%d.%m.%Y %H:%i'), %3, %4, %5, %6, %7)", $questionId, $form['time'], $form['comment'], _uid, $form['responsible'], $form['entrance'], $form['floor']);
        if ($test) {
            $form['id'] = $questionId;
            $message = "<div id='message_success'>Заявка создана, <a href='/abonents/detail?id=$agreeId'>перейти к карточке абонента</a></div>";
            \envPHP\EventSystem\EventRepository::getSelf()->notify("question:$notifyType", [
                'agreement_id' => $agreeId,
                'phone' => $form['phone'],
                'reason_id' => $form['reason'],
                'destination_time' => $form['time'],
                'id' => $questionId,
                'responsible_employee_id' => $form['responsible'],
                'employee_id' => _uid,
                'comment' => $form['comment'],
            ]);
        } else {
            $message = "<div id='message_fail'>Не удалось внести заявку</div>";
        }
    }
}
AFTER_ACTION:



//Получение предыдущих заявок и подгрузка договора
if ($form['id']) {
    $data = $sql->query("SELECT 
        c.created_at,
        c.dest_time,
        e.name employee,
        c.`comment` comment,
        r.name reason,
        r.id id_reason,
        a.agreement,
        q.phone,
        c.responsible_employee responsible,
        er.name responsible_name,
        c.entrance,
        c.floor
        FROM questions q 
        JOIN clients a on a.id = q.agreement 
        JOIN (SELECT h.id from addr_houses h JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")) houses on houses.id = a.house
        JOIN question_comments c on c.question = q.id 
        JOIN employees e on e.id = c.employee
        JOIN question_reason r on r.id = q.reason
        LEFT JOIN employees er on er.id = c.responsible_employee
        WHERE q.id = %1
        ORDER BY created_at desc ", $form['id']);
    while ($d = $data->fetch_assoc()) {
        if (!$form['agree']) $form['agree'] = $d['agreement'];
        if (!$form['phone']) $form['phone'] = $d['phone'];
        if (!$form['comment']) $form['comment'] = $d['comment'];
        if (!$form['time']) $form['time'] = $d['dest_time'];
        if (!$form['reason']) $form['reason'] = $d['id_reason'];
        if (!$form['responsible']) $form['responsible'] = $d['responsible'];
        if (!$form['entrance']) $form['entrance'] = $d['entrance'];
        if (!$form['floor']) $form['floor'] = $d['floor'];
        if (!$form['reason']) $form['id_reason'] = $d['id_reason'];
        if (!$d['responsible_name']) {
            $d['responsible_name'] = "Не назначено";
        }
        $ht['old_comment'] .= "
          <tr>
            <td>{$d['created_at']}</td>
            <td>{$d['employee']}</td>
            <td>{$d['dest_time']}</td>
            <td>{$d['reason']}</td>
            <td>{$d['comment']}</td>
            <td>{$d['responsible_name']}</td>
          </tr>
        ";
    }
}

if ($form['agree']) {
    $agree_info = $sql->query("
        SELECT s.*,  ha.name hous, sa.name street, ca.name city, ph.phone, ha.group_id, ag.name group_name
        FROM clients s 
        JOIN addr_houses ha on ha.id = s.house and ha.group_id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets sa on sa.id = ha.street
        JOIN addr_cities ca on ca.id = sa.city 
        JOIN addr_groups ag on ha.group_id = ag.id   
        LEFT JOIN (SELECT agreement_id, `value` phone FROM client_contacts WHERE main = 1 and type = 'PHONE') ph on ph.agreement_id = s.id 
        WHERE s.agreement = %1", $form['agree']);
    if ($agree_info->num_rows == 0) {
        $html->addNoty('warning', 'Указанный номер договора не найден');
        $agree = "<span style='color: red; font-weight: bold'>Договор не найден</span>";
    } else {
        $info = $agree_info->fetch_assoc();
        $agree = "<span style='color: green; '><B>" . $info['name'] . ", </B><BR>г." . $info['city'] . ", ул. " . $info['street'] . ", д." . $info['hous'] . ", кв." . $info['apartment'] . "<br><i>Группа: {$info['group_name']}</i></span>";
        $agreement = $info['id'];
        if ($form['phone'] == '') $form['phone'] = $info['phone'];
    }
}


$reactionTime = 0;
if ($form['reason']) {
    $reactionTime = $sql->query("SELECT reaction_time FROM question_reason WHERE id='{$form['reason']}'")->fetch_assoc()['reaction_time'];
}

//Получение временных слотов
$DATES = [];
$TOS_CONF = getGlobalConfigVar('TOS');
$startDay = time();
$endDay = time() + ($TOS_CONF['display_add_days'] * 86400);
if($form['agree'] && $form['reason'] && isset($info)) {

    $slotObj = new \envPHP\Schedule\TimeSlot();
    $slots = $slotObj->getSlots(
        $startDay,
        $endDay,
        (new \envPHP\Schedule\ScheduleAddressGroups())->fillById($info['group_id'])
    );

    $datesArr = $slotObj
        ->addQuestionsLayout($slots)
        ->addScheduleLayout($slots)
        ->getQuestionSlots($slots, $reactionTime);
    foreach ($datesArr as $d) {
        $hour = date("H", $d['time']);
        if($hour < $TOS_CONF['hours_create_question']['start'] || $hour > $TOS_CONF['hours_create_question']['end']) {
            continue;
        }
        $DATES[] = [
            'date' => date("Y-m-d H:i:s", $d['time']),
            'status' => strtolower($d['status']),
            'employee' => $d['employee_id'],
        ];
    }
    if(!$form['time'] && count($DATES) > 0) {
        foreach ($DATES as $date) {
            if($date['status'] === 'free') {
                $form['time'] = $date['date'];
                $form['responsible'] = $date['employee'];
                $ht['auto_choosed'] = true;
                break;
            }
        }
    }
}

//ПОлучение списка причин
$data = $sql->query("SELECT id, name FROM question_reason WHERE display = 'YES' ORDER BY 1;");
$ht['reasons'] .= "<OPTION value='0'>Не указана</OPTION>";
while ($d = $data->fetch_assoc()) {
    $sel = $d['id'] == $form['reason'] ? "SELECTED " : "";
    $ht['reasons'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

//Получение списка отвестветственных
$data = $sql->query("SELECT e.id,  p.position , e.name  FROM employees e JOIN emplo_positions p on p.id = e.position WHERE display = 1 ORDER BY 2,3 ");
$ht['responsible'] .= "<OPTION value='0'>Не назначено</OPTION>";
while ($d = $data->fetch_assoc()) {
    $sel = $d['id'] == $form['responsible'] ? "SELECTED " : "";
    $ht['responsible'] .= "<OPTION value='{$d['id']}' $sel>{$d['position']} - {$d['name']}</OPTION>";
}


$employees = [];
foreach (dbConnPDO()->query("SELECT id, name FROM employees order by 2")->fetchAll() as $e) {
    $employees[$e['id']] = $e['name'];
}

?>
<?= tpl('head', ['title' => '']) ?>
    <link rel="stylesheet"  href="/res/question-schedule/schedule.css">
    <div id="choose-date-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" id="choose-date-modal-body">
            <div class="modal-content" >
                <div class="modal-header" style="margin: 0; padding 0;">
                    <button type="button" style="margin: 0; padding 0;" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" style="margin: 0; padding 0;">Выберите время и дату заявки</h4>
                </div>
                <div class="modal-body" >
                    <div style="overflow: scroll; " id="schedule-calendar-overflow">
                        <div id="schedule-calendar"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-xs-10 col-lg-10 col-md-10 col-sm-10">
                            <div style="float: left; text-align: left"  id="selected-date-message"></div>
                        </div>
                        <div class="col-xs-2 col-lg-2 col-md-2 col-sm-2">
                            <button type="button" onclick="closeModal(); return false;" class="btn btn-primary btn-block">OK</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4 col-xs-12 col-md-12 col-lg-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Создание/изменение заявки</h2>
                    <?php if($info) { ?>
                    <a href="/abonents/detail?id=<?=$info['id']?>" style="float: right" class="btn btn-primary">К договору</a>
                    <?php } ?>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form method="POST" class="form-horizontal form-label-left row" enctype="multipart/form-data">
                        <?= $ht['message'] ?>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Номер договора<span
                                        class="required">*</span>
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input name='agree' class="form-control" onchange='submit()'
                                       value='<?= $form['agree'] ?>' required placeholder="123456">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree"></label>
                            <div class="col-md-4-offset col-sm-4-offset  col-md-8 col-xs-12">
                                <?= $agree ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="phone">Номер телефона
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input name='phone' value='<?= $form['phone'] ?>' class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="reason">Причина
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <SELECT name="reason" onchange='submit()'
                                        class="form-control block" <?= $form['reason'] && $form['id'] ? "DISABLED" : "" ?>>
                                    <?= $ht['reasons'] ?>
                                </SELECT>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="time">На когда
                            </label>
                            <?php if($form['reason'] && $form['agree']) { ?>
                            <div class=" col-md-8 col-xs-12">
                                <input type="text" name="time", value="<?=$form['time']?>"  class="form-control" id="choose-date-input" onclick="showChooseDateModal(); return false;">
                             <!--   <?= $html->formDateTime("time", $form['time']) ?> -->
                            </div>
                            <?php } else {?>
                                <div class=" col-md-8 col-xs-12" style="font-weight: bold; color: darkred;">
                                    Для выбора времени необходимо указать договор и причину
                                </div>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="responsible">Отвественный
                            </label>
                            <div class="col-md-8 col-xs-12">
                                <SELECT name="responsible" id="responsible" class="form-control">
                                    <?= $ht['responsible'] ?>
                                </SELECT>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="comment">Коментарий
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <textarea class='form-control' name='comment'
                                          style="width: 100%; height: 140px"><?= $form['comment'] ?></textarea>
                            </div>
                        </div>

                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-sm-6 col-md-6 col-xs-12 col-lg-6">
                                <label for="entrance">Подьезд</label>
                                <INPUT name="entrance" id="entrance" class="form-control"
                                       value="<?= $form['entrance'] ?>">
                            </div>
                            <div class="col-sm-6 col-md-6 col-xs-12 col-lg-6">
                                <label for="floor">Этаж</label>
                                <INPUT name="floor" id="floor" class="form-control" value="<?= $form['floor'] ?>">
                            </div>
                        </div>


                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-4">
                                <button type="submit" class="btn btn-primary" style="margin-bottom: 10px" name="action"
                                        value="save">Внести заявку
                                </button>
                                <a href='<?= $_SESSION['LAST_PAGE'] ?>' style="margin-bottom: 10px"
                                   class='btn btn-primary'>Назад</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-8 col-xs-12 col-md-12 col-lg-8">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Ранее внесенные коментарии</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">

                    <div class="table-responsive-light">
                        <table class="table table-bordered table-responsive-light table-striped" align='center'
                               style='width: 100%; margin-top: 10px;'>
                            <thead>
                            <tr>
                                <th>Создано</th>
                                <th>Кем создано</th>
                                <th>На когда</th>
                                <th>Причина</th>
                                <th>Коментарий</th>
                                <th>Отвественный</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?= $ht['old_comment'] ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/res/question-schedule/schedule.js"></script>
    <script>
        function showChooseDateModal() {
            var preloader = $('#preload');
            preloader.show()
            $('#choose-date-modal').modal('show')

        }
        function closeModal() {
            $('#choose-date-modal').modal('hide')
        }
        window.addEventListener("resize", () => {
            var h = window.innerHeight - 220 ;
            var w = window.innerWidth;
            if(w >= 1260) {
                $("#choose-date-modal-body").css({'width': '85%'})
            }
            console.log("Detected height="+h)
            $("#schedule-calendar-overflow").css({'height': h + 'px'})
        });
        window.addEventListener('load', function() {
            $( "#choose-date-modal" ).on('shown.bs.modal', function(){
                $('#preload').hide();
            });
            var h = window.innerHeight - 220 ;
            var w = window.innerWidth;
            if(w >= 1260) {
                $("#choose-date-modal-body").css({'width': '85%'})
            }

            console.log("Detected height="+h)
            $("#schedule-calendar-overflow").css({'height': h + 'px'})
        });
        const pre =  {
            days: {
                start: '<?=date("Y-m-d", $startDay)?>',
                end: '<?=date("Y-m-d", $endDay)?>',
            },
            hours: {
                'start': <?=$TOS_CONF['hours_create_question']['start']?>,
                'end': <?=$TOS_CONF['hours_create_question']['end']?>,
            },
            stepInMinutes:  <?=$TOS_CONF['slot_time']?>,
            schedules: <?=json_encode($DATES)?>,
            employees: <?=json_encode($employees)?>,
        }
        var schedule = new Schedule(pre.days, pre.hours, pre.stepInMinutes);
        schedule.disablePastTime(true).setSchedules(pre.schedules).render('#schedule-calendar')
        var clickedDate = ''
        schedule.bind(function (event) {
            let date = moment(event.date * 1000).format("DD.MM.YYYY HH:mm");
            console.log(event)
            if(clickedDate === date) {
                console.log("Double click detected with date " + date)
                $('#choose-date-modal').modal('hide')
            }
            clickedDate = date
            if(typeof pre.employees[event.employee] !== 'undefined') {
                $('#selected-date-message').html(`
                    Выбраное время: <b>${date}</b><br>
                    Будет назначен: <b>${pre.employees[event.employee]}</b>
                `)
            } else {
                $('#selected-date-message').html(`
                    Выбраное время: <b>${date}</b><br>
                    <span style="color: darkred; font-weight: bold">Внимание, исполнителя нужно назначить вручную!</span>
                `)
            }
            $('#choose-date-input').val(date);
            $("#responsible").val(event.employee);

            setTimeout(() => {
                clickedDate = ''
            }, 300)
        }, true)
    </script>
<?= tpl('footer') ?>