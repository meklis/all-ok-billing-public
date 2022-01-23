<?php
$rank = 5;

$message = '';
$actions = '';
$table = "";  
$paid_day = array(-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15, "Больше 16", "Не важно");
$h_paid = '';
$price_list = '';
$no_price_arr = array('Не показывать','Показывать');
$selected = array(0=>'Уведомить через СМС',1=>'Уведомить через Email');
 
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();
 
 $form = [
 'search'=>'',
 'paid_to'=>0,
 'price'=>0,
 'no_price'=>0,
 'act_checked'=>0,
 'sms'=>'',
 'mail'=>''
 ];
if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;


foreach ($paid_day as $v) {
    if($form['paid_to'] == $v) $sel = "SELECTED"; else $sel = '';
    $h_paid .= "<OPTION value='$v' $sel>$v</OPTION>";
}
$price_list .= "<OPTION value='0'>Не важно</OPTION>";
$prices = $sql->query("SELECT * FROM bill_prices ORDER BY 2");
while($price = $prices->fetch_assoc()) {
     if($form['price'] == $price['id']) $sel = "SELECTED"; else $sel = '';
    $price_list .= "<OPTION value='".$price['id']."' $sel>".$price['name']." - ".$price['price_day']."</OPTION>";
}

foreach ($no_price_arr as $k=>$v){
    if($form['no_price'] == $k) $sel = "SELECTED"; else $sel = '';
    $no_price .= "<OPTION value='$k' $sel>$v</OPTION>";
}

