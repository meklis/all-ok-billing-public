<?php
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();

if(!\envPHP\service\PSC::isPermitted('eq_show')) {
    pageNotPermittedAction();
}
$form = [
	'id'=>0,
	'ip'=>'',
	'model'=>1,
	'mac'=>'',
	'sn'=>'',
	'hardware'=>'',
	'firmware'=>'',
	'house'=>'',
	'access'=>1,
	'entrance'=>'',
	'description'=>'',
	'city'=>0,
	'street'=>0,
	'uplink_port'=>1,
	'group'=>1,
    'unbind_vlan' => [],
    'bind_vlan' => [],
    'action' => "",
];
$ht = [
'access'=>'',
'model'=>'',
'group'=>'',
'pinger_log'=>'',
'status'=>'',
'last_ping'=>'',
    'unbind_vlan'=>'',
    'bind_vlan'=>'',
];
if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;




//Выбор существующего оборудование
$sw = $sql->query("SELECT * FROM equipment WHERE id = {$form['id']} OR ip = '{$form['ip']}'")->fetch_assoc();
if($sw['id'] != '') {
		$addr = $sql->query("SELECT c.id cid, s.id sid, h.id hid 
											FROM addr_houses h 
											JOIN addr_streets s on s.id = h.street
											JOIN addr_cities c on c.id = s.city
											WHERE h.id = {$sw['house']}")->fetch_assoc();
	$form['id'] = $sw['id'];
	$form['ip'] = $sw['ip'];
	$form['model'] = $sw['model'];
	$form['mac'] = $sw['mac'];
	$form['house'] = $sw['house'];
	$form['street'] = $addr['sid'];
	$form['city'] = $addr['cid'];
	$form['access'] = $sw['access'];
	$form['entrance'] = $sw['entrance'];
	$form['description'] = $sw['description'];
	$form['uplink_port'] = $sw['uplink_port'];
	$form['group'] = $sw['group'];
	$ht['last_ping'] = $sw['last_ping'];
	if($sw['ping'] < 0 ) {
		$ht['status'] = "<b><font color='red'>ЛЕЖИТ</font></b>";
	} else {
		$ht['status'] = "<b><font color='green'>Работает</font></b>";
	}
}

//Блок с адресом
$html->getHouses($form['city'],$form['street'],$form['house'], $sql);
//Выборка ACCESS
$data = $sql->query("SELECT * FROM equipment_access order by 1");
while($d = $data->fetch_assoc()) {
	if($d['id'] == $form['access']) $sel = "SELECTED"; else $sel = "";
	$ht['access'] .= "<OPTION value='{$d['id']}' $sel>{$d['login']}</OPTION>";
}

//Выборка моделей
$data = $sql->query("SELECT * FROM equipment_models order by 2");
while($d = $data->fetch_assoc()) {
	if($d['id'] == $form['model']) $sel = "SELECTED"; else $sel = "";
	$ht['model'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

//Выборка групп
$data = $sql->query("SELECT * FROM equipment_group order by 2");
while($d = $data->fetch_assoc()) {
	if($d['id'] == $form['group']) $sel = "SELECTED"; else $sel = "";
	$ht['group'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}


//Выборка логов
$data = $sql->query("SELECT down, ifnull(up, 'Лежит сейчас') up, TIMEDIFF(up,down) dur FROM v_eq_ping_status WHERE equipment = {$form['id']} ORDER BY 1 DESC LIMIT 200 ");
while($d = $data->fetch_assoc()) {
    $ht['pinger_log'] .= "
            <tr>
                <td>{$d['down']}</td>
                <td>{$d['up']}</td>
                <td>{$d['dur']}</td>
            </tr>";
}
if(!$ht['pinger_log']) {
    $ht['pinger_log'] = "<h3 align='center'>Логов не найдено</h3>";
} else {
    $ht['pinger_log'] = "<table class='table table-sm table-bordered table-striped'>
            <tr>
              <th>Время падения</th>
              <th>Время поднятия</th>
              <th>Пролежал (ч)</th>
            </tr>
              " .
             $ht['pinger_log'] .
             "</table>";
}


//Выборка всех вланов НЕ закрепленных за железом
$data = $sql->query("SELECT v.id, v.vlan, k.`name`, k.description 
FROM eq_vlans v 
JOIN eq_kinds k on k.id = v.type
WHERE v.id not in (
	SELECT ev.vlan FROM equipment e JOIN eq_vlan_equipment ev on ev.equipment = e.id   WHERE e.id = {$form['id']}
)
ORDER  by vlan");
while ($d = $data->fetch_assoc()) {
    $ht['unbind_vlan'] .= "<OPTION value='{$d['id']}'>{$d['vlan']} - {$d['name']} - {$d['description']}</OPTION>";
}


//Выборка всех вланов  закрепленных за железом
$data = $sql->query("SELECT v.id, v.vlan, k.`name`, k.description 
FROM eq_vlans v 
JOIN eq_kinds k on k.id = v.type
WHERE v.id  in (
	SELECT ev.vlan FROM equipment e JOIN eq_vlan_equipment ev on ev.equipment = e.id     WHERE e.id = {$form['id']}
)
ORDER  by vlan");
$count_vlans = 0;
while ($d = $data->fetch_assoc()) {
    $ht['bind_vlan'] .= "<OPTION value='{$d['id']}'>{$d['vlan']} - {$d['name']} - {$d['description']}</OPTION>";
    $count_vlans++;
}
$count_bindings = $sql->query("SELECT count(*) c FROM eq_bindings WHERE id = '{$form['id']}'")->fetch_assoc()['c'];

?><?=tpl('head', ['title'=>"IP: {$form['ip']}"])?>

<div class="row">
<?php if($form['id']) { ?>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Состояние</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <h3>Статус: <b><?=$ht['status']?></b></h3>
                    Посл. изм. состояние: <b><?=$ht['last_ping']?></b><br><br>
                    Внесен в базу: <b><?=$sw['change']?></b><br>
                    Закреплено вланов: <b><?=$count_vlans?></b><br>
                    Закреплено привязок: <b><?=$count_bindings?></b><br>
                    <br>
                    <a href="<?=conf('BASE.wildcore')?>/info/device?ip=<?=$form['ip']?>"  class="btn btn-primary"><small>Опросить</small><br><span class='fa fa-table' style='   margin: 5px'></span></a>
                    <a href="/equipment/edit?id=<?=$form['id']?>"  class="btn btn-primary"><small>Изменить</small><br><span class='fa fa-edit' style='   margin: 5px; '></span></a>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Логи пингера</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div style=' height: 200px;   overflow-y: scroll;'>
                        <?=$ht['pinger_log']?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>О устройстве</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="form-horizontal form-label-left input_mask row" >
                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                            <span style="font-size: 16px; font-weight:  500">Основное</span>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">IP адрес</label>
                            <input readonly name='ip' value='<?=$form['ip']?>' placeholder='10.10.10.10' class='form-control'>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">MAC адрес</label>
                            <input readonly name='mac' value='<?=$form['mac']?>' placeholder='AA:BB:CC:DD:EE:FF' class='form-control'>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Модель</label>
                            <select readonly class='form-control' name='model'><?=$ht['model']?></select>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Группа</label>
                            <select readonly class='form-control' name='group'><?=$ht['group']?></select>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Доступы</label>
                            <select readonly class='form-control' name='access'><?=$ht['access']?></select>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Аплинк-порт</label>
                            <input readonly name='uplink_port' value='<?=$form['uplink_port']?>' placeholder='25' class='form-control'>
                        </div>
                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group"  style="margin: 0">
                            <div class="divider-dashed"></div>
                            <span style="font-size: 16px; font-weight:  500">Адрес</span>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                            <label class="control-label">Город</label>
                            <?=$html->listCities?>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                            <label class="control-label">Улица</label>
                            <?=$html->listStreets?>
                        </div>
                        <div class=" col-xs-12 col-sm-4 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Дом</label>
                            <?=$html->listHouses?>
                        </div>
                        <div class=" col-xs-6 col-sm-2 col-md-2 col-lg-1 form-group">
                            <label class="control-label">Подьезд</label>
                            <input readonly name='entrance' class="form-control"  value='<?=$form['entrance']?>' placeholder="1" pattern="[0-9]{1,3}">
                        </div>
                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group"  style="margin: 0">
                            <div class="divider-dashed"></div>
                            <span style="font-size: 16px; font-weight:  500">Дополнительно</span>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-4 col-lg-4 form-group">
                            <label class="control-label">Коментарий</label>
                            <textarea readonly name='description'  class="form-control" style="height: 80px;" ><?=$form['description']?></textarea>
                        </div>

                    </div>
                </div>
            </div>
        </div>
</div>



<?=tpl('footer')?>
