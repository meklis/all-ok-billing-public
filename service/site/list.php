<?php 
//Для распределения доступа укажите ранг страницы. доступ будут иметь те, у кого ранг больше или равен указаного
$rank = 10;
$hlist='<h3>Записей не найдено</h3>';
require($_SERVER['DOCUMENT_ROOT']."/include/load.php");
init();
//if(!$post->lastPage()) $post->clearAll();
//$self = $post->href();
//$form = $post->POST();
$form = [
'page'=>1,
'search'=>'',
'cat'=>1
];
if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=$v;


$categories = array(1=>'Новости',
   		2=>'Полезная информация');
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
	if($form['page'] != 1) $limit = $pages[$form['page']];
	$hpage = 'Страницы: ';
	foreach($pages as $k=>$v) {	
		if($k==$form['page']) $btn = "btn-primary"; else $btn='btn-default';
		if($k == 1) {$hpage .= "<a href='?p=notes&cat=".$form['cat']."&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;}
		if($k == count($pages)) {$hpage .= "<a href='?p=notes&cat=".$form['cat']."&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;}
		if($k==$form['page']-3 || $k ==$form['page']+3) {$hpage .= "<div style='font-weight: bold; padding-left: 5px; padding-right: 5px; display: inline'> . . . </div>"; continue;} 
		if($k==$form['page']-2 || $k ==$form['page']+2) {$hpage .= "<a href='?p=notes&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;} 
		if($k==$form['page']-1 || $k == $form['page']+1) {$hpage .= "<a href='?p=notes&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;} 
		if($k==$form['page']) {$hpage .= "<a href='?p=notes&cat=".$form['cat']."&page=$k' style='margin-left: 2px;' class='btn $btn'>$k</a>"; continue;} 
	}
}
//Фильтр WHERE 
$where = "WHERE category = '".$form['cat']."'";
if($form['search'] != '') {
		$where .= " and (description like  '%".$form['search']."%' or title like '%".$form['search']."%') or text like '%".$form['search']."%'";
                $limit  = "0,50";
                }

$sql_q = "SELECT * FROM site.sitePages $where  order by id desc limit $limit";
$data_list = $sql->query($sql_q);
if($data_list->num_rows != 0) {
	$hlist = "<table class=' table table-striped table-bordered' style='margin-left: 5px    '><tr><th>Заголовок<th>Создана<th width=30><img src='/res/img/eye.png' height=18 width=24><th  width=28><img src='/res/img/change.png' height=24>";
	while($data = $data_list->fetch_assoc()) {
		if($data['show'] != 0 ) $show = 'Да'; else $show = 'Нет';
		 $color='';
		$hlist .= "<tr $color>";
		$hlist .= "<td>".$data['title'];
		$hlist .= "<td>".$data['created'];
		$hlist .= "<td>".$show;
		$hlist .= "<td><a href='detail?p=notes&cat=".$form['cat']."&id=".$data['id']."&page=".$form['page']."'><img src='/res/img/change.png' height=24></a>";
		}
	$hlist .= "</table>";
}

?>
<?=tpl('head', ['title'=>"Список записей, категория: {$categories[$form['cat']]}"])?>

<div class="row"  >
    <div class="col-lg-3 col-md-3" style="margin-left: 10px">
        <form action="" method='post'>
        <small>Поиск по содержимому</small><br>
        <input name='search' class="form-control" value='<?=$form['search']?>'><br>
        <button type='submit' class='btn btn-primary' >Искать</button>
        </form>
        <br><br> <br><br>
        <a href="detail?id=0&cat=<?=$form['cat']?>" class="btn btn-primary">Добавить запись</a>
  
        
    </div>
    <div class="col-lg-6 col-md-6 col-xs-6" style="margin-left: 10px">
<?=$hlist?><br>
		<?=$hpage?>
<br><br>
    </div>
</div>
<?=tpl('footer', ['provider'=> getGlobalConfigVar('BASE')['provider_name']])?>