$s_where = '';
$s_having = '';
if(isset($form['action']) ) {
  $s_having = " day <= ".$form['paid_to'];
  if($form['paid_to'] == "Больше 16") $s_having = " day >= 16 " ;
  if($form['paid_to'] == "Не важно") $s_having = " c.agreement is not null" ;
  if($form['no_price'] == 1) $s_having .= " OR price is null";
  if($form['no_price'] == 0) $s_where .= " AND (time_stop is  null and time_start is not null)";
  if($form['price'] != 0) $s_where .= " and p.id = ".$form['price'];
    $data = $sql->query("
       SELECT 
c.id
, c.agreement
, c.`name`
, c.apartment
, ph.phone
, em.email
, c.balance
, round(c.balance / sum(p.price_day)) day 
, CURDATE() + INTERVAL round(c.balance / sum(p.price_day)) DAY paid_to
, sum(p.price_day) price
, group_concat(distinct concat('<br>',p.name)) prices_name
, CONCAT('г.',city.name,', ', s.name, ', д.', h.name, ', кв.', apartment) addr
, (SELECT max(time) FROM paymants WHERE agreement = c.id) last_pay
FROM clients c 
JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
JOIN addr_streets s on s.id = h.street
JOIN addr_cities city on city.id = s.city
LEFT JOIN client_prices pr on pr.agreement = c.id
LEFT JOIN bill_prices p on p.id = pr.price
LEFT JOIN (SELECT agreement_id, `value` phone FROM client_contacts WHERE main = 1 and type = 'PHONE') ph on ph.agreement_id = c.id 
LEFT JOIN (SELECT agreement_id, `value` email FROM client_contacts WHERE main = 1 and type = 'EMAIL') em on em.agreement_id = c.id 
WHERE c.id !=0  $s_where 
GROUP BY c.id
HAVING $s_having
order by prices_name, addr
");
  if($data->num_rows == 0) $table = "<h4 align='center'>По запросу ничего не найдено</h4>"; else {
      $table = "<table class='table table-striped' id='myT'>
            <thead>
            <th>Договор</th>
            <th>Имя</th>
            <th>Адрес</th>
            <th>Телефон</th>
            <th>Почта</th>
            <th>Оплачено до</th>
            <th>Дней до оплаты</th>
            <th>Посл. платеж</th>
            <th>Баланс</th>
            <th>активные прайсы</th>
            <th>Кредитует в день</th>
            <th><input id=\"ch\" type=\"checkbox\" name=\"one\" value=\"all\" onclick=\"checkAll('search')\"></th></thead><tbody>";
      while($d = $data->fetch_assoc()) {
          $href = "<a href = '/abonents/detail?id=".$d['id']."'>".$d['agreement']."</a>";
          if(isset($form['send'][$d['id']])) $sel = 'CHECKED'; else $sel = '';
        $send = "<INPUT type='checkbox' name='send[".$d['id']."]' $sel>";
         $table .= "<tr>
               <td>$href</td>
               <td>".$d['name']."</td>
               <td>".$d['addr']."</td>
               <td>".$d['phone']."</td>
               <td>".$d['email']."</td>
               <td>".$d['paid_to']."</td>
               <td>".$d['day']."</td>
               <td>".$d['last_pay']."</td>
               <td>".$d['balance']."</td>
               <td>".trim($d['prices_name'],"<br>")."</td>
               <td>".$d['price']."</td>
               <td>$send</td>";
      }
      $table .="</tbody></table>";
  }
  $act = '';
foreach ($selected as $k=>$v) {
    if($k == $form['act_checked']) $sel = "SELECTED"; else $sel = '';
    $act .= "<OPTION value='$k' $sel>$v</OPTION>";

}
 
}

//Отправка уведомлений
if(isset($form['action']) && $form['action'] == 'go') {
 if(isset($form['send'])) {
     $count = 0;
	 if(trim($form['sms']) == '' && $form['act_checked'] == 0) {
         html()->addNoty('info', "Вы не указали сообщения для отправки");
		 goto html;
	 }
	 if(trim($form['mail']) == '' && $form['act_checked'] == 1) {
         html()->addNoty('info', "Вы не указали сообщения для отправки");
		 goto html;
	 }
      foreach ($form['send'] as $k=>$v) {
     if($form['act_checked'] == 0) {
           $abon = $sql->query("SELECT value phone FROM clients c JOIN client_contacts cc on c.id = cc.agreement_id and main=1 and type='PHONE' WHERE c.id = %1", $k)->fetch_assoc();
			envPHP\service\shedule::add(envPHP\service\shedule::SOURCE_NOTIFICATION_GENERATOR, "notification/sendSMS",['phone'=>$abon['phone'],'message'=>$form['sms']]);
       }
       if($form['act_checked'] == 1) {
           $abon = $sql->query("SELECT * FROM clients WHERE id = %1", $k)->fetch_assoc();
         // $test = $mail->sendMessage($abon['email'], 'Залишок на рахунку', generMail($abon));
            $sql->query("INSERT INTO mail_outgoing (mail, message_text, message_status, type) VALUES ('".$abon['email']."', '".addslashes($form['mail'])."', '$test', 'Ручная рассылка')");

       }
       $count++;
     }
     html()->addNoty('success', "Успешно отправлено $count уведомлений!");
 } else {
     html()->addNoty('info', "Укажите хотя бы одного абонента");
 }
}
html: 
?><?=tpl('head', ['title'=>''])?>
    <form action="" method="post" name="search">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Массовая рассылка</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="form-horizontal form-label-left input_mask row" >
                            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                                <span style="font-size: 16px; font-weight:  500">Параметры поиска</span>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                                <label class="control-label">Дней до оплаты</label>
                                <SELECT name="paid_to" class="form-control">
                                    <?=$h_paid?>
                                </select>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                                <label class="control-label">Прайс</label>
                                <SELECT name="price" class="form-control">
                                    <?=$price_list?>
                                </select>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                                <label class="control-label">Без прайсов(Отключенные)</label>
                                <SELECT name="no_price" class="form-control">
                                    <?=$no_price?>
                                </select>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                                <label class="control-label">&nbsp </label>
                                <button type="submit" name="action" value="paid_to" class="btn btn-primary btn-block">Найти</button>
                            </div>
                            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                                <div class="divider-dashed"></div>
                                <span style="font-size: 16px; font-weight:  500">Параметры сообщений</span>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                                <label class="control-label">Текст СМС</label>
                                <textarea name='sms' class='form-control' style='  height: 100px;	'><?=$form['sms']?></textarea>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                                <label class="control-label">Текст Email</label>
                                <textarea name='mail' class='form-control' style='  height: 150px;	'><?=$form['mail']?></textarea>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                                <label class="control-label">С отмеченными</label>
                                <select name='act_checked' class='form-control'><?=$act?></select>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                                <label class="control-label"> &nbsp</label>
                                <button type='submit' name='action' value='go' class='btn btn-primary btn-block'>Выполнить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?=$table?>
            </div>
        </div>
    </form>
<script>
    var statusCheckbox = false;
    function checkAll(formname)
    {
        var checkboxes = new Array();
        checkboxes = document[formname].getElementsByTagName('input');
        statusCheckbox = !statusCheckbox;
        for (var i=0; i<checkboxes.length; i++)  {
            if (checkboxes[i].type == 'checkbox')   {
                checkboxes[i].checked = statusCheckbox;
            }
        }
    }
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
            "scrollX": true,
            "order": [[ 3, 'asc' ]],

            "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "Все"]]
        });
    });
</script>
<?=tpl('footer')?>