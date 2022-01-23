<?php
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();


$form = [
    'id' => 0,
    'ip' => '',
    'model' => 1,
    'mac' => '',
    'sn' => '',
    'hardware' => '',
    'firmware' => '',
    'house' => 0,
    'access' => 1,
    'entrance' => '',
    'description' => '',
    'city' => 0,
    'street' => 0,
    'uplink_port' => 1,
    'group' => 1,
    'unbind_vlan' => [],
    'bind_vlan' => [],
    'action' => "",
];
$ht = [
    'access' => '',
    'model' => '',
    'group' => '',
    'pinger_log' => '',
    'status' => '',
    'last_ping' => '',
    'unbind_vlan' => '',
    'bind_vlan' => '',
];
if (isset($_REQUEST)) foreach ($_REQUEST as $k => $v) $form[$k] = $v;

if (@$form['action'] == 'add_one_vlan') {

    if(!\envPHP\service\PSC::isPermitted('eq_change_vlan')) {
        $html->addNoty('error', 'Недостаточно прав для изменения вланов');
        goto AFTER_ACTION;
    }
    foreach ($form['unbind_vlan'] as $val) {
        $test = $sql->query("INSERT INTO eq_vlan_equipment (vlan, equipment) VALUES ($val, {$form['id']})");
    }
}
if (@$form['action'] == 'del_one_vlan') {

    if(!\envPHP\service\PSC::isPermitted('eq_change_vlan')) {
        $html->addNoty('error', 'Недостаточно прав для изменения вланов');
        goto AFTER_ACTION;
    }
    foreach ($form['bind_vlan'] as $val) {
        $test = $sql->query("DELETE FROM eq_vlan_equipment WHERE equipment =  {$form['id']} and vlan = $val");

    }
}
if (@$form['action'] == 'add_all_vlan') {

    if(!\envPHP\service\PSC::isPermitted('eq_change_vlan')) {
        $html->addNoty('error', 'Недостаточно прав для изменения вланов');
        goto AFTER_ACTION;
    }
    $sql->query("INSERT INTO eq_vlan_equipment (equipment, vlan)
SELECT {$form['id']}, id FROM eq_vlans");
}
if (@$form['action'] == 'del_all_vlan') {

    if(!\envPHP\service\PSC::isPermitted('eq_change_vlan')) {
        $html->addNoty('error', 'Недостаточно прав для изменения вланов');
        goto AFTER_ACTION;
    }
    $sql->query("DELETE FROM eq_vlan_equipment WHERE equipment = '{$form['id']}'");
}

if (@$form['action'] == 'delete') {
    if (!\envPHP\service\PSC::isPermitted('eq_delete')) {
        $html->addNoty('error', "Недостаточно прав для удаления");
        goto AFTER_ACTION;
    } elseif ($sql->query("SELECT count(*) c FROM eq_bindings WHERE switch = '{$form['id']}'")->fetch_assoc()['c'] > 0) {
        $html->addNoty('error', "За данным оборудованием закреплены привязки, перенесите привязки перед удалением.");
    } else {
        $sql->query("DELETE FROM equipment WHERE id = '{$form['id']}'");
        header("Location: {$_SESSION['LAST_PAGE']}");
        exit;
    }
}

//Действия с записями
if (@$form['action'] == 'save') {
    //Проверим наличие записи в базе
    $id = $sql->query("SELECT id FROM equipment WHERE id = '{$form['id']}'")->fetch_assoc()['id'];
    if ($id == '') {
        if (!\envPHP\service\PSC::isPermitted('eq_create')) {
            $html->addNoty('error', 'Недостаточно прав для создания железки');
            goto AFTER_ACTION;
        }

        //Если новый хост
        $query = "INSERT INTO equipment (ip, model,mac,house,access,entrance,description,uplink_port,`group`) 
		VALUES 
		(
		'{$form['ip']}'
		,'{$form['model']}'
		,'{$form['mac']}'
		,'{$form['house']}'
		,'{$form['access']}'
		,'{$form['entrance']}'
		,'{$form['description']}'
		,'{$form['uplink_port']}'
		,'{$form['group']}'
		);
		";

        $test = $sql->query($query);
        if (!$test) {
            $html->addNoty('error', "Ошибка записи в базу: {$sql->error}");
        } else {
            $id = $sql->query("SELECT id FROM equipment WHERE ip = '{$form['ip']}'")->fetch_assoc()['id'];
            $form['id'] = $id;
            $html->addNoty('success', "Новый хост успешно добавлен. ID - $id");
        }
    } else {

        if (!\envPHP\service\PSC::isPermitted('eq_edit')) {
            $html->addNoty('error', 'Недостаточно прав для изменении');
            goto AFTER_ACTION;
        }
        //Обновление существующего
        $query = "UPDATE equipment SET ip = '{$form['ip']}', model = '{$form['model']}', mac = '{$form['mac']}', house = '{$form['house']}', access = '{$form['access']}', entrance ='{$form['entrance']}', description = '{$form['description']}', uplink_port = '{$form['uplink_port']}', `group` = '{$form['group']}' WHERE id= {$form['id']} ;";

        $test = $sql->query($query);
        if (!$test) {
            $html->addNoty('error', "Ошибка записи в базу: {$sql->error}");
        } else {
            $id = $sql->query("SELECT id FROM equipment WHERE ip = '{$form['ip']}'")->fetch_assoc()['id'];
            $form['id'] = $id;
            $html->addNoty('success', "Хост успешно изменен. ID - $id");
        }
    }
}
AFTER_ACTION:


//Выбор существующего оборудование   
$sw = $sql->query("SELECT * FROM equipment WHERE id = {$form['id']} OR ip = '{$form['ip']}'")->fetch_assoc();
$addr = [
    'city_id' => 0,
    'street_id' => 0,
    'house_id' => 0,
];
if ($sw && $sw['id'] != '') {
    $addr = $sql->query("SELECT c.id city_id, s.id street_id, h.id house_id 
											FROM addr_houses h 
											JOIN addr_streets s on s.id = h.street
											JOIN addr_cities c on c.id = s.city
											WHERE h.id = {$sw['house']}")->fetch_assoc();
    if(!$form['city'] && !$form['street'] && !$form['house']) {
        $form['city'] = $addr['city_id'];
        $form['street'] = $addr['street_id'];
        $form['house'] = $addr['house_id'];
    }
    $form['id'] = $sw['id'];
    $form['ip'] = $sw['ip'];
    $form['model'] = $sw['model'];
    $form['mac'] = $sw['mac'];
    $form['access'] = $sw['access'];
    $form['entrance'] = $sw['entrance'];
    $form['description'] = $sw['description'];
    $form['uplink_port'] = $sw['uplink_port'];
    $form['group'] = $sw['group'];
    $ht['last_ping'] = $sw['last_ping'];
    if ($sw['ping'] < 0) {
        $ht['status'] = "<b><font color='red'>ЛЕЖИТ</font></b>";
    } else {
        $ht['status'] = "<b><font color='green'>Работает</font></b>";
    }
}

//Блок с адресом 
html()->getHouses($form['city'], $form['street'], $form['house'], $sql);
//Выборка ACCESS 
$data = $sql->query("SELECT * FROM equipment_access order by 1");
while ($d = $data->fetch_assoc()) {
    if ($d['id'] == $form['access']) $sel = "SELECTED"; else $sel = "";
    $ht['access'] .= "<OPTION value='{$d['id']}' $sel>{$d['login']}</OPTION>";
}

//Выборка моделей 
$data = $sql->query("SELECT * FROM equipment_models order by 2");
while ($d = $data->fetch_assoc()) {
    if ($d['id'] == $form['model']) $sel = "SELECTED"; else $sel = "";
    $ht['model'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

//Выборка групп 
$data = $sql->query("SELECT * FROM equipment_group order by 2");
while ($d = $data->fetch_assoc()) {
    if ($d['id'] == $form['group']) $sel = "SELECTED"; else $sel = "";
    $ht['group'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}


//Выборка логов 
$data = $sql->query("SELECT down, ifnull(up, 'Лежит сейчас') up, TIMEDIFF(up,down) dur FROM v_eq_ping_status WHERE equipment = {$form['id']} ORDER BY 1 DESC LIMIT 200 ");
while ($d = $data->fetch_assoc()) {
    $ht['pinger_log'] .= "
            <tr>
                <td>{$d['down']}</td>
                <td>{$d['up']}</td>
                <td>{$d['dur']}</td>
            </tr>";
}
if (!$ht['pinger_log']) {
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

?><?= tpl('head', ['title' => "Изменение хоста {$form['ip']}"]) ?>

    <div class="row">
        <form name="form" method="POST">
            <?php if ($form['id']) { ?>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Состояние</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <h3>Статус: <b><?= $ht['status'] ?></b></h3>
                            Посл. изм. состояние: <b><?= $ht['last_ping'] ?></b><br><br>
                            Внесен в базу: <b><?= $sw['change'] ?></b><br>
                            Закреплено вланов: <b><?= $count_vlans ?></b><br>
                            Закреплено привязок: <b><?= $count_bindings ?></b><br>
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
                                <?= $ht['pinger_log'] ?>
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
                        <div class="form-horizontal form-label-left input_mask row">
                            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                                <span style="font-size: 16px; font-weight:  500">Основное</span>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                                <label class="control-label">IP адрес</label>
                                <input name='ip' value='<?= $form['ip'] ?>' placeholder='10.10.10.10'
                                       class='form-control'>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                                <label class="control-label">MAC адрес</label>
                                <input name='mac' value='<?= $form['mac'] ?>' placeholder='AA:BB:CC:DD:EE:FF'
                                       class='form-control'>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                                <label class="control-label">Модель</label>
                                <select class='form-control' name='model'><?= $ht['model'] ?></select>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                                <label class="control-label">Группа</label>
                                <select class='form-control' name='group'><?= $ht['group'] ?></select>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                                <label class="control-label">Доступы</label>
                                <select class='form-control' name='access'><?= $ht['access'] ?></select>
                            </div>
                            <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                                <label class="control-label">Аплинк-порт</label>
                                <input name='uplink_port' value='<?= $form['uplink_port'] ?>' placeholder='25'
                                       class='form-control'>
                            </div>
                            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                                <div class="divider-dashed"></div>
                                <span style="font-size: 16px; font-weight:  500">Адрес</span>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                                <label class="control-label">Город</label>
                                <?= $html->listCities ?>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                                <label class="control-label">Улица</label>
                                <?= $html->listStreets ?>
                            </div>
                            <div class=" col-xs-12 col-sm-4 col-md-4 col-lg-2 form-group">
                                <label class="control-label">Дом</label>
                                <?= $html->listHouses ?>
                            </div>
                            <div class=" col-xs-6 col-sm-2 col-md-2 col-lg-1 form-group">
                                <label class="control-label">Подьезд</label>
                                <input name='entrance' class="form-control" value='<?= $form['entrance'] ?>'
                                       placeholder="1" pattern="[0-9]{1,3}">
                            </div>
                            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                                <div class="divider-dashed"></div>
                                <span style="font-size: 16px; font-weight:  500">Дополнительно</span>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-4 col-lg-4 form-group">
                                <label class="control-label">Коментарий</label>
                                <textarea name='description' class="form-control"
                                          style="height: 80px;"><?= $form['description'] ?></textarea>
                            </div>
                            <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                                <div class="divider-dashed"></div>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-4 col-lg-3 form-group">
                                <button type='submit' name='action' value='save' class='btn btn-primary btn-block'>
                                    Сохранить изменения
                                </button>
                            </div>
                            <div class=" col-xs-12 col-sm-6 col-md-4 col-lg-3 form-group">
                                <a href='/sw/?#/switcher/sys_info?ip=<?= $form['ip'] ?>' target='_blank'
                                   class='btn btn-primary btn-block'>Опросить свитчером</a>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-4 col-lg-6 form-group" align=right>
                                <button type='submit' name='action' value='delete' class='btn btn-danger'
                                        onclick='confirm("Уверены?");'>Удалить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($form['id']) { ?>
                <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Закрепление вланов</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <table>
                                <tr>
                                    <td style="width: 45%">
                                        Список вланов
                                        <select size="20" class='form-control' style="width: 100%" multiple
                                                name="unbind_vlan[]">
                                            <?= $ht['unbind_vlan'] ?>
                                        </select>
                                    <td valign="center" align="center" style="width: 10%">
                                        <button class="btn btn-default" type="submit" name="action" value="add_one_vlan"
                                                style="margin: 3px;"> >
                                        </button>
                                        <br>
                                        <button class="btn btn-default" type="submit" name="action" value="del_one_vlan"
                                                style="margin: 3px;"> <
                                        </button>
                                        <br>
                                        <button class="btn btn-default" type="submit" name="action" value="add_all_vlan"
                                                style="margin: 3px;"> >>
                                        </button>
                                        <br>
                                        <button class="btn btn-default" type="submit" name="action" value="del_all_vlan"
                                                style="margin: 3px;"> <<
                                        </button>
                                    <td style="width: 45%">
                                        Закрепленные за оборудованием
                                        <select size="20" style="width: 100%" class='form-control' multiple
                                                name="bind_vlan[]">
                                            <?= $ht['bind_vlan'] ?>
                                        </select>
                            </table>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <input hidden type='hidden' name='id' value='<?= $form['id'] ?>'>
        </form>
    </div>



<?= tpl('footer') ?>