<?php
$rank = 20;
$table = "<center><h3>По Вашему запросу пользователей не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(isset($_REQUEST['form'])) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

if(!isset($form['page'])) $form['page'] = 1;

$data = $sql->query("SELECT vl.id, vl.vlan, vl.name, k.`name` kind, k.description , IFNULL(c_eq,0) c_eq, IFNULL(c_nt,0) c_nt
FROM `eq_vlans` vl
JOIN eq_kinds k on k.id = vl.type 
LEFT JOIN (SELECT vlan, count(*) c_eq FROM eq_vlan_equipment GROUP BY vlan) eq on eq.vlan = vl.id 
LEFT JOIN (SELECT vlan, count(*) c_nt FROM eq_vlan_neth GROUP BY vlan) nt on nt.vlan = vl.id 
order by 2;
");
if($data->num_rows != 0) {
    $table = "<table class='t table-striped'><tr><th>ID<th>Vlan<th>VlanName<th>KindName<th>KindDescr<th>CountDevices<th>CountNetworks<th><img src='/res/img/change.png' width=24>";
    while($d = $data->fetch_assoc()) {
        $table .= "<tr><td>".$d['id']."<td>".$d['vlan']."<td>".$d['name']."<td>".$d['kind']."<td>".$d['description']."<td>".$d['c_eq']."<td>".$d['c_nt']."<td><a href = 'vlan_edit.php?id=".$d['id']."' ><img src='/res/img/change.png' width=24></a>";
    }
    $table .= "</table>";
}

?>
<?=tpl(head')?>
<?=tpl(menu')?>
<?=tpl('page_name',array('name'=>'Управление вланами', 'descr'=>''))?>
<div class="main">
    <div class="col-xs-6">
<?=$table?>
     </div>
        <div class="col-xs-5">
            <a href="edit_vlan?id=0" class="btn btn-primary">Добавить новый влан</a>
     </div>
    
</div>
<?=tpl('footer')?>