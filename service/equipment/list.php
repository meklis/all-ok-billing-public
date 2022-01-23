<?php 
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();
if(!\envPHP\service\PSC::isPermitted('eq_list')) {
    pageNotPermittedAction();
}

$ht['table'] = '';
$data  = $sql->query("SELECT eq.ip, 
eq.id id ,
eq.mac, 
mo.name model, 
concat('г.', ci.`name`,', ',st.name , ', д.', ho.name ) addr,
eq.entrance,
gr.`name` `group`,
count(distinct b.port) count, 
eq.ping,
eq.last_ping,
ac.login access
FROM `equipment` eq
JOIN equipment_models mo on mo.id = eq.model
JOIN equipment_access ac on ac.id = eq.access
JOIN equipment_group gr on gr.id = eq.`group`
JOIN addr_houses ho on ho.id = eq.house and ho.group_id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")    
JOIN addr_streets st on st.id = ho.street
JOIN addr_cities ci on ci.id = st.city
LEFT JOIN eq_bindings b on b.switch = eq.id
GROUP BY eq.ip ");
while($d = $data->fetch_assoc()) {
	if($d['ping'] <= 0) {
		$color="#FF7256";
		$status = "Лежит";
	} else {
		$color = "";
		$status = "Работает";
	}
	$href = "<a href='edit?id={$d['id']}' target = '_blank'>Изменить</a>";
	$ht['table'] .= "<tr style='background: $color'><td><b>{$d['ip']}<td>{$d['mac']}<td>{$d['model']}<td>{$d['addr']}<td>{$d['entrance']}<td>{$d['group']}<td>{$d['access']}<td>{$d['count']}<td><b>$status<td><b>{$d['last_ping']}<td>$href</tr>";
}
?><?=tpl('head', ['title'=>'Список хостов'])?>

<div class='row'>
    <div class="col-sm-12 col-xs-12 col-lg-12 col-md-12">
<table class='table table-striped' id='myT' >
	<thead>
	<tr>
		<th>IP 
		<th>MAC
		<th>Модель
		<th>Адрес
		<th>Подьезд
		<th>Группа
		<th>Логин
		<th>Кол. абонентов
		<th>Состояние
		<th>Посл. изм. состояния
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