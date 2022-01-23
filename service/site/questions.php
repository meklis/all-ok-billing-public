<?php
$rank = 5;

$message = '';
$table = "<center><h3>По Вашему запросу записей не найдено не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT']."/include/load.php");
init();

if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k] = $v;

if(!isset($form['date1'])) $form['date1'] = date('d.m.Y');
if(!isset($form['date2'])) $form['date2'] = date('d.m.Y');
if(isset($form['checked'])) {
   $data =  $sql->query("UPDATE site.siteNewApp SET new=0 WHERE id=%1", $form['checked']);
    if(!$data) {
        $html->addNoty('error', "Ошибка сохранения: {$sql->error}");
    }
   //   $post->clear('checked');
}
if(isset($form['action'])) {
    $data =$sql->query("SELECT * FROM site.siteNewApp WHERE cast(created as date) BETWEEN STR_TO_DATE(%1,'%d.%m.%Y') and STR_TO_DATE(%2,'%d.%m.%Y')", $form['date1'], $form['date2']);
    if($data->num_rows != 0) {
        $table = "<table class='table table-striped table-bordered'><tr><th>Время создания<th>Адрес/номер договора<th>Номер телефона<th>Имя<th>Комантарий<th>Отметить ";
        while($d = $data->fetch_assoc()) {
            if($d['new'] == 0) $color='	#86B2E4'; else $color = '';
            $table .= "<tr style='background: $color'><td>".$d['created']."<td>".$d['addr']."<td>".$d['phone']."<td>".$d['name']."<td>".$d['desc']."<td><a href='?checked=".$d['id']."&date1={$form['date1']}&date2={$form['date2']}&action' class='btn btn-primary'>Отметить</a>";
        }
        $table .="</table>";
    } 
}
?>
<?=tpl('head', ['title' => ''])?>

<div class="row"  >
	<form action='' method="POST">
                <div class="col-sm-8 col-lg-8  col-mx-8 col-md-8">
                    <table>
                        <tr>
                          
                       <td class='formT'><small>Укажите даты</small><br>
                            <?=$html->formDate('date1', $form['date1'])?>
                       <td class='formT' valign='bottom'><?=$html->formDate('date2', $form['date2'])?>
                       <td class='formT'  valign='bottom'><?=$html->formButton('Найти', 'action', 'search')?>
                
                    </table>
                    <br>
                </div>
            <div class='col-sm-3 col-lg-3  col-mx-3 col-md-3' style="display:inline">
			<div style='float: right; font-size: 20px; font-family: serif '>Новые заявки с сайта
                        </div>
                      
		</div>
    </form>
	</div>
<div class="row">
    <div class="col-sm-12">
    <?=$table?>
    </div>
</div>
<?=tpl('footer', ['provider'=> getGlobalConfigVar('BASE')['provider_name']])?>
