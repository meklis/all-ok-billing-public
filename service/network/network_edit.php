<?php
$rank = 20;
$table = "<center><h3>Не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();
$message = '';


$form = [
    'id'=>0,
    'name'=>'',
    'type'=>0,
    'startIp'=>'',
    'stopIp'=>'',
    'mask'=>24,
    'gateway'=>'',
    'bind_vlan'=>[],
    'unbind_vlan'=>[],
];


envPHP\classes\std::Request($form);

if(isset($form['del'])) {
    $sql->query("DELETE FROM eq_neth WHERE id = %1", $form['del']);
    header("Location: networks");
    exit;
}
if(isset($form['save'])) {
    $test = function($form, &$message) {
        try {
            envPHP\classes\std::checkParam('ip',$form['startIp'], "startIP");
            envPHP\classes\std::checkParam('ip',$form['stopIp'], "stopIp");
            envPHP\classes\std::checkParam('ip',$form['gateway'], "Gateway");
        } catch (Exception $e) {
            $message = "<div id='message_fail'>" . $e->getMessage() . "</div>";
            return false;
        }
        return true;
    };
    if($test($form,$message)) {
        if ($form['id'] == 0) {
            $test = $sql->query("INSERT INTO eq_neth (name, type, startIp, stopIp, mask, gateway) 
VALUES (%1,%2,%3,%4,%5,%6)", $form['name'], $form['type'], $form['startIp'], $form['stopIp'], $form['mask'], $form['gateway']);
            if ($test) $form['id'] = $sql->query("SELECT max(id) id FROM eq_neth")->fetch_assoc()['id'];
        } else {
            $test = $sql->query("UPDATE eq_neth SET name  = %1, type = %2, startIp=%3, stopIp=%4, mask=%5, gateway=%6  WHERE id = %7",
                $form['name'], $form['type'], $form['startIp'], $form['stopIp'], $form['mask'], $form['gateway'], $form['id']);
        }
        if ($test) $message = "<div id='message_success'>Успешно сохранено</div>"; else $message = "<div id='message_fail'>Возникла проблема при сохранении. детали в /_logs/error_sql.log</div>";
    }
}


if($form['id'] != 0) {
    $form = $sql->query("SELECT h.id
, h.type type 
, h.name
, h.startIp
, h.stopIp
, h.gateway
, h.mask
, vl.vlan vlans 
,(SELECT count(*) FROM eq_bindings WHERE INET_ATON(ip) BETWEEN INET_ATON(startIp) and INET_ATON(stopIp)) bindings
 FROM `eq_neth` h
JOIN eq_kinds k on k.id = h.type
LEFT JOIN (SELECT GROUP_CONCAT(vlan) vlan, neth FROM eq_vlan_neth GROUP BY neth) vl on vl.neth = h.id
WHERE h.id = %1 ", $form['id'])->fetch_assoc();

}

//Choose kinds of types
$prof = $sql->query("SELECT id, name, description FROM eq_kinds WHERE parent = 2 order by name");
$list_prof = "<SELECT name='type' class='form-control'>";
while($p  = $prof->fetch_assoc()) {
    if($form['type'] == $p['id']) $sel = "SELECTED"; else $sel = '';
    $list_prof .= "<OPTION value='".$p['id']."' $sel>".$p['name']." - ".$p['description']."</OPTION>";
}
$list_prof .= "</SELECT>";
$title = $form['id'] ? "Изменение подсети {$form['name']}" : "Внесение подсети";
?><?=tpl('head', ['title'=>''])?>
<div class="row justify-content-md-center">
    <div class="col-lg-offset-1 col-md-offset-1 col-sm-offset-1  col-sm-10 col-lg-10 col-md-10 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?=$title?></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form  method="POST" enctype="multipart/form-data">
                    <div class="form-horizontal form-label-left row">
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">
                                Имя подсети (для отображения)
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input name='name' class="form-control" value='<?=$form['name']?>' placeholder='' required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree"> Первый IP(network)
                            </label>
                            <div class="col-md-8 col-xs-12">
                                <input name='startIp'  class="form-control" value='<?=$form['startIp']?>' placeholder='192.168.0.0' required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree"> Последний IP(broadcast)
                            </label>
                            <div class="col-md-8 col-xs-12">
                                <input name='stopIp'  class="form-control" value='<?=$form['stopIp']?>' placeholder='192.168.0.255' required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Шлюз(gateway)
                            </label>
                            <div class="col-md-8 col-xs-12">
                                <input name='gateway'  class="form-control" value='<?=$form['gateway']?>' placeholder='192.168.1.1' required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Маска подсети
                            </label>
                            <div class="col-md-8 col-xs-12">
                                <input name='mask'  class="form-control" value='<?=$form['mask']?>' placeholder='0-32' required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Тип подсети
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <?=$list_prof?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">&nbsp;
                            </label>
                            <div class="col-md-8 col-xs-12">
                                <button type="submit" name='save' class="btn btn-primary">Сохранить</button>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="divider-dashed"></div>
                        <div class="col-md-6 col-xs-6">
                            <a href="networks" class="btn btn-primary">Вернуться к списку подсетей</a>
                        </div>
                        <div class="col-md-6 col-xs-6" align="right">
                            <a href="?del=<?=$form['id']?>" class="btn  " onclick="return confirm('вы уверены?') ? true : false;">Удалить</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
if($form['id']) {
$ht = [
        'unbind_vlan'=>'',
        'bind_vlan'=>'',
];
envPHP\classes\std::Request($form);
    if(@$form['action'] == 'add_one_vlan' && @$form['unbind_vlan']) {
        foreach (@$form['unbind_vlan'] as $val) {
            $test =  $sql->query("INSERT INTO eq_vlan_neth (vlan, neth) VALUES ($val, {$form['id']})");
        }
    }
    if(@$form['action'] == 'del_one_vlan' && @$form['bind_vlan']) {
       foreach (@$form['bind_vlan'] as $val) {
            $test =  $sql->query("DELETE FROM eq_vlan_neth WHERE neth =  {$form['id']} and vlan = $val");

        }
    }
    if(@$form['action'] == 'add_all_vlan') {
        $sql->query("INSERT INTO eq_vlan_neth (neth, vlan)
                            SELECT {$form['id']}, id FROM eq_vlans");
    }
    if(@$form['action'] == 'del_all_vlan') {
        $sql->query("DELETE FROM eq_vlan_neth WHERE neth = '{$form['id']}'");
    }

//Выборка всех вланов НЕ закрепленных за железом
        $data = $sql->query("SELECT v.id, v.vlan, k.`name`, k.description 
FROM eq_vlans v 
JOIN eq_kinds k on k.id = v.type
WHERE v.id not in (SELECT vlan FROM eq_vlan_neth WHERE neth = {$form['id']}) 
ORDER  by vlan");
        while ($d = $data->fetch_assoc()) {
            $ht['unbind_vlan'] .= "<OPTION value='{$d['id']}'>{$d['vlan']} - {$d['name']} - {$d['description']}</OPTION>";
        }


//Выборка всех вланов  закрепленных за железом
        $data = $sql->query("SELECT v.id, v.vlan, k.`name`, k.description 
FROM eq_vlans v 
JOIN eq_kinds k on k.id = v.type
WHERE v.id  in (SELECT vlan FROM eq_vlan_neth WHERE neth = {$form['id']}) 
ORDER  by vlan ");
        while ($d = $data->fetch_assoc()) {
            $ht['bind_vlan'] .= "<OPTION value='{$d['id']}'>{$d['vlan']} - {$d['name']} - {$d['description']}</OPTION>";
 }

?>
    <div class="row justify-content-md-center">
        <div class=" col-sm-12 col-lg-12 col-md-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Закрепление подсети за вланами</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form method="POST">
                    <table align="center">
                        <td style="width: 45%">
                            Список вланов
                            <select size="20" style='width: 350px' class='form-control' multiple name="unbind_vlan[]" >
                                <?=$ht['unbind_vlan']?>
                            </select>
                        <td align="center" valign="center" style="width: 10%">
                            <button class="btn btn-default" type="submit" name="action" value="add_one_vlan" style="margin: 3px;"> > </button><br>
                            <button class="btn btn-default" type="submit" name="action" value="del_one_vlan" style="margin: 3px;"> < </button><br>
                            <button class="btn btn-default" type="submit" name="action" value="add_all_vlan" style="margin: 3px;"> >> </button><br>
                            <button class="btn btn-default" type="submit" name="action" value="del_all_vlan" style="margin: 3px;"> << </button>
                        <td style="width: 45%">
                            Подсеть закреплена за
                            <select size="20" style='width: 350px' class='form-control' multiple name="bind_vlan[]" >
                                <?=$ht['bind_vlan']?>
                            </select>
                    </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
    }
?>
<?=tpl('footer')?>
