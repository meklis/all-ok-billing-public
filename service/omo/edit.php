<?php
use envPHP\classes\std;

require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();
$form = [
	'id'=>0,
	'status'=>'',

    'city'=>0,
    'street'=>0,
    'house'=>0,
    'entrance'=>'',

    'comment'=>'',
    'apartment' => '',
    'floor' => '',

    'created_at' => '',
    'hub_uid' => '',
    'device_uid' => '',
];
$ht = [
  'status' => '',
];

$statuses = [
    'BINDED' => 'Закреплен',
    'ENABLED' => 'Включен',
    'DISABLED' => 'Выключен',
    'NOT_BINDED' => 'Не закреплен',
    'DELETED' => 'Удален',
];

std::Request($form);

//Действия с записями
if(@$form['action'] == 'save') {
	//Проверим наличие записи в базе
    $hasError = false;
    if(!$form['house']) {
        $hasError = true;
        $html->addNoty('warning', "Адрес(дом) обязателен для заполнения");
    }
    if(!$form['entrance']) {
        $hasError = true;
        $html->addNoty('warning', "Подьезд обязателен для заполнения");
    }

	$id = $sql->query("SELECT id FROM omo_devices WHERE id = '{$form['id']}'")->fetch_assoc()['id'];
	if(!$id) {
        $html->addNoty('error', "Устройство с ID '{$form['id']}' не найдено");
    } else if (!$hasError) {
        $updateParams = [];
        $updateParams[]  = "entrance = '{$form['entrance']}'";
        $updateParams[]  = "comment = '{$form['comment']}'";
        $updateParams[]  = "house = '{$form['house']}'";
        if($form['house'] && $form['entrance']) {
            $updateParams[] = "status = 'BINDED'";
        }
        if($form['floor'] !== '') {
            $updateParams[]  = "floor = '{$form['floor']}'";
        }
        if($form['apartment'] !== '') {
            $updateParams[]  = "apartment = '{$form['apartment']}'";
        }
		$query = "UPDATE omo_devices SET  " . join(",", $updateParams). " WHERE id = '{$form['id']}'";
		$test = $sql->query($query);
		if(!$test) {
            $html->addNoty('error', "SQL ERR: {$sql->error}");
        }
	}
}


//Выбор существующего оборудование   
$fetch = $sql->query("
SELECT d.id, 
d.created_at, 
d.hub_uid, 
d.device_uid, 
d.type, 
d.`status`, 
if(a.id is null, '', a.full_addr) addr,
d.house,
d.entrance,
d.floor,
d.apartment,
d.`comment`,
d.delete_reason
FROM `omo_devices` d 
LEFT JOIN omo_device_bindings b on b.device_id = d.id  
LEFT JOIN addr a on a.id = d.house
WHERE d.id = '{$form['id']}' 
ORDER BY created_at desc ")->fetch_assoc();
foreach ($fetch as $f=>$v) {
    if (in_array($f, ['house'])) {
        if($form['city'] == 0 || $form['street'] == 0) {
            $form[$f] = $v;
        }
    } else {
        $form[$f] = $v;
    }
}


foreach ($statuses as $k=>$v) {
	if($k == $form['status']) $sel = "SELECTED"; else $sel = "";
	$ht['status'] .= "<OPTION value='{$k}' $sel>{$v}</OPTION>";
}

//Блок с адресом
if($form['house'] && !$form['city'] && !$form['street']) {
     $addrBind = $sql->query("SELECT h.id house_id, st.id street_id, c.id city_id 
            FROM addr_houses h 
            LEFT JOIN addr_streets st on st.id = h.street 
            LEFT JOIN addr_cities c on c.id = st.city
            WHERE h.id = '{$form['house']}'")->fetch_assoc();
     $form['city'] = $addrBind['city_id'];
     $form['street'] = $addrBind['street_id'];
}

$html->getHouses($form['city'],$form['street'],$form['house'], $sql);

echo tpl('head', ['title'=>"Изменение OMO устройства {$form['device_uid']}"])

?>

<div class="row">
    <form name="form" method="POST">
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
                        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-5 form-group">
                            <table style="border: 1px solid dimgray; background: #F0F0F0; width: 100%">
                                <tr>
                                    <th style="padding: 5px;border-bottom: 1px solid dimgray;">Добавлен</th>
                                    <td style="padding: 5px; border-bottom: 1px solid dimgray; "><?=$form['created_at']?></td>
                                </tr>
                                <tr>
                                    <th style="padding: 5px;border-bottom: 1px solid dimgray;">Статус</th>
                                    <td style="padding: 5px; border-bottom: 1px solid dimgray; "><?=$statuses[$form['status']]?></td>
                                </tr>
                                <tr>
                                    <th style="padding: 5px;border-bottom: 1px solid dimgray;">ID устройства</th>
                                    <td style="padding: 5px;border-bottom: 1px solid dimgray;"><?=$form['device_uid']?></td>
                                </tr>
                                <tr>
                                    <th style="padding: 5px;border-bottom: 1px solid dimgray;">Тип устройства</th>
                                    <td style="padding: 5px;border-bottom: 1px solid dimgray;"><?=$form['type']?></td>
                                </tr>
                                <tr>
                                    <th style="padding: 5px;border-bottom: 1px solid dimgray;">ID хаба</th>
                                    <td style="padding: 5px;border-bottom: 1px solid dimgray;"><?=$form['hub_uid']?></td>
                                </tr>
                            </table>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Этаж</label>
                            <input  value='<?=$form['floor']?>' name="floor" class='form-control'>
                            <label class="control-label">Квартира</label>
                            <input  value='<?=$form['apartment']?>' name="apartment" class='form-control'>
                            <small>
                                Для устройств, которые установленны в необычном месте
                            </small>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-12 col-lg-5 form-group">
                            <label class="control-label">Коментарий</label>
                            <textarea name="comment"  class='form-control' rows="5"><?=$form['comment']?></textarea>
                        </div>
                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group"  style="margin: 0">
                            <div class="divider-dashed"></div>
                            <span style="font-size: 16px; font-weight:  500">Адрес</span>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                            <label class="control-label">Город*</label>
                            <?=$html->listCities?>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                            <label class="control-label">Улица*</label>
                            <?=$html->listStreets?>
                        </div>
                        <div class=" col-xs-12 col-sm-4 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Дом*</label>
                            <?=$html->listHouses?>
                        </div>
                        <div class=" col-xs-6 col-sm-2 col-md-2 col-lg-1 form-group">
                            <label class="control-label">Подьезд*</label>
                            <input name='entrance' class="form-control"  value='<?=$form['entrance']?>' placeholder="1" pattern="[0-9]{1,3}" required>
                        </div>

                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group"  style="margin: 0">
                            <div class="divider-dashed"></div>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-4 col-lg-3 form-group">
                            <button type='submit' name='action' value='save' class='btn btn-primary btn-block'>Сохранить изменения</button>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-4 col-lg-3 form-group">
                            <a href="/omo/devices" name='action' value='save' class='btn btn-primary btn-block'>К устройствам</a>
                        </div>
                       <!--
                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-6 form-group" align=right>
                            <button type='submit' name='action' value='delete' class='btn btn-danger' onclick='confirm("Уверены?");'>Удалить</button>
                        </div>
                        -->
                    </div>
                </div>
            </div>
        </div>

        <input hidden type='hidden' name='id' value='<?=$form['id']?>'>
    </form>
</div>

<?=tpl('footer')?>