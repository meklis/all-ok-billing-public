<?php
$rank = 20;
$table = "<center><h3>По Вашему запросу пользователей не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();
$message = '';


if(!\envPHP\service\PSC::isPermitted('employees_add')) {
    pageNotPermittedAction();
}


$form = [
'id'=>0,
'name'=>'',
'position'=>0,
'phone'=>'',
'mail'=>'',
'skype'=>'',
'password'=>'',
'login'=>''
];

if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

if(isset($form['del'])) {
    $sql->query("DELETE FROM employees WHERE id = %1", $form['del']);
    header("Location: list");
    exit;
}
if(isset($form['save'])) {
    if($form['id'] == 0) {
  $test = $sql->query("INSERT INTO employees (name,phone,skype,mail,`position`,password, login) VALUES (%1,%2,%3,%4,%5,%6,%7)", $form['name'], $form['phone'], $form['skype'],$form['mail'],$form['position'],$form['password'], $form['login']);
  if($test) $form['id'] = $sql->query("SELECT max(id) id FROM employees")->fetch_assoc()['id']; 
  }  else {
  $test = $sql->query("UPDATE employees SET name  = %1, phone = %2, skype=%3, mail=%4, position=%5,login = %7 WHERE id = %6",
                               $form['name'], $form['phone'], $form['skype'],$form['mail'],$form['position'], $form['id'], $form['login']);
   if($form['password']) {
        $sql->query("UPDATE employees SET password = '{$form['password']}' WHERE id = '{$form['id']}'");
   }
 }
  if($test) {
        $html->addNoty('success', "Успешно сохранено");
  } else {
        $html->addNoty('error', 'Возникла проблема при сохранении');
  }
}


if($form['id'] != 0) {
    $form = $sql->query("SELECT e.id, e.name, phone, skype, mail, e.`position`,  e.password, e.login FROM `employees` e
 WHERE e.id = %1; ", $form['id'])->fetch_assoc();

}
//Choose Profession
$prof = $sql->query("SELECT id, `position` FROM emplo_positions");
$list_prof = "<SELECT name='position' class='form-control'>";
while($p  = $prof->fetch_assoc()) {
    if($form['position'] == $p['id']) $sel = "SELECTED"; else $sel = '';
    $list_prof .= "<OPTION value='".$p['id']."' $sel>".$p['position']."</OPTION>";
}
$list_prof .= "</SELECT>";
?><?=tpl('head', ['title'=>''])?>

<form action="" method="post">
    <div class="row">
        <div class="col-lg-offset-3 col-md-offset-2 col-sm-offset-1  col-sm-10 col-lg-6 col-md-8 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Изменение пользователя</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="form-horizontal form-label-left row">
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Имя
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input name='name' class="form-control" value='<?=$form['name']?>' placeholder='Иван Иванов' required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Номер телефона
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                 <input name='phone'  class="form-control" value='<?=$form['phone']?>' placeholder='+380631234567' required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Email
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input name='mail'  class="form-control" value='<?=$form['mail']?>' placeholder='example@mail.dot' >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Skype
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input name='skype'   class="form-control" value='<?=$form['skype']?>' placeholder='exampleMySkype' >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Должность
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <?=$list_prof?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Логин
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input name='login'   class="form-control" value='<?=$form['login']?>' placeholder='login' >
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Пароль
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input name='password'   class="form-control" value='' placeholder='myPass123' type="password">
                            </div>
                        </div>
                        <div class="divider-dashed"></div>
                            <div class="col-lg-4 col-md-4 col-xs-12">
                                <a href="list" class="btn btn-primary btn-block">Назад</a>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xs-12">
                                <button type="submit" name='save' class="btn btn-primary btn-block">Сохранить</button>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xs-12" style="text-align: right">
                                <a href="?del=<?=$form['id']?>" class="btn btn-primary" onclick="return confirm('вы уверены?') ? true : false;">Удалить</a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?=tpl('footer')?>
