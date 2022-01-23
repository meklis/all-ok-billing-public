<?php
$rank = 5;
$urank = 8;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


if(!\envPHP\service\PSC::isPermitted('payment_create')) {
    pageNotPermittedAction();
}


$message = '';
$form = [
    'agreement'=>'',
    'pay'=>'',
    'comment'=>'',
    'type' => '',
];
$ht = [
        'pay_type' => '',
];

envPHP\classes\std::Request($form);

foreach (getGlobalConfigVar('CUSTOM_PAYMENT')['types'] as $param) {
    $sel = $form['type'] == $param ? "SELECTED" : "";
    $ht['pay_type'] .= "<OPTION value='{$param}' {$sel}>{$param}</OPTION>";
}

if(isset($form['action'])) {
        $form['pay'] = str_replace(",", ".", $form['pay']);
        $agree = $sql->query("SELECT id FROM clients WHERE agreement = %1 and house in (SELECT h.id from addr_houses h JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups())."))", $form['agreement']);
        if($agree->num_rows != 0) {
            $agreement = $agree->fetch_assoc()['id'];
            try {
                $pay_id = envPHP\service\payment::add($agreement, $form['pay'],$form['type'], "", $form['comment'],'user: '. _uid );
                $html->addNoty('success', "Платеж успешно добавлен, ID: ".$pay_id."");
            } catch (Exception $e) {
                $html->addNoty('error', "Ошибка добавления платежа: ".$e->getMessage()."");
                $message = "<div id='message_fail'>Ошибка добавления платежа: ".$e->getMessage()."</div>";
            }
        } else  {
            $html->addNoty('warning', "Указанный номер договора не найден");
        }
  
 }

$last_qu = $sql->query("SELECT p.time, s.id, s.agreement, p.money, p.comment, payment_type 
FROM paymants p 
    JOIN clients s on s.id = p.agreement and house in (
        SELECT h.id from addr_houses h 
        JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")) order by time desc LIMIT 30");
$last = "<table class='table table-striped' id='myT'>
            <thead>
                <tr>
                    <th>Время</th>
                    <th>Номер договора</th>
                    <th>Сумма</th>
                    <th>Источник</th>
                    <th>Коментарий</th>
                </tr>
            </thead>
            <tbody>
            ";
while ($d = $last_qu->fetch_assoc()) {
    $last .= "<tr>
            <td>".$d['time']."</td>
            <td><a href='/abonents/detail?id=".$d['id']."'>".$d['agreement']."</a></td>
            <td>".$d['money']."<td>{$d['payment_type']}</td>
            <td>".$d['comment'] . "</td>
            </tr>";
}
$last .="</tbody>
</table>";
?><?=tpl('head', ['title'=>''])?>
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Внесение платежа</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form class="form-horizontal form-label-left input_mask row" method="POST"  >
                    <div class=" col-xs-6 col-sm-6 col-md-6 col-lg-2 form-group">
                        <label class="control-label">Номер договора</label>
                        <input name = 'agreement'class="form-control" value="<?=$form['agreement']?>" required>
                    </div>
                    <div class=" col-xs-6  col-sm-6  col-md-6  col-lg-2 form-group">
                        <label class="control-label">Сумма платежа</label>
                        <input name = 'pay'  class="form-control" value="<?=$form['pay']?>" required placeholder="0.0">
                    </div>
                    <div class=" col-xs-6  col-sm-6  col-md-6  col-lg-3 form-group">
                        <label class="control-label">Тип платежа</label>
                        <select name = 'type'  class="form-control">
                            <?=$ht['pay_type']?>
                        </select>
                    </div>
                    <div class=" col-xs-6  col-sm-6  col-md-6  col-lg-5 form-group">
                        <label class="control-label">Коментарий</label>
                        <textarea style='height: 80px;' name = 'comment' class="form-control"><?=$form['comment']?></textarea>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12 col-sm-12 col-md-12  col-lg-3 ">
                            <label class="control-label">	&nbsp;</label>
                            <button class="btn btn-primary btn-block" name='action'>Внести платеж</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Последние платежи</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive-light">
                <?=$last?>
                </div>
            </div>
        </div>
    </div>
</div>



<?=tpl('footer')?>
