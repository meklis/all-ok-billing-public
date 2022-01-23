<?php 
//Для распределения доступа укажите ранг страницы. доступ будут иметь те, у кого ранг больше или равен указаного
$rank = 10;

$success = '';
require($_SERVER['DOCUMENT_ROOT']."/include/load.php");
init();
$message = '';
$form = [

];
if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

$dir = "/www/site/pages";
function getFileList($dir)
  {$retval = array();
    if(substr($dir, -1) != "/") $dir .= "/";
    $d = dir($dir) or die("getFileList: Не удалось открыть каталог $dir для чтения");
    while(false !== ($entry = $d->read())) {
      if($entry[0] == ".") continue;
	  if(is_readable("$dir$entry")) {
        $retval[] = array(
          "name" => "$entry",
          "size" => filesize("$dir$entry"),
          "lastmod" => filemtime("$dir$entry")
        );
      }
    }
    $d->close();
    return $retval;
}

if(isset($form['save'])) {
	$file = "/www/site/pages/".$form['edit'];
	$test = file_put_contents($file, $form['text']);
	if($test != 0) {
        $html->addNoty('success', "Успешно обновлено");
    } else {
        $html->addNoty('error', "Ошибка обновления файла");
    }
    //    $post->clear('save');
}	
$dirlist = getFileList($dir);
$list = "<table class=' table table-striped table-bordered'><tr><th>Имя страницы<th>Посл. изменение<th><img src='/res/img/change.png' height=24>";
foreach ($dirlist as $v) {
	if(isset($form['edit']) && $form['edit'] == $v['name']) $color = "style='background: #C0C0C0'"; else $color = '';
	$list .= "<tr $color><td>".$v['name']."<td>".date('H:m:s d.m.Y', $v['lastmod'])."<td><a href='?edit=".$v['name']."'><img src='/res/img/change.png' height=24></a></tr>";
}
$list .="</table>";
$data = "<h2 align='center'>Выбери файл для редактирования</h2>";


if(isset($form['edit'])) {
	$file = $dir.'/'.$form['edit'];
	if (file_exists($file)) {
		$text = file_get_contents($file);
		$data = "<form action='' method='POST'>";
		$data .= "<textarea id='editor' name='text' class='form-control' style='height: 460px'>$text</textarea>";
		$data .= "<input type='hidden' hidden value='".$form['edit']."' name='edit'>";
		$data .="<br><button type='submit' class='btn btn-default' name='save'>Сохранить изменения</button>";
		
	}
}
?>
<?=tpl('head', ['title' => ''])?>
<a href='?p=default'>Главная</a>
-> <a href='?p=pages'><u>Управление страницами</u></a>
<div class='row' style='margin: 0; margin-top: 15px;'>
	<div class="col-sm-3">
		<?=$list?>
	</div>
	<div class="col-sm-9">
		<?=$success?>
		<?=$data?>
	</div>
</div>
<?=tpl('footer', ['provider'=> getGlobalConfigVar('BASE')['provider_name']])?>