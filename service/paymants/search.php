<?php
$rank = 5;
$urank = 8;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();
$sh ='';


if(!\envPHP\service\PSC::isPermitted('payment_search')) {
    pageNotPermittedAction();
}


$message = '';
$form = [
    'agreement'=>'',
    'comment'=>'',
    'type' => '',
    'start'=>date("d.m.Y"),
    'stop'=>date("d.m.Y"),
    'pay_id' => [],
];
$ht = [
        'form_pay_type' => '<OPTION value="">Не важно</OPTION>',
];
envPHP\classes\std::Request($form);

if($form['pay_id']) {
    $ids = join(',', $form['pay_id']);
    $data = $sql->query("SELECT 
            p.money, 
            cp.purpose_of_payment,
            a.agreement,
            a.`name`,
            p.time,
            pr.`name` provider ,
            p.id
            FROM paymants p
            JOIN clients a on a.id = p.agreement
            JOIN addr adr on adr.id = a.house and adr.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
            LEFT JOIN providers pr on pr.id = a.provider
            LEFT JOIN (
              SELECT client_id, purpose_of_payment
              FROM bill_prices bp
              JOIN client_prices cp on cp.price = bp.id 
              JOIN (
                    SELECT 
                    c.id client_id, 
                    max(p.id) price_id
                    FROM clients c 
                    JOIN client_prices p on p.agreement = c.id 
                    GROUP BY c.id 
                ) pp on pp.price_id = cp.id 
            ) cp on cp.client_id = a.id 
        WHERE p.id in ($ids) ");
    $payments = [];
    while ($d = $data->fetch_assoc()) {
        $payments[] = [
          'Квитанція' => $d['id'],
          'Договор' => $d['agreement'],
          'ФІП' => $d['name'],
          'Призначення' => $d['purpose_of_payment'],
          'Провайдер' => $d['provider'],
          'Дата' => $d['time'],
          'Отримано' => $d['money'],
        ];
    }
    $pdf = new envPHP\pdf\PdfPrinter(new \Mpdf\Mpdf([
        'tempDir' => '/tmp/php/pdf',
        'format' => [72.1, 114 ],
        'margin_top' => 0,
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_bottom' => 0,
    ]));
    $properties = getGlobalConfigVar('PDF_PRINTING')['receipt'];
    $pdf->setTemplate(file_get_contents($properties['path']))
        ->setVariables($properties['params'])
        ->write($payments)
        ->outputHTML();
    exit;
}
if(isset($form['del'])) {
    $test = @json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/payment/delete", ['payment'=>$form['del'], 'employee'=> _uid ]));
    if(!$test) {
        $html->addNoty('error', "Ошибка удаления платежа (причина неизвестна)");
    } elseif($test->code != 0) {
        $html->addNoty('error', "Ошибка удаления платежа: ".$test->errorMessage."");
    } else {
        $html->addNoty('error', "Платеж успешно удален");
    }
}

if(isset($form['action'])) {
    $qus =  implode("%", explode(' ', $form['comment']));
	$agree = trim($form['agreement']);
    $qus = "%".$qus."%";
	if($agree != '') $query = "and s.agreement = '$agree'"; else $query = '';
	if($form['type']) $query .= " and payment_type = '{$form['type']}' ";
    $search = $sql->query("
SELECT p.time, s.id, p.id pid, p.payment_id, s.agreement, p.money, p.comment, p.debug_info debug, payment_type type, em.name added_emplo
FROM paymants p 
JOIN clients s on s.id = p.agreement 
JOIN addr adr on s.house = adr.id and adr.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
LEFT JOIN (
	SELECT id,
	CASE 
		WHEN POSITION('user' IN debug_info) != 0 THEN CAST(SUBSTR(debug_info,6,3) as UNSIGNED) ELSE '' 
	END r 
	FROM paymants 
) pid on pid.id = p.id 
LEFT JOIN employees em on em.id = pid.r 
WHERE ((cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y'))) and CONCAT(s.agreement, p.comment,p.money) like '$qus' $query order by time desc");
    if($search->num_rows != 0) {
        $sh = "<table class='table table-striped' id='myT'>
                <thead>
                   <tr>
                      <th>ID</th>
                      <th>Время платежа</th>
                      <th>Номер договора</th>
                      <th>Сумма</th>
                      <th>Источник платежа</th>
                      <th>Коментарий</th>
                      <th>Локальный ID</th>
                      <th>Удалить</th>
                      <th>Печать</th>
                      </tr>
                      </thead><tbody>";
        while($d = $search->fetch_assoc()) {
        $sh .= "<tr>
                     <td>{$d['id']}</td>
                     <td>{$d['time']}</td>
                     <td><a href='/abonents/detail?id={$d['id']}'>{$d['agreement']}</a></td>
                     <td>{$d['money']}</td>
                     <td>{$d['type']}". ($d['added_emplo'] ? " - {$d['added_emplo']}" : '') ."</td>
                     <td>{$d['comment']}</td>
                     <td>{$d['payment_id']}</td>
                     <td><a href='?del={$d['pid']}&agreement={$form['agreement']}&comment={$form['comment']}&action' onclick=\"if(confirm('Уверен?')) return true; return false;\">Удалить</a></td>
                     <td><input type='checkbox' name='pay_id[]' value='{$d['pid']}' " . (in_array($d['pid'], $form['pay_id']) ? 'checked' : '' ). "></td>
                 </tr>     
                     ";
        } 
        $sh .="</tbody></table>";
    } else {
        $html->addNoty('info', "Платежей не найдено");
        $sh = "<br><br><h3 align='center'>По Вашему запросу платежей не найдено</h3>";
    }
}

//Получение типа платежей
$data = $sql->query("SELECT DISTINCT payment_type type FROM paymants ORDER BY 1 ");
while ($d = $data->fetch_assoc()) {
    $sel = $d['type'] == $form['type'] ? "SELECTED ": "";
    $ht['form_pay_type'] .= "<OPTION value='{$d['type']}' {$sel}>{$d['type']}</OPTION>";
}

?><?=tpl('head', ['title'=>''])?>
<form method="POST">
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Поиск платежей</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-horizontal form-label-left input_mask row" >
                    <div class=" col-xs-12 col-sm-3 col-md-3 col-lg-2 form-group">
                        <label class="control-label">Номер договора</label>
                        <input name = 'agreement'    class="form-control" value="<?=$form['agreement']?>">
                    </div>
                    <div class=" col-xs-6 col-sm-3 col-md-3 col-lg-2 form-group">
                        <label class="control-label">Дата ОТ</label>
                        <input type='text' name='start' class='form-control' value="<?=$form['start']?>" id="start_date" >
                    </div>
                    <div class=" col-xs-6  col-sm-3  col-md-3  col-lg-2 form-group">
                        <label class="control-label">Дата ДО</label>
                        <input type='text' name='stop' class='form-control' value="<?=$form['stop']?>"  id="stop_date">
                    </div>
                    <div class=" col-xs-6  col-sm-3  col-md-3  col-lg-2 form-group">
                        <label class="control-label">Источник платежа</label>
                        <SELECT name = 'type' class="form-control" >
                            <?=$ht['form_pay_type']?>
                        </SELECT>
                    </div>
                    <div class=" col-xs-6  col-sm-3  col-md-3  col-lg-2 form-group">
                        <label class="control-label">Коментарий</label>
                        <input name = 'comment'  class="form-control" value="<?=$form['comment']?>">
                    </div>
                    <div class=" col-xs-6  col-sm-3  col-md-3  col-lg-2 form-group">
                        <label class="control-label">&nbsp; </label>
                        <button class="btn btn-primary btn-block" name='action' value="search">Поиск</button>
                    </div>
                    <?php if(isset($form['action'])) { ?>
                    <div class=" col-xs-6  col-sm-3  col-md-3  col-lg-3 form-group">
                        <label class="control-label">&nbsp; </label>
                        <button class="btn btn-primary btn-block" name='action' target="_blank" value="print">Печать</button>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12  col-sm-12 col-md-12 col-lg-12">
        <?=$sh?>
    </div>
</div>
</form>
<script>

    $(document).ready(function() {
        $('#myT').DataTable( {
            "language": {
                "lengthMenu": "Отображено _MENU_ записей на странице",
                "zeroRecords": "К сожалению, записей не найдено",
                "info": "Показана  страница _PAGE_ с _PAGES_",
                "infoEmpty": "Нет записей",
                "infoFiltered": "(filtered from _MAX_ total records)",
                "search": "Живой фильтр:",
                "paginate": {
                    "first":      "Первая",
                    "last":       "Последняя",
                    "next":       "Следующая",
                    "previous":   "Предыдущая"
                },
            },
            "order": [[ 1, 'desc' ]],
            "scrollX": true,
            "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "Все"]]
        });
    });
</script>

<script>
    $(function () {
        $('#start_date').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?=$form['start']?>'});
        $('#stop_date').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?=$form['stop']?>'});
    });
</script>
<?=tpl('footer')?>
