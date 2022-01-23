<?php
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();




$ht['table'] = '';
$omo = new envPHP\service\OmoLocalControl();
foreach ($omo->deviceGetList() as $d) {
    //'BINDED','ENABLED','DISABLED','NOT_BINDED','DELETED'
    $status = "";
    switch ($d['status']) {
        case 'NOT_BINDED': $color = '#F3E2A9'; $status="Не закреплен"; break;
        case 'BINDED': $color = ''; $status="Закреплен"; break;
        case 'DELETED': $color = '#BDBDBD'; $status="Удален"; break;
        default:
            $color = "";
            $status = $d['status'];
    }
    if(!$d['addr']) $d['addr'] = '<span style="color: red; font-weight: bold">Не закреплен</span>';
    if(!$d['entrance']) $d['entrance'] = '<span style="color: red; font-weight: bold">Не указан</span>';

	$edit_url = "<a href='edit?id={$d['id']}' target = '_blank'>Изменить</a>";

	$detailed = "<small>";
	if($d['floor']) {
	    $detailed .= "Этаж - <b>{$d['floor']}</b><br>";
    }
	if($d['apartment']) {
	    $detailed .= "Квартира - <b>{$d['apartment']}</b><br>";
    }
	$detailed .= "</small>";
	if(!$d['comment']) {
	    $d['comment'] = "<small><span style='color: darkgray'>Нет коментария</span></small>";
    }

	$ht['table'] .= "
<tr style='background: $color'>
   <td>{$d['id']}
   <td>{$d['created_at']}
   <td>{$d['hub_uid']}
   <td><b>{$d['device_uid']}
   <td>{$d['type']}
   <td>{$status}
   <td>{$d['addr']}
   <td>{$d['entrance']}
   <td>$detailed</td>
   <td>{$d['comment']} 
   <td>$edit_url</tr>";
}

use envPHP\classes\std;use envPHP\service\OmoLocalControl; ?><?=tpl('head', ['title'=>'Список устройств OMO'])?>

<div class='row'>
    <div class="col-sm-12 col-xs-12 col-lg-12 col-md-12">
<table class='table table-striped table-bordered' id='myT' >
	<thead>
	<tr>
		<th>ID
		<th>Добавлен
		<th>ID хаба
		<th>ID устройства
		<th>Тип устройства
		<th>Статус устройства
		<th>Адрес установки
		<th>Подьезд
        <th>Детали установки<br><small>(этаж/квартира)</small>
		<th>Коментарий
		<th>Изменить
	</thead>
	<tbody>
		<?=$ht['table']?>
	</tbody>
</table>
    </div>
</div>
<script> 
$(document).ready(function() {
    $('#myT').DataTable( {
        "language": {
            "lengthMenu": "Отображено _MENU_ записей на странице",
            "zeroRecords": "К сожалению, записей не найдено",
            "info": "Показана  страница _PAGE_ с _PAGES_",
            "infoEmpty": "Нет записей",
            "infoFiltered": "(filtered from _MAX_ total records)",
			"search": "Живой фильтр:",
			"paginate": {
				"first":      "Первая",
				"last":       "Последняя",
				"next":       "Следующая",
				"previous":   "Предыдущая"
			},
        },
        "scrollX": true,
        "scrollCollapse": true,
        "sScrollXInner": "100%",
        "xScrollXInner": "100%",
		"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "Все"]]
    });
});
</script>

<?=tpl('footer')?>