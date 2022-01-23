<?php

require($_SERVER['DOCUMENT_ROOT']."/include/load.php");
init();
$message = '';

$form = [
'display'=>'',
'text'=>'',
'title'=>'',
'descr'=>'',
'id'=>0
];
if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

if($form['display']) $disp = 'CHECKED'; else $disp='';


if(isset($form['action'])) {
    if($form['id'] == 0) {
        if($form['display'])  $display=1; else $display = 0;
	$test = $sql->query("INSERT INTO site.`sitePages` (title, description, `show`, category, text) VALUES (%1,%2,%3,%4,%5);", 
                                                $form['title'],$form['descr'], $display,$form['cat'], $form['text']);
     } else {
         if($form['display'])  $display=1; else $display = 0;
        $test = $sql->query("UPDATE site.`sitePages` SET title = %1, description = %2, `show` = %3, text = %4 WHERE id = %5", $form['title'], $form['descr'], $display,$form['text'], $form['id']);
    }
    if($test) {
        $html->addNoty('success', "Запись успешно сохранена");
    } else {
        $html->addNoty('error', "Возникли ошибки при сохранении");
    }
}
if(isset($form['del'])) {
    $test = $sql->query("DELETE FROM site.sitePages WHERE id = %1", $form['del']);
    if($test) $message = "<div id='message_success' >Запись успешно удалена</siv>"; else $message = "<div id='message_fail'>Возникли ошибки при удалении</div>";
header('Location: /site/list?message='.$message);
}
if($form['id'] != 0) {
    $data = $sql->query("SELECT * FROM site.sitePages WHERE id = %1", $form['id'])->fetch_assoc();
    $form['text'] = $data['text'];
    $form['descr'] = $data['description'];
    $form['title'] = $data['title'];
  if($data['show'] == 1) $disp = "CHECKED" ; else $disp= '';
}

//Определение стандартных переменных
$categories = array(1=>'Новости',
   		2=>'Полезная информация');
 
?>
<?=tpl('head', ['title' => ''])?>

<div class='row' style='margin: 0; margin-top: 15px;'>
    <div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Изменение записи</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content ">
                <div class="col-lg-12 col-md-12 col-xs-12 col-sm-12" style="margin-bottom: 10px">
                    <form action='' method='POST'>
                        <?=$message?>
                        <input type="checkbox"  style='display: inline' name='display' <?=$disp?>> Опубликовать на сайте<br>
                        Заголовок: <input name='title' class='form-control' value='<?=$form['title']?>'>
                        <input name='id' type='hidden' hidden class='form-control' value='<?=$form['id']?>'>
                        Краткое описание: <textarea class='form-control' name='descr'><?=$form['descr']?></textarea>
                        Содержание страницы: <textarea class='form-control' id='editor' name='text' style='height: 260px'><?=$form['text']?></textarea><br>
                        <button type="submit" class="btn btn-primary" name='action'>Сохранить</button>
                    </form>
                    <a href="/site/list" class="btn btn-primary">Вернуться к списку</a><br><br><a href="/site/detail?del=<?=$form['id']?>" class="btn btn-primary">Удалить запись</a>
                </div>
            </div>
        </div>
    </div>

</div>

<?=tpl('footer', ['provider'=> getGlobalConfigVar('BASE')['provider_name']])?>
 