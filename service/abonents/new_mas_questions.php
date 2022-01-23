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
    'agreements' => '',
    'comment' => '',
    'time' => '',
    'reason' => 0,
    'responsible' => 0,
    'action' => '',
];
\envPHP\classes\std::Request($form);
$agreements = [];
if ($form['agreements']) {
    $agreeIds = explode(',', trim($form['agreements']));
    foreach ($agreeIds as $agree) {
        $client = (new \envPHP\structs\Client())->fillById((int) $agree);
        $agreements[] = $client;
    }
}


$message = '';
$agree = '';

$ht = [
    'reasons' => '',
    'message' => '',
    'responsible' => '',
    'auto_choosed' => false,
    'go_to_questions' => '',
];

if ($form['action'] && count($agreements) > 0) {
    if (!\envPHP\service\PSC::isPermitted('question_create')) {
        html()->addNoty('error', 'Недостаточно прав для создания заявок');
        goto AFTER_ACTION;
    }
    foreach ($agreements as $client) {
        $phone = '';
        foreach ($client->getContacts() as $contact) {
            if ($contact->getType() === 'PHONE' && $contact->isMain()) {
                $phone = $contact->getValue();
            }
        }
        $test = $sql->query("INSERT INTO questions (agreement, created, phone, reason) 
              VALUES (%1,NOW(),%2, %3)", $client->getId(), $phone, $form['reason']);
        $questionId = dbConn()->insert_id;
        $test = $sql->query("INSERT INTO question_comments (created_at, question, dest_time, `comment`, employee, responsible_employee, entrance, floor)
VALUES (NOW(), %1, STR_TO_DATE(%2,'%d.%m.%Y %H:%i'), %3, %4, %5, %6, %7)", $questionId, $form['time'], $form['comment'], _uid, $form['responsible'], $client->getEntrance(), $client->getFloor());
        $form['id'] = $questionId;
        \envPHP\EventSystem\EventRepository::getSelf()->notify("question:created", [
            'agreement_id' => $client->getId(),
            'phone' => $phone,
            'reason_id' => $form['reason'],
            'destination_time' => $form['time'],
            'id' => $questionId,
            'responsible_employee_id' => $form['responsible'],
            'employee_id' => _uid,
            'comment' => $form['comment'],
        ]);
    }
    html()->addNoty('success', "Успешно создано " . count($agreements) . " заявок");
    $date = DateTime::createFromFormat("d.m.Y H:i", $form['time'])->format("d.m.Y");
    $ht['go_to_questions'] = <<<HTML
        <a href="/abonents/questions?type_date=1&date1={$date}&date2={$date}&reason={$form['reason']}&action=search&responsible={$form['responsible']}" class="btn btn-primary">Перейти к заявкам</a>
HTML;
    $form = [
        'agreements' => $form['agreements'],
        'comment' => '',
        'time' => '',
        'reason' => 0,
        'responsible' => 0,
        'action' => '',
    ];

}
AFTER_ACTION:


$reactionTime = 0;
if ($form['reason']) {
    $reactionTime = $sql->query("SELECT reaction_time FROM question_reason WHERE id='{$form['reason']}'")->fetch_assoc()['reaction_time'];
}
//Получение временных слотов
$DATES = [];
$TOS_CONF = getGlobalConfigVar('TOS');
$startDay = time();
$endDay = time() + ($TOS_CONF['display_add_days'] * 86400);

$slotObj = new \envPHP\Schedule\TimeSlot();
$slots = $slotObj->getSlots(
    $startDay,
    $endDay,
    null
);

$datesArr = $slotObj
    ->addQuestionsLayout($slots)
    ->addScheduleLayout($slots)
    ->getQuestionSlots($slots, $reactionTime);
foreach ($datesArr as $d) {
    $hour = date("H", $d['time']);
    if ($hour < $TOS_CONF['hours_create_question']['start'] || $hour > $TOS_CONF['hours_create_question']['end']) {
        continue;
    }
    $DATES[] = [
        'date' => date("Y-m-d H:i:s", $d['time']),
        'status' => strtolower($d['status']),
        'employee' => $d['employee_id'],
    ];
}
if (!$form['time'] && count($DATES) > 0) {
    foreach ($DATES as $date) {
        if ($date['status'] === 'free') {
            $form['time'] = $date['date'];
            $form['responsible'] = $date['employee'];
            $ht['auto_choosed'] = true;
            break;
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
    <link rel="stylesheet" href="/res/question-schedule/schedule.css">
    <div id="choose-date-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" id="choose-date-modal-body">
            <div class="modal-content">
                <div class="modal-header" style="margin: 0; padding 0;">
                    <button type="button" style="margin: 0; padding 0;" class="close" data-dismiss="modal"
                            aria-hidden="true">×
                    </button>
                    <h4 class="modal-title" style="margin: 0; padding 0;">Выберите время и дату заявки</h4>
                </div>
                <div class="modal-body">
                    <div style="overflow: scroll; " id="schedule-calendar-overflow">
                        <div id="schedule-calendar"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-xs-10 col-lg-10 col-md-10 col-sm-10">
                            <div style="float: left; text-align: left" id="selected-date-message"></div>
                        </div>
                        <div class="col-xs-2 col-lg-2 col-md-2 col-sm-2">
                            <button type="button" onclick="closeModal(); return false;"
                                    class="btn btn-primary btn-block">OK
                            </button>
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
                    <h2>Создание заявок</h2>
                    <?php if ($info) { ?>
                        <a href="/abonents/detail?id=<?= $info['id'] ?>" style="float: right" class="btn btn-primary">К
                            договору</a>
                    <?php } ?>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form method="POST" class="form-horizontal form-label-left row" enctype="multipart/form-data">
                        <input name="agreements" value="<?=$form['agreements']?>" type="hidden" hidden>
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
                            <div class=" col-md-8 col-xs-12">
                                <input type="text" name="time" , value="<?= $form['time'] ?>" class="form-control"
                                       id="choose-date-input" onclick="showChooseDateModal(); return false;">
                                <!--   <?= html()->formDateTime("time", $form['time']) ?> -->
                            </div>
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
                            <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-4">
                                <button type="submit" class="btn btn-primary" style="margin-bottom: 10px" name="action"
                                        value="save">Внести заявки
                                </button>
                                <a href='<?= $_SESSION['LAST_PAGE'] ?>' style="margin-bottom: 10px"
                                   class='btn btn-primary'>Назад</a>
                                <?=$ht['go_to_questions']?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-8 col-xs-12 col-md-12 col-lg-8">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Выбранные договора</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <?php
                        foreach ($agreements as $agreement) {
                            echo "<div style='display: table-cell; padding: 5px; font-size: 16px'><a href='/abonents/detail?id={$agreement->getId()}'>{$agreement->getAgreement()}</a></div>";
                        }
                    ?>
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
            var h = window.innerHeight - 220;
            var w = window.innerWidth;
            if (w >= 1260) {
                $("#choose-date-modal-body").css({'width': '85%'})
            }
            console.log("Detected height=" + h)
            $("#schedule-calendar-overflow").css({'height': h + 'px'})
        });
        window.addEventListener('load', function () {
            $("#choose-date-modal").on('shown.bs.modal', function () {
                $('#preload').hide();
            });
            var h = window.innerHeight - 220;
            var w = window.innerWidth;
            if (w >= 1260) {
                $("#choose-date-modal-body").css({'width': '85%'})
            }

            console.log("Detected height=" + h)
            $("#schedule-calendar-overflow").css({'height': h + 'px'})
        });
        const pre = {
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
            if (clickedDate === date) {
                console.log("Double click detected with date " + date)
                $('#choose-date-modal').modal('hide')
            }
            clickedDate = date
            if (typeof pre.employees[event.employee] !== 'undefined') {
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