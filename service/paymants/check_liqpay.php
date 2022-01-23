<?php
ob_implicit_flush();
$rank = 5;
$urank = 8;

require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


if(!\envPHP\service\PSC::isPermitted('payment_liqpay')) {
    pageNotPermittedAction();
}


$sh ='';

$form = [
'agreement'=>'',
'date' => date('d.m.Y')
];
$message = '';


if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

?><?=tpl('head', ['title'=>''])?>
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Проверка платежей LiqPay</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form class="form-horizontal form-label-left input_mask row" method="GET"  >
                    <div class=" col-xs-6 col-sm-6 col-md-6 col-lg-2 form-group">
                        <label class="control-label">Номер договора</label>
                        <input name = 'agreement'   class="form-control" value="<?=$form['agreement']?>">
                    </div>
                    <div class=" col-xs-6  col-sm-6  col-md-6  col-lg-2 form-group">
                        <label class="control-label">Дата</label>
                        <?=$html->formDate('date', $form['date'])?>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-6 col-sm-6 col-md-6  col-lg-2 ">
                            <label class="control-label">	&nbsp;</label>
                            <button class="btn btn-primary btn-block" name='action'>Поиск</button>
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
                <h2>Результат проверки </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

<?php
 ob_flush();
if(isset($form['action'])) {
    foreach (getGlobalConfigVar('LIQPAY_ACCESS') as $provider=>$params) {
        $liqpay = new envPHP\libs\LiqPay($params['id'], $params['private_key']);
        if ($form['agreement'] != '') {
            $data = $sql->query("SELECT * FROM paymants_orders WHERE agreement = (SELECT id FROM clients WHERE agreement = %1) and cast(time as date) = STR_TO_DATE(%2,'%d.%m.%Y')", $form['agreement'], $form['date']);
        } else {
            $data = $sql->query("SELECT * FROM paymants_orders WHERE cast(time as date) = STR_TO_DATE(%1,'%d.%m.%Y')", $form['date']);
        }
        echo "Found {$data->num_rows} pays for request<br>";
        while ($d = $data->fetch_assoc()) {

            $res = (array)$liqpay->api("payment/status", array(
                'version' => '3',
                'order_id' => $d['order_id']));

            echo "Order_ID: " . $d['order_id'];
            if ($sql->query("SELECT * FROM paymants WHERE payment_id = %1", $d['order_id'])->num_rows != 0) $pay = true; else $pay = false;

            if ($res['status'] == 'error' && $res['err_code'] == 'payment_not_found') {
                $sql->query("DELETE FROM paymants_orders WHERE id = %1", $d['id']);
                echo " - <span style='color: gray'>Не найден в LiqPay (Не был произведен) yдален с оплат</span>";
            } else {
                if ($res['status'] == 'success' && $pay) {
                    echo " - <span style='color: green'>Платеж успешно оплачен, зачислен автоматически</span>";
                }
                if ($res['status'] == 'success' && !$pay) {
                    $sql->query("INSERT INTO paymants(money, agreement, comment, payment_type, payment_id) VALUES ('" . $res['amount'] . "', '" . $d['agreement'] . "', 'Оплата через LiqPay', 'LiqPay', '" . $d['order_id'] . "')");
                    echo " - <span style='color: green; font-weight: bold'>Платеж успешно оплачен, в базу внесен только что</span>";
                }
                if ($res['status'] !== 'success' && $pay) {
                    $sql->query("DELETE FROM paymants WHERE payment_id = %1", $d['order_id']);
                    echo " - <span style='color: red; font-weight: bold'>Платеж был отменен, но был ранее зачислен абоненту, удален</span>";
                }
                if ($res['status'] !== 'success' && !$pay) {
                    if (isset($res['err_description'])) $error = $res['err_description']; else $error = 'Неизвестно';
                    echo " - <span style='color: grey'>Платеж не проведен, статус: {$res['status']}, описание: $error </span>";

                }
            }
            echo "<br>";
            ob_flush();
        }
    }
}
 
 
 ?>

            </div>
        </div>
    </div>
</div>


<?=tpl('footer')?>


