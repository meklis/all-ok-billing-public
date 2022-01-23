<?php
$rank = 21;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


if(!\envPHP\service\PSC::isPermitted('employees_notification')) {
    pageNotPermittedAction();
}

$table = '';
$count = '';

$form = [
'change'=>'',
'type'=>0
];

if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;



//Generate list radio of types 
$types = $sql->query("SELECT * FROM sms_types order by name");
$ht = '';
while($t = $types->fetch_assoc()) {
    $ch = '';
    if($form['type'] == 0 ) {
        $ch = 'checked';
        $form['type'] = $t['id'];
    } elseif ($form['type'] == $t['id']) {
        $ch = 'checked';
    }
    $ht .= "<input type='radio' name='type' onChange='form.submit()' $ch value='".$t['id']."'>".$t['name']."(".$t['type'].")<br>";
}
        
if(!isset($form['allow'])) $form['allow'] = array();
if(!isset($form['select'])) $form['select'] = array();
if($form['change']) {
    switch ($form['change']) {
        case 'add': 
            foreach ($form['allow'] as $v) $sql->query("INSERT INTO sms_send_list(eid, type) values(%1,%2)",$v,  $form['type']);
        break;
        case 'del':
            foreach ($form['select'] as $v) $sql->query("DELETE FROM sms_send_list WHERE eid = %1 and type = %2",  $v, $form['type']);
        break;
        case 'add_all':
            $data = $sql->query("SELECT id FROM employees WHERE  id not in (SELECT eid FROM sms_send_list WHERE type = %1)",  $form['type']);
            while($d = $data->fetch_assoc()) {
                 $sql->query("INSERT INTO sms_send_list(eid, type) values(%1,%2)",$d['id'] , $form['type']);
            }  
        break;
        case 'del_all':
            $sql->query("DELETE FROM sms_send_list WHERE type = %1", $form['type']);
        break;
    }
 }
$employees = $sql->query("SELECT e.id, e.name, p.position FROM employees e JOIN emplo_positions p on p.id = e.position  WHERE  e.id not in (SELECT eid FROM sms_send_list WHERE type = %1) order by name",  $form['type']);
$sended = $sql->query("SELECT e.id, e.name, p.position FROM employees e JOIN emplo_positions p on p.id = e.position WHERE e.id in (SELECT eid FROM sms_send_list WHERE type = %1)  order by name", $form['type']);
if($employees->num_rows != 0) {
    $option1 = '';
    while ($area = $employees->fetch_assoc()) {
    $option1 .= "<OPTION id='option' value = '".$area['id']."'>".$area['name']." - ".$area['position']."</OPTION>";
  }
}
if($sended->num_rows != 0) {
    $option2 = '';
    while ($area = $sended->fetch_assoc()) {
    $option2 .= "<OPTION id='option' value = '".$area['id']."'>".$area['name']." - ".$area['position']."</OPTION>";
  }
}



?>
<?=tpl('head', ['title'=>''])?>

<form action="" method="post">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Управление уведомлениями</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                        <h4>Тип уведомления</h4>
                        <div class="table-responsive-light" style="min-height: 400px">
                            <?=$ht?>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
                        <h4>Управление получателями</h4>
                        <div class="table-responsive-light">
                            <table style="width: 100%">
                                <tr>
                                    <td style="padding-top: 20px; width: 45%" >
                                        Список персонала без рассылки: <br>
                                        <select size="20" style='width: 100%' class='form-control' multiple name="allow[]" >
                                            <?=$option1?>
                                        </select>
                                    <td valign='center' align="center" width='10%' style='padding: 5px;'>
                                        <button type='submit' name='change' value="add" style='width: 35px; font-weight: bold'>></button><br><br>
                                        <button type='submit' name='change' value="del" style='width: 35px; font-weight: bold'><</button><br><br>
                                        <button type='submit' name='change' value="add_all" style='width: 35px; font-weight: bold'>>></button><br><br>
                                        <button type='submit' name='change' value="del_all" style='width: 35px; font-weight: bold'><<</button>
                                    <td style="padding-top: 20px; width: 45%">
                                        Включены в рассылку: <br>
                                        <select size="20" style='width: 100%' class='form-control' multiple  name="select[]">
                                            <?=$option2?>
                                        </select>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!--
        <div class='main'>
        <form action='' method="POST">
            <table>
                <tr>
                    <td style='padding-left: 10px; padding-right: 20px;' valign='top'>
                        <?=$ht?>
                    </td>
              
                    
                    <td style="padding-top: 20px">
                        Список персонала без рассылки: <br>
                        <select size="20" style='width: 350px' class='form-control' multiple name="allow[]" >
                         <?=$option1?>
                        </select>
                    <td valign='center' width='40' style='padding: 5px;'> 
                        <button type='submit' name='change' value="add" style='width: 35px; font-weight: bold'>></button><br><br>
                        <button type='submit' name='change' value="del" style='width: 35px; font-weight: bold'><</button><br><br>
                        <button type='submit' name='change' value="add_all" style='width: 35px; font-weight: bold'>>></button><br><br>
                        <button type='submit' name='change' value="del_all" style='width: 35px; font-weight: bold'><<</button>
                    <td style="padding-top: 20px">
                        
                        Включены в рассылку: <br>
                     <select size="20" style='width: 350px' class='form-control' multiple  name="select[]">
                         <?=$option2?>
                     </select>
            </table>
        </form>
  
       -->
<?=tpl('footer')?>
