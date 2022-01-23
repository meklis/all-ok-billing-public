<?php
require($_SERVER['DOCUMENT_ROOT']."/include/load.php");
init();
if(!$post->lastPage()) $post->clearAll();
$self = $post->href();
$form = $post->POST();
$message = '';
if(!isset($form['text'])) $form['text'] = '';
if(!isset($form['display'])) $form['display'] = '';
if(!isset($form['title'])) $form['title'] = '';
if(!isset($form['descr'])) $form['descr'] = '';
if(!isset($form['cat'])) $form['cat'] = 1;
if(!isset($form['id'])) $form['id'] = 0;
if(!isset($form['page'])) $form['page'] = 1;
if(!isset($form['search'])) $form['search'] = '';


if(isset($form['action'])) {
    if($form['id'] == 0) {
	$test = $sql->query("INSERT INTO site.`sitePages` (title, description, `show`, category, text) VALUES (%1,%2,%3,%4);", $form['title'],$form['descr'], $form['display'],$form['cat'], $form['text']);
     } else {
        $text = $sql->query("UPDATE site.`sitePages` SET title = %1, description = %2, `show` = %3, text = %4 WHERE id = %5", $form['title'], $form['descr'], $form['display'],$form['text'], $form['id']);
    }
if($test) $message = "<div id='message_success' >Запись успешно сохранена</div>"; else $message = "<div id='fail' >Возникли ошибки при сохранении</div>";
$post->clear('action');  
}
if(isset($form['del'])) {
    $test = $sql->query("DELETE FROM site.sitePages WHERE id = %1", $form['id']);
    if($test) $message = "<div id='message_success' >Запись успешно удалена</siv>"; else $message = "<div id='message_fail'>Возникли ошибки при удалении</div>";
$post->clear('del');
}
//Определение стандартных переменных
$categories = array(1=>'Новости',
   		2=>'Полезная информация');
$hlist = "<h3 align='center'>Не найдено ни одной записи!</h3>";


//Заполнение формы 
if($form['id'] != 0) {
	$test = $sql->query("SELECT * FROM site.sitePages WHERE id = %1",$form['id']);
	if($test->num_rows != 0) {
		$data = $test->fetch_assoc();
		$show = $data['show'];
		$title = $data['title'];
		$desc = $data['description'];
                $html = $data['text'];
	}
} else $show = '';


//фильтр limit + страничка листинга. берем по 20 записей
$limit = "0,15";
$fields = 15;
$num_rows = $sql->query("SELECT count(id) count FROM site.sitePages WHERE  category = %1", $form['cat'])->fetch_assoc()['count'];
if($num_rows != 0) 
{	
	$count_pages = ceil($num_rows/$fields);
	$counter=1;
	for ($x=-1; $x++<$count_pages-1;) {
		$pages[$counter]= $fields*$x.",".$fields;
		$counter++;
	}
	if($form['page'] != 1) $limit = $pages[$page];
	$hpage = 'Страницы: ';
	foreach($pages as $k=>$v) {	
		if($k==$form['page']) $btn = "btn-primary"; else $btn='btn-default';
		if($k == 1) {$hpage .= "<a href='?p=notes&cat=".$form['cat']."&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;}
		if($k == count($pages)) {$hpage .= "<a href='?p=notes&cat=".$form['cat']."&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;}
		if($k==$form['page']-3 || $k ==$form['page']+3) {$hpage .= "<div style='font-weight: bold; padding-left: 5px; padding-right: 5px; display: inline'> . . . </div>"; continue;} 
		if($k==$form['page']-2 || $k ==$form['page']+2) {$hpage .= "<a href='?p=notes&cat=".$form['cat']."&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;} 
		if($k==$form['page']-1 || $k == $form['page']+1) {$hpage .= "<a href='?p=notes&cat=".$form['cat']."&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;} 
		if($k==$form['page']) {$hpage .= "<a href='?p=notes&cat=".$cat."&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;} 
	}
}
//Фильтр WHERE 
$where = "WHERE category = '".$form['cat']."'";
if($form['search'] != '') {
		$where .= " and concat(title,desc,text) like '".$form['search']."'";
                $limit  = "0,50";
                }

$sql_q = "SELECT * FROM site.sitePages $where  order by id desc limit $limit";
$data_list = $sql->query($sql_q);
if($data_list->num_rows != 0) {
	$hlist = "<table class=' t table-striped table-bordered' style='width: 100%'><tr><th>Заголовок<th>Создана<th width=30><img src='/res/img/eye.png' height=18 width=24><th  width=28><img src='/res/img/change.png' height=24><th  width=28><img src='/res/img/del.png' height=24>";
	while($data = $data_list->fetch_assoc()) {
		if($data['show'] != 0 ) $show = 'Да'; else $show = 'Нет';
		if($data['id'] == $form['id']) $color="style='background: #C0C0C0'"; else $color='';
		$hlist .= "<tr $color>";
		$hlist .= "<td>".$data['title'];
		$hlist .= "<td>".$data['created'];
		$hlist .= "<td>".$show;
		$hlist .= "<td><a href='?p=notes&cat=".$form['cat']."&id=".$data['id']."&page=".$form['page']."'><img src='/res/img/change.png' height=24></a>";
		$hlist .= "<td><a href='?p=notes&cat=".$form['cat']."&id=".$data['id']."&page=".$form['page']."&act=del' onClick=\"return window.confirm('Уверены, что хотите удалить?');\" ><img src='/res/img/del.png' height=24></a>";
	}
	$hlist .= "</table>";
}

?>
<?=html('head')?>
<?=html('menu')?>
<a href='?p=default'>Главная</a>
-> <a href='?p=pages'><u>Управление записями</u></a>
<div class='row' style='margin: 0; margin-top: 15px;'>
	<div class="col-sm-5">
	<form action='' METHOD='POST' >
		<input name='list[search]' value='<?=$form['search']?>' placeholder='Поиск по названию, описанию...' class=' col-sm-8 col-xs-8 col-lg-8 form-control' style='height: 34px; max-width: 300px; margin-bottom: 7px; '>
		<button class='btn btn-default' style='height: 34px; margin-left: 5px; margin-bottom: 7px;' type='submit'>Найти</button>
		</form>
		<?=$hlist?>
		<?=$hpage?>
	</div>
	<div class="col-sm-7">
			<form action='<?=$href?>' method='POST'>
			<h3>Изменение записи</h3>
			<?=$message?>
			<input type="checkbox"  style='display: inline' name='display' <?=display?>> Опубликовать на сайте<br>
			Заголовок: <input name='title' class='form-control' value='<?=$title?>'>
			Краткое описание: <textarea class='form-control' name='descr'><?=$desc?></textarea>
			Содержание страницы: <textarea class='form-control' id='editor' name='text' style='height: 200px'><?=$html?></textarea><br>
                        <button type="submit" class="btn btn-primary" name='action'>Сохранить</button>
			</form>
	</div>
</div>

 