<?php
$table = "<center><h3>По Вашему запросу доступов не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(!\envPHP\service\PSC::isPermitted('eq_access')) {
    pageNotPermittedAction();
}


if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

if(isset($form['del'])) {
   $test =  $sql->query("DELETE FROM equipment_access WHERE id = %1", $form['del']);
   if($test) {
       $html->addNoty('success', "Запись успешно удалена!");
   } else {
       $html->addNoty('error', "Ошибки при удалении записи: ".$sql->error."");
   }
}

if(isset($form['save'])) {
    $error = false;
    foreach ($form['ch'] as $k=>$v) {
       if($v['login'] != '' && $v['password'] != '' && $v['community'] != '') $test =  $sql->query("INSERT INTO equipment_access (id, login, password, community) VALUES (%1,%2, %3, %4) ON DUPLICATE key UPDATE login = %2, password = %3, community = %4", $k, $v['login'], $v['password'],$v['community']); 
        if(!$test) $error = true;
    }
    if($error) {
        $html->addNoty('error', "Ошибка обновления: ".$sql->error."");
    } else {
        $html->addNoty('success', "Успешно обновлено");
    }
}

$table = "<table class='table table-striped'><tr><th style='width: 300px'>Логин<th style='width: 300px'>Пароль<th style='width: 300px'>Комунити(RW)<th style='width: 40px'>Кол. оборудки<th><img src='/res/img/del.png' width=22>";
$data = $sql->query("SELECT id,  login, password, community,  (SELECT count(*) FROM equipment WHERE `access` = s.id) count FROM equipment_access s ORDER BY 2");
while ($d = $data->fetch_assoc()) {
    $table .= "<tr>"
           ."<td><input class='form-control' name='ch[".$d['id']."][login]' value='".$d['login']."'>"
           ."<td><input class='form-control' name='ch[".$d['id']."][password]' value='".$d['password']."'>"
           ."<td><input class='form-control' name='ch[".$d['id']."][community]' value='".$d['community']."'>"
           ."<td>".$d['count']
           ."<td><a href = '?del=".$d['id']."'><img src='/res/img/del.png' width=22></a>";
}
$max = $sql->query("SELECT max(id)+1 id FROM equipment_access")->fetch_assoc()['id'];
$table .= "
			<tr><td style='width: 300px'><input class='form-control' name='ch[$max][login]' >
			<td style='width: 300px'><input class='form-control' name='ch[$max][password]' >
			<td style='width: 300px'><input class='form-control' name='ch[$max][community]' >
			<td><td></table>";
?><?=tpl('head', ['title'=>''])?>
<form action="" method="post">
    <div class="row">
        <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Управление доступами</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="table-responsive-light">
                        <?=$table?>
                    </div>
                    <div class="clearfix"></div>
                    <div class="divider-dashed"></div>
                    <button class="btn btn-primary" type="submit" name="save">Сохранить изменения</button>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <b>Внимание! </b><br>
            Для сохранения целостности базы используются внешние ключи.<br>
            Соответсвенно, если по параметру есть оборудование - удаление НЕВОЗМОЖНО!
        </div>
    </div>
</form>
<?=tpl('footer')?>
