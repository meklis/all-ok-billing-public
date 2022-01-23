<?php

use envPHP\classes\Num2TextUa;
use envPHP\classes\std;
use envPHP\pdf\CompletionPdfPrinter;
use \Mpdf\Mpdf;

$rank = 5;
$urank = 8;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(!\envPHP\service\PSC::isPermitted('question_report_show')) {
    pageNotPermittedAction();
}

$form = [
    'status'=>'',
    'comment'=>'',
    'id' => 0,
    'amount' => '0',
    'completion' => [],
];
std::Request($form);

$statuses = [
    'IN_PROCESS'=>'В процессе',
    'DONE'=>'Выполнена',
    'CANCEL'=>'Отменена',
    '' => 'Без отчета',
];


$ht = [
    'status'=> '',
    'old_comment' => '',
    'message' => '',
    'proccess_comment' => '',
    'process_comment' => '',
];


if(isset($form['action'])) {
    if(!\envPHP\service\PSC::isPermitted('question_report_change')) {
        $html->addNoty('error', 'Недостаточно прав для внесения отчета');
        goto AFTER_ACTION;
    }
    if($form['status'] == 'DONE' && getGlobalConfigVar('QUESTION_RULES') && getGlobalConfigVar('QUESTION_RULES')['enabled']) {
        try {
            $rules = new \envPHP\service\QuestionRules($form['id']);
            $rules->proccess(_uid);
        } catch (\Exception $e) {
            $html->addNoty('error', "Возникли с автоматическим управлением услуг - " . $e->getMessage());
        }
    }
    $test = dbConn()->query("INSERT INTO question_responses (created_at, question, `comment`, `status`, employee, amount)
                               VALUES (NOW(), '{$form['id']}','{$form['comment']}','{$form['status']}','". _uid ."', '{$form['amount']}');");
    $errors = "";
    $hash = "";
    if($test) {
        $response_id = dbConn()->insert_id;

        //Генерация акта выполненных работ
        $cfg_completion = getGlobalConfigVar('CERT_OF_COMPLETION');
        $completionForm = [];
        foreach ($form['completion'] as $elem) {
            if($elem['name'] === '') continue;
            $completionForm[] = $elem;
        }
        if(count($completionForm) === 0) {
            $form['completion'] = [];
        }
        if(count($form['completion']) != 0 && $cfg_completion['enabled']) {
            try {

                $cert_data = addslashes(json_encode($form['completion']));
                    //Получение инфы о абоненте
                $abonent_info = dbConn()->query("SELECT c.name, c.agreement, CONCAT(a.full_addr, ', кв. ', c.apartment) addr 
                FROM questions q 
                JOIN question_responses r on r.question = q.id 
                JOIN clients c on c.id = q.agreement
                JOIN addr a on a.id = c.house
                WHERE r.id = $response_id")->fetch_assoc();
                if (!$abonent_info['agreement']) {
                   throw new \Exception("Ошибка получение информации о абоненте, обратитесь к администратору");
                }

                $template = file_get_contents($cfg_completion['template_path'] . "/cert_of_completion/template.html");
                if(!$template) {
                    throw new \Exception("Ошибка получения шаблона акта выполненных работ. Обратитесь к администратору!");
                }
                $completion_printer = new CompletionPdfPrinter(new Mpdf([
                    'tempDir' => '/tmp/php/pdf',
                    'format' => [210, 297],
                ]));

                $summary_amount = 0;
                foreach ($form['completion'] as $c) {
                    if($c['price'] === '') continue;
                    $completion_printer->addCompletionRow($c['name'], $abonent_info['agreement'], $c['count'], $c['price']);
                    $summary_amount += ($c['count'] * $c['price']);
                }
                $completion_printer->setVariables([
                    'ACT_NUMBER' => $response_id,
                    'ACT_DATE' => date("d.m.Y") . "р",
                    'ABON_NAME' => $abonent_info['name'],
                    'ABON_ADDR' => $abonent_info['addr'],
                    'SUMMARY_AMOUNT_AS_TEXT' => Num2TextUa::getTextByNum($summary_amount),
                    'SUMMARY_AMOUNT' => $summary_amount,
                ]);

                $completion_printer->setTemplate($template)->prepareTemplate()->save($cfg_completion['path'] . "/" . $response_id .".pdf");

                if (!dbConn()->query("UPDATE question_responses SET cert_of_completion = '$cert_data', amount='$summary_amount' WHERE id = $response_id")) {
                    throw new \Exception('Ошибка внесения акта выполненных работ в базу: ' . dbConn()->error);
                }
                $html->addNoty('success', "Акт выполненных работ успешно сгенерирован, id: $response_id");
            } catch (\Exception $e) {
                $html->addNoty('error', $e->getMessage());
            }
        }

        //Выгрузка фоточек
        if(isset($_FILES['pictures'])) {
            foreach ($_FILES['pictures']['error'] as $key => $error) {
                if ($error == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES["pictures"]["tmp_name"][$key];
                    if (!\envPHP\classes\std::isPicture($tmp_name)) {
                        $html->addNoty('error', "Разрешено загружать только изображения! Файл  {$_FILES['pictures']['name'][$key]} проигнорирован");
                        continue;
                    }
                    if(preg_match('/^.*?\.(jpg|jpeg|png|gif)$/i',  $_FILES["pictures"]["name"][$key], $matches)) {
                        $hash = @md5_file($tmp_name);
                    }  else {
                        $html->addNoty('error', "Не удалось прочитать расширение файла");
                        continue;
                    }
                    if (!$hash) {
                        $html->addNoty('error', "Не удалось получить хеш файла");
                        continue;
                    }
                    $name = $hash . "." . $matches[1];
                    if(!move_uploaded_file($tmp_name, getGlobalConfigVar('PICTURES')['system_path'] . "/" . $name )) {
                        $html->addNoty('error', "Не удалось загрузить изображение {$_FILES['pictures']['name'][$key]}");
                    }
                    $test = $sql->query("INSERT INTO `question_responses_pictures` (response_id, name) VALUES ('{$response_id}', '{$name}');");
                    if (!$test) {
                        $html->addNoty('error', "Не удалось закрепить сохраненное изображение {$_FILES['pictures']['name'][$key]}");
                    }
                } else {
                    $html->addNoty('error', "Не удалось загрузить изображение {$_FILES['pictures']['name'][$key]} - неизвестная ошибка");
                }
            }
        }

        \envPHP\EventSystem\EventRepository::getSelf()->notify("question:report", [
            'question_id' => $form['id'],
            'id' => $response_id,
            'employee_id' => _uid,
            'comment' => $form['comment'],
            'status' => $form['status'],
            'amount' => $form['amount'],
        ]);

        //Генерация акта

        $html->addNoty('success', "Отчет успешно внесен");
    } else {
        $html->addNoty('error', "Не удалось внести отчет");
    }
}
AFTER_ACTION:

//Получение информации по заявке
$questionInfo = $sql->query("SELECT q.created, e.name created_employee, q.phone, q.reason, q.`comment`, q.dest_time, q.report_status, q.amount 
FROM questions_full q
JOIN clients c on c.id = q.agreement
JOIN employees e on e.id = q.created_employee
WHERE q.id = %1
LIMIT 1 ", $form['id'])->fetch_assoc();


//Получение предыдущих коментариев по заявке
if ($form['id']) {
    $data = $sql->query("SELECT r.id, r.created_at, r.comment, r.status, e.name employee, GROUP_CONCAT(rp.`name`) images, r.amount , r.cert_of_completion, r.cert_subscribed
FROM question_responses r 
JOIN employees e on e.id = r.employee 
LEFT JOIN question_responses_pictures rp on rp.response_id = r.id 
WHERE r.question = %1 
GROUP BY r.id, r.created_at, r.`comment`, r.`status`, e.`name`, r.amount
ORDER BY id desc ", $form['id']);
    while ($d = $data->fetch_assoc()) {
        switch ($d['status']) {
            case 'IN_PROCESS':
                $color = "#ADBDFA";
                $report =  "<b>В процессе</b>";
                break;
            case 'DONE':
                $color = "#AEECA0";
                $report =  "<b>Выполнена</b>";
                break;
            case 'CANCEL':
                $color = "#FAADAD";
                $report =  "<b>Отменена</b></small>";
                break;
            case '':
                $color = "#F9FAAD";
                $report = "Без отчета";
                break;
            default:
                $color = "";
                $report = "Неизвестно";
        }
        $cert_of_completion = "";
        if(getGlobalConfigVar('CERT_OF_COMPLETION') && getGlobalConfigVar('CERT_OF_COMPLETION')['enabled']) {
            if($d['cert_of_completion'] && $d['cert_subscribed']) {
                $cert_of_completion = "<td  style='background: {$color}'>
<a href='/abonents/cert_of_completion?id={$d['id']}'>Подписан, просмотреть</a>
</td>";
            } elseif ($d['cert_of_completion']) {
                $cert_of_completion = "<td  style='background: {$color}'> 
<a href='/abonents/cert_of_completion?id={$d['id']}' target='_blank'>Без подписи, подписать</a>
</td>";
            } else {
                $cert_of_completion = "<td  style='background: {$color}'><small>Без акта <br> выполненных работ</small></td>";
            }
        }


        $ht['old_comment'] .= "
          <tr>
            <td style='background: {$color}'>{$d['id']}</td>
            <td style='background: {$color}'>{$d['created_at']}</td>
            <td style='background: {$color}'>{$d['employee']}</td>
            <td style='background: {$color}'>{$statuses[$d['status']]}</td>
            <td style='background: {$color}'>{$d['comment']}</td>
            <td style='background: {$color}'>{$d['amount']}</td>
            $cert_of_completion
          </tr>
        ";
        if ($d['images']) {
            $ht['old_comment'] .= "<tr><td colspan='5'><small>Прикрепленные фото (кликните по нужной, что бы загрузить оригинал)</small><br>";
            foreach (explode(",", $d['images']) as $image) {
                $path = getGlobalConfigVar('PICTURES')['http_path']  . $image ;
                $ht['old_comment'] .= "<a href='$path' target='_blank'><img src='$path' style='height: 120px; margin: 3px; border: 1px solid black'/></a>";
            }
            $ht['old_comment'] .= "</td></tr>";
        }

    }
}

//Формирование списка status
foreach ($statuses as $status=>$humanizeStatus) {
    if(!$status) continue;
    $sel = $status == $form['status'] ? "SELECTED":"";
    $ht['status'] .= "<OPTION value='{$status}' {$sel} >{$humanizeStatus}</OPTION>";
}

if(getGlobalConfigVar('QUESTION_RULES') && getGlobalConfigVar('QUESTION_RULES')['enabled']) {
    $rules = new \envPHP\service\QuestionRules($form['id']);
    $result = $rules->validate();
$ht['process_comment'] = "";
switch ($result) {
    case \envPHP\service\QuestionRules::NOT_PROCESSED:
    case \envPHP\service\QuestionRules::DISABLED:
    case \envPHP\service\QuestionRules::DEACTIVATED_EARLY:
    case \envPHP\service\QuestionRules::ACTIVATED_EARLY:
        $ht['process_comment'] = "";
        break;
    case \envPHP\service\QuestionRules::MUST_BE_DEACTIVATED:
        $price = $rules->getPriceName();
        $ht['process_comment'] = "Услуга '{$price}' будет отключена автоматически при закрытии заявки.";
        break;
    case \envPHP\service\QuestionRules::MUST_BE_ACTIVATED:
        $price = $rules->getPriceName();
        $ht['process_comment'] = "Услуга '{$price}' будет автоматически активирована при закрытии заявки.";
        break;
    case \envPHP\service\QuestionRules::NOT_FOUND_ACTIVATIONS:
        $ht['process_comment'] = "Не удалось обнаружить подключенные услуги. Сначало установите прайс!";
    }
}
?>
<?=tpl('head', ['title'=>"Внесение отчета по заявке #{$form['id']}"])?>
        <div class="row">
            <div class="col-sm-4 col-xs-12 col-md-6 col-lg-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Информация по заявке</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12 col-lg-12">
                                Создатель: <b><?=$questionInfo['created_employee']?></b><br>
                                Создана: <b><?=$questionInfo['created']?></b><br>
                                На когда: <b><?=$questionInfo['dest_time']?></b><br>
                                Коментарий: <br><b><?=$questionInfo['comment']?></b><br><br>
                                Статус заявки:  <b><?=$statuses[$questionInfo['report_status']]?></b><br><br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12 col-md-6 col-lg-6">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Внесение отчета</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form  method="POST" class="form-horizontal form-label-left row" enctype="multipart/form-data">
                            <?=$ht['message']?>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Статус<span class="required">*</span>
                                </label>
                                <div class=" col-md-9 col-xs-12">
                                    <SELECT name="status" class="form-control">
                                        <?=$ht['status']?>
                                    </SELECT>
                                </div>
                            </div>
                            <div class="form-group" id="amount_default">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Сумма (грн)</span>
                                </label>
                                <div class=" col-md-9 col-xs-12">
                                    <INPUT name="amount" class="form-control" placeholder="1.99" value="<?=$form['amount']?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Коментарий
                                </label>
                                <div class=" col-md-9 col-xs-12">
                                <textarea class='form-control' name='comment' rows="3"><?=$form['comment']?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Фотографии
                                </label>
                                <div class=" col-md-9 col-xs-12">
                                    <input type="file" name="pictures[]" multiple id="pictures"  />
                                </div>
                            </div>
<?php $completion = getGlobalConfigVar('CERT_OF_COMPLETION'); if($completion && $completion['enabled']) { ?>
                            <div class="form-group" id="btn_cert_completion">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <button class="btn btn-default btn-primary" onclick="showCompletionFormBlock(); return false;">Заполнить акт выполненных работ</button>
                                </div>
                            </div>
                            <style>
                                #cert_of_completion .form-group .input-a {
                                    padding-bottom: 3px;
                                }
                            </style>
                            <div id="cert_of_completion" style="display: none">

                                <div class="ln_solid"></div>
                                <div id="cert_of_completion_list">
                                </div>
                                <div class="form-group">
                                    <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 ">
                                        <button class="btn btn-default" onclick=" fillCompletionsFromForm(); addCompletion(); reloadCompletions(); return false;">Добавить еще работу </button>
                                    </div>
                                </div>
                            </div>
                            <div class="ln_solid"></div>
<?php  } ?>
                            <div class="form-group">
                                <div class="col-md-12 col-sm-12 col-xs-12" style="margin: 5px; color: darkred; font-weight: bold">
                                    <?=$ht['process_comment']?>
                                </div>
                                <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                                    <button type="submit" class="btn btn-primary" name="action" value="save">Внести отчет</button>
                                    <a href='<?=$_SESSION['LAST_PAGE']?>' class='btn btn-primary'>Назад</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-xs-12 col-md-12 col-lg-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Ранее внесенные отчеты</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12 col-lg-12 ">
                                <div class="table-responsive-light">
                                    <table class="table table-bordered table-stripped" align='center' style='width: 100%; margin-top: 10px;'>
                                        <thead><tr>
                                            <th>ID</th>
                                            <th>Время</th>
                                            <th>Внес отчет</th>
                                            <th>Статус</th>
                                            <th>Коментарий</th>
                                            <th>Сумма</th>
<?php $completion = getGlobalConfigVar('CERT_OF_COMPLETION'); if($completion && $completion['enabled']) {  echo "<th>Акт выполненных работ</th>"; } ?>
                                        </tr></thead>
                                        <?=$ht['old_comment']?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
</div>
<script>
<?php $completion = getGlobalConfigVar('CERT_OF_COMPLETION'); if($completion && $completion['enabled']) { ?>
String.prototype.format = function() {
    var formatted = this;
    for (var i = 0; i < arguments.length; i++) {
        var regexp = new RegExp('\\{'+i+'\\}', 'gi');
        formatted = formatted.replace(regexp, arguments[i]);
    }
    return formatted;
};
var completions = <?= json_encode($form['completion']) ?>;


var completionTemplate = " <div class=\"form-group\" id='completion_row_{0}'>\n" +
"                                    <div class=\"col-lg-1 col-md-1 col-sm-1 col-xs-2 input-a\">\n" +
"                                        <button class='btn btn-danger btn-block' onclick='deleteCompletion({0}); reloadCompletions(); return false;'><span class='fa fa-trash'></span></button>\n" +
"                                    </div>\n" +
"                                    <div class=\"col-lg-7 col-md-5 col-sm-5 col-xs-10 input-a\">\n" +
"                                        <INPUT name=\"completion[{0}][name]\" id='completion_field_name_{0}' value='{1}' class=\"form-control\" placeholder=\"Тип работы\">\n" +
"                                    </div>\n" +
"                                    <div class=\"col-lg-2 col-md-3 col-sm-3 col-xs-6 input-a\">\n" +
"                                        <INPUT  pattern='^\\d+(\\.\\d{1,2})?$'   name=\"completion[{0}][count]\" id='completion_field_count_{0}'  value='{2}' class=\"form-control\" placeholder=\"Количество\">\n" +
"                                    </div>\n" +
"                                    <div class=\"col-lg-2 col-md-3 col-sm-3 col-xs-6 input-a\">\n" +
"                                        <INPUT pattern='^\\d+(\\.\\d{1,2})?$'  name=\"completion[{0}][price]\" id='completion_field_price_{0}'  value='{3}' class=\"form-control\" placeholder=\"Прайс\">\n" +
"                                    </div>\n" +
"                                </div>";

function reloadCompletions() {
    $('#cert_of_completion_list').html();
    var html = "";
    completions.forEach(function(item, i, arr) {
       html += completionTemplate.format(i, item.name, item.count, item.price)
    });
    $('#cert_of_completion_list').html(html);
}

function addCompletion() {
    console.log("Adding completion element");
    completions.push({name: "", count: '', price: ''})
}

function fillCompletionsFromForm() {
    console.log("Filling completion array from form");
    completions.forEach(function(item, i, arr) {
        console.log(item);
       completions[i].name = $('#completion_field_name_'+i).val() !== undefined ? $('#completion_field_name_'+i).val() : "";
       completions[i].count = $('#completion_field_count_'+i).val()  !== undefined ? $('#completion_field_count_'+i).val() : 0;
       completions[i].price = $('#completion_field_price_'+i).val()  !== undefined ? $('#completion_field_price_'+i).val() : 0.0;
    });
}
function showCompletionFormBlock() {
    $('#cert_of_completion').show();
    $('#btn_cert_completion').hide();
    $('#amount_default').hide();
}

function deleteCompletion(id) {
    console.log("Deleted completion element with id " + id);
    index = completions.indexOf(id);
    if (index > -1) {
        completions.splice(index, 1);
    }
    completions.splice(id, 1);
    console.log(completions);
}
window.addEventListener('load', function() {
    console.log("Length of completion array");
    console.log(completions.length);
    if(completions.length === 0) {
        addCompletion();
    } else {
        showCompletionFormBlock();
    }
    reloadCompletions();
});
<?php } ?>
</script>
<?=tpl('footer')?>