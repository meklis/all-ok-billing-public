<?php
$rank = 5;
$urank = 8;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if (!envPHP\service\PSC::isPermitted('question_loading')) {
    pageNotPermittedAction();
}

$info = [];

$form = [
    'start' => date("d.m.Y"),
    'end' => date('d.m.Y', time() + (60 * 60 * 24 * 14)) ,
    'groups' => [],
    'action' => null,
];
\envPHP\classes\std::Request($form);

$ht = [
    'groups' => '',
];

$GROUPS = [];
$data = $sql->query("SELECT id, name FROM addr_groups order by name");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['groups']) ? "SELECTED ": "";
    $GROUPS[$d['id']] = $d['name'];
    $ht['groups'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}



//Получение временных слотов
$DATA = [];
$startDay = DateTime::createFromFormat("d.m.Y", $form['start'])->getTimestamp();
$endDay = DateTime::createFromFormat("d.m.Y", $form['end'])->getTimestamp();
$TOS_CONF = getGlobalConfigVar('TOS');

$calculateGroup = function($groupId) use ($startDay, $endDay, $TOS_CONF)
{
    $DATES = [];
    $slotObj = new \envPHP\Schedule\TimeSlot();
    $slots = $slotObj->getSlots(
        $startDay,
        $endDay,
        (new \envPHP\Schedule\ScheduleAddressGroups())->fillById($groupId)
    );

    $datesArr = $slotObj
        ->addQuestionsLayout($slots)
        ->addScheduleLayout($slots)
        ->getQuestionSlots($slots, 0);
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
    return $DATES;
};


$employees = [];
foreach (dbConnPDO()->query("SELECT id, name FROM employees order by 2")->fetchAll() as $e) {
    $employees[$e['id']] = $e['name'];
}


$HTML = '<br><br><h3 style="text-align: center">Выберите параметры для отображения и нажмите \'Отобразить\'</h3>';
if($form['action']) {
    if(!count($form['groups'])) {
        html()->addNoty('error', 'Необходимо указать хотя бы одну группу');
        goto SHOWING;
    }
    if(DateTime::createFromFormat("d.m.Y", $form['start'])->getTimestamp() > DateTime::createFromFormat("d.m.Y", $form['end'])->getTimestamp()) {
        html()->addNoty('error', "Конечная дата не может быть меньше начальной");
        goto SHOWING;
    }
    $HTML = '';

    foreach ($form['groups'] as $grId) {
        $DATA[] = [
                'id' => "group-{$grId}",
                'data' => $calculateGroup($grId),
            ];
        $HTML .= <<<HTML
<div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>{$GROUPS[$grId]}</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div  style="width: 100%; height: 400px; overflow: scroll">
                        <div id="group-{$grId}"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
HTML;

    }
}

SHOWING:
?>
<?= tpl('head', ['title' => '']) ?>
    <link rel="stylesheet"  href="/res/question-schedule/schedule.css">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Загрузка заявками по территории</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form class="form-horizontal form-label-left input_mask row" method="GET">
                        <div class=" col-xs-12  col-sm-12  col-md-12  col-lg-5">
                            <label class="control-label">Группа</label>
                            <select name='groups[]' multiple="multiple" id="groups" class="form-control btn-block"><?=$ht['groups']?></select>
                        </div>
                        <div class=" col-xs-6  col-sm-5  col-md-5  col-lg-2 ">
                            <label class="control-label">Начальная дата</label>
                            <input name="start" value="<?=$form['start']?>" class="form-control" id="start">
                        </div>
                        <div class=" col-xs-6  col-sm-5  col-md-5  col-lg-2 ">
                            <label class="control-label">Конечная дата</label>
                            <input name="end" value="<?=$form['end']?>" class="form-control" id="end">
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12 col-sm-2 col-md-2  col-lg-3 " style="margin-top: 27px">
                                <button type="submit" name="action" value="show" class="btn btn-block btn-primary">Отобразить</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <?=$HTML?>
    <script src="/res/question-schedule/schedule.js"></script>
    <script>
        $(function () {
            $('#start').datetimepicker({
                language: 'ru',
                pickTime: false,
                defaultDate: moment().format('DD.MM.YYYY')
            });
        });
        $(function () {
            $('#end').datetimepicker({
                language: 'ru',
                pickTime: false,
                defaultDate: moment().add(14, 'days').format('DD.MM.YYYY')
            });
        });
        $(document).ready(function () {
            $('#groups').multiselect({
                includeSelectAllOption: true,
            });
        })

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
            employees: <?=json_encode($employees)?>,
            data: <?=json_encode($DATA)?>,
        };

        pre.data.forEach(d => {
            console.log(d)
            let schedule = new Schedule(pre.days, pre.hours, pre.stepInMinutes);
            schedule.disablePastTime(false).setSchedules(d.data, true).render('#' + d.id)
        })
    </script>
<?= tpl('footer') ?>