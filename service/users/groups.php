<?php
$rank = 21;
$table = "<center><h3>По Вашему запросу пользователей не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(!\envPHP\service\PSC::isPermitted('employees_group')) {
    pageNotPermittedAction();
}


$message = '';

if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

if(isset($form['del'])) {
   $test =  $sql->query("DELETE FROM emplo_positions WHERE id = %1", $form['del']);
   if($test) {
       $html->addNoty("success", "Изменения успешно сохранены!");
       $message = "<div id='message_success'>Группа успешно удалена!</div>";
   } else {
       $html->addNoty("error", "Возникли ошибки при удалении");
       $message="<div id='message_fail'>Ошибки при удалении группы: ".$sql->error."</div>";
   }
}
if(isset($form['save'])) {
    $error = false;
    foreach ($form['ch'] as $k=>$v) {
        $v['rank'] = 30;
       if( $v['name'] != '') $test =  $sql->query("INSERT INTO emplo_positions (position, rank, id) VALUES (%1,%2, %3) ON DUPLICATE key UPDATE position = %1, rank = %2", $v['name'], $v['rank'], $k );
        if(!$test) $error = true;
    }
        if($error) {
            $html->addNoty("success", "Ошибка обновления: ".$sql->error."");
        } else {
            $html->addNoty("success", "Изменения успешно сохранены!");
        }
    }

$table = "<table class='table table-striped'><thead>
     <tr>
        <th style='width: 300px'>Имя профессии</th>
        <th style='width: 80px'>Контроль прав</th>
        <th style='width: 80px'>Количество пользователей</th>
        <th style='text-align: right'><img src='/res/img/del.png' width=22></th>
        </tr>
        </thead><tbody>";
$data = $sql->query("SELECT id, position name, `rank`, (SELECT count(*) FROM employees WHERE position = s.id) count FROM emplo_positions s ORDER BY 2");
while ($d = $data->fetch_assoc()) {
    $table .= "<tr>"
           ."<td><input class='form-control' name='ch[".$d['id']."][name]' value='".$d['name']."'>"
           ."<td style='width: 80px'><a href='/users/group_permission?id={$d['id']}'><i class='fa fa-pencil-square-o' style='font-size: 26px'></i></a>"
           ."<td>".$d['count']
           ."<td style='text-align: right'><a href = '?del=".$d['id']."'><img src='/res/img/del.png' width=22></a>";
}
$max = $sql->query("SELECT max(id)+1 id FROM emplo_positions")->fetch_assoc()['id'];
$table .= "<tr><td style='width: 40px'><input class='form-control' name='ch[$max][name]' ><td style='width: 40px'><td><td></tbody></table>";

?><?=tpl('head', ['title'=>''])?>

    <form action="" method="post">
        <div class="row">
            <div class="col-lg-offset-3 col-md-offset-2 col-sm-offset-1  col-sm-10 col-lg-6 col-md-8 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Управление группами пользователей</h2>
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
        </div>
    </form>
<?=tpl('footer')?>