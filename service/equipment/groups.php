<?php
$rank = 20;
$table = "<center><h3>По Вашему запросу Групп не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(!\envPHP\service\PSC::isPermitted('eq_group')) {
    pageNotPermittedAction();
}


if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

if(isset($form['del'])) {
   $test =  $sql->query("DELETE FROM equipment_group WHERE id = %1", $form['del']);
   if($test) {
       $html->addNoty('success', "Запись успешно удалена!");
   } else {
       $html->addNoty('error', "Ошибки при удалении записи: ".$sql->error."");
   }
}

if(isset($form['save'])) {
    $error = false;
    foreach ($form['ch'] as $k=>$v) {
       if($v['name'] != '') $test =  $sql->query("INSERT INTO equipment_group (id, name, description) VALUES (%1,%2, %3) ON DUPLICATE key UPDATE name = %2, description = %3", $k, $v['name'], $v['description']); 
        if(!$test) $error = true;
    }
        if($error) {
            $html->addNoty('error', "Ошибка обновления: ".$sql->error."");
        } else {
            $html->addNoty('success', "Успешно обновлено");
        }
}

$table = "<table class='table table-striped'><tr><th style='width: 300px'>Название<th style='width: 300px'>Описание<th style='width: 40px'>Кол. оборудки в группе<th align='right' style='text-align: right !important;'><img src='/res/img/del.png' width=22>";
$data = $sql->query("SELECT id,  name, description,  (SELECT count(*) FROM equipment WHERE `group` = s.id) count FROM equipment_group s ORDER BY 2");
while ($d = $data->fetch_assoc()) {
    $table .= "<tr>"
        ."<td><input class='form-control' name='ch[".$d['id']."][name]' value='".$d['name']."'>"
        ."<td><input class='form-control'  style = 'width: 300px;' name='ch[{$d['id']}][description]' value='{$d['description']}'>"
        ."<td style='width: 140px'>".$d['count']
        ."<td align='right'><a href = '?del=".$d['id']."'><img src='/res/img/del.png' width=22></a>";
}
$max = $sql->query("SELECT max(id)+1 id FROM equipment_group")->fetch_assoc()['id'];
$table .= "<tr><td style='width: 40px'><input class='form-control' name='ch[$max][name]' ><td><input class='form-control'   name='ch[{$max}][description]'><td></td><td></td></table>";

?><?=tpl('head', ['title'=>''])?>


    <form action="" method="post">
        <div class="row">
            <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Управление группами</h2>
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