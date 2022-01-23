<?php
$rank = 20;
$table = "<center><h3>С таким ID влан не найден</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


$form = [
'id'=>0,
'name'=>'',
'type'=>0,
'vlan'=>'',
];

if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

if(isset($form['del'])) {
    $sql->query("DELETE FROM eq_vlans WHERE id = %1", $form['del']);
    header("Location: vlans");
    exit;
}
if(isset($form['save'])) {
    if($form['id'] == 0) {
  $test = $sql->query("INSERT INTO eq_vlans (vlan, name, type) values (%1, %2, %3)", $form['vlan'], $form['name'], $form['type']);
  if($test) $form['id'] = $sql->query("SELECT max(id) id FROM eq_vlans")->fetch_assoc()['id'];
  }  else {
  $test = $sql->query("UPDATE eq_vlans SET vlan  = %1, name = %2, type=%3 WHERE id = %4", $form['vlan'], $form['name'], $form['type'], $form['id']);
    }
  if($test) {
      $html->addNoty('success', 'Успешно сохранено');
  } else {
      $html->addNoty('error', 'Возникла проблема при сохранении');
  }
}


if($form['id'] != 0) {
    $form = $sql->query("SELECT vl.id, vl.vlan, vl.name, k.`name` kind, k.description , IFNULL(c_eq,0) c_eq, IFNULL(c_nt,0) c_nt, k.id type
FROM `eq_vlans` vl
JOIN eq_kinds k on k.id = vl.type 
LEFT JOIN (SELECT vlan, count(*) c_eq FROM eq_vlan_equipment GROUP BY vlan) eq on eq.vlan = vl.id 
LEFT JOIN (SELECT vlan, count(*) c_nt FROM eq_vlan_neth GROUP BY vlan) nt on nt.vlan = vl.id 
WHERE vl.id = %1; ", $form['id'])->fetch_assoc();

}

//Choose kinds of vlan
$prof = $sql->query("SELECT id, name, description FROM eq_kinds WHERE parent = 1 order by name");
$list_prof = "<SELECT name='type' class='form-control'>";
while($p  = $prof->fetch_assoc()) {
    if($form['type'] == $p['id']) $sel = "SELECTED"; else $sel = '';
    $list_prof .= "<OPTION value='".$p['id']."' $sel>".$p['name']." - ".$p['description']."</OPTION>";
}
$list_prof .= "</SELECT>";

$title =  $form['id']  ? "Изменение влана {$form['vlan']}" : "Внесение влана";
?><?=tpl('head', ['title'=>''])?>
<div class="row justify-content-md-center">
    <div class="col-lg-offset-3 col-md-offset-2 col-sm-offset-1  col-sm-10 col-lg-6 col-md-8 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><?=$title?></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form  method="POST" enctype="multipart/form-data">
                        <div class="form-horizontal form-label-left row">
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">ID влана
                                </label>
                                <div class=" col-md-8 col-xs-12">
                                    <input name='vlan' class="form-control" value='<?=$form['vlan']?>' placeholder='4095' required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Имя влана
                                </label>
                                <div class="col-md-8 col-xs-12">
                                    <input name='name'  class="form-control" value='<?=$form['name']?>' placeholder='INTERNET_REAL' required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Тип влана
                                </label>
                                <div class=" col-md-8 col-xs-12">
                                    <?=$list_prof?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">&nbsp;
                                </label>
                                <div class="col-md-8 col-xs-12">
                                    <button class="btn btn-primary btn-block" type="submit" name="save"  value="add" >Сохранить</button>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="divider-dashed"></div>
                            <div class="col-md-6 col-xs-6">
                                <a href="vlans" class="btn btn-primary">Вернуться к списку вланов</a>
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
<?=tpl('footer')?>
