<?php
$rank = 20;
$table = "<center><h3>По Вашему запросу пользователей не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


if(!\envPHP\service\PSC::isPermitted('employees_show')) {
    pageNotPermittedAction();
}

if(isset($_REQUEST['form'])) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

if(!isset($form['page'])) $form['page'] = 1;

$data = $sql->query("SELECT e.id, e.name, phone, skype, mail, p.position FROM `employees` e
JOIN emplo_positions p on p.id = e.position WHERE e.display = 1;");
if($data->num_rows != 0) {
    $table = "<table class='table table-striped'>
            <thead>
                <tr>
                  <th>Имя</th>
                  <th>Профессия</th>
                  <th>Номер телефона</th>
                  <th>Меил</th>
                  <th>Скайп</th>
                  <th><img src='/res/img/change.png' width=24>
                  </th>
                  </tr>
                  </thead><tbody>";
    while($d = $data->fetch_assoc()) {
        $table .= "<tr><td>".$d['name']."<td>".$d['position']."<td>".$d['phone']."<td>".$d['mail']."<td>".$d['skype']."<td><a href = 'edit_employee?id=".$d['id']."' ><img src='/res/img/change.png' width=24></a>";
    }
    $table .= "</tbody></table>";
}

?><?=tpl('head', ['title'=>''])?>
<form action="" method="post">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Персонал</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="col-lg-9 col-md-12 col-sm-12 col-xs-12">
                        <div class="table-responsive-light">
                            <?=$table?>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                        <a href="edit_employee?id=0" class="btn btn-primary">Добавить пользователя</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?=tpl('footer')?>