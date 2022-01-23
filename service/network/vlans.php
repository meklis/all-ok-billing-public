<?php
$rank = 20;
$table = "<center><h3>По Вашему запросу пользователей не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if (isset($_REQUEST['form'])) foreach ($_REQUEST as $k => $v) $form[$k] = $v;

if (!isset($form['page'])) $form['page'] = 1;

$data = $sql->query("SELECT vl.id, vl.vlan, vl.name, k.`name` kind, k.description , IFNULL(c_eq,0) c_eq, IFNULL(c_nt,0) c_nt
FROM `eq_vlans` vl
JOIN eq_kinds k on k.id = vl.type 
LEFT JOIN (SELECT vlan, count(*) c_eq FROM eq_vlan_equipment GROUP BY vlan) eq on eq.vlan = vl.id 
LEFT JOIN (SELECT vlan, count(*) c_nt FROM eq_vlan_neth GROUP BY vlan) nt on nt.vlan = vl.id 
order by 2;
");
if ($data->num_rows != 0) {
    $table = "<table class='table table-striped' id='tbl-sort'>
            <thead>
            <tr>
                <th>ID</th>
                <th>Vlan</th>
                <th>VlanName</th>
                <th>KindName</th>
                <th>KindDescr</th>
                <th>CountDevices</th>
                <th>CountNetworks</th>
                <th><img src='/res/img/change.png' width=24></th>
            </tr>
            </thead>
            <tbody>
            ";
    while ($d = $data->fetch_assoc()) {
        $table .= "
           <tr>
               <td>" . $d['id'] . "</td>
               <td>" . $d['vlan'] . "</td>
               <td>" . $d['name'] . "</td>
               <td>" . $d['kind'] . "</td>
               <td>" . $d['description'] . "</td>
               <td>" . $d['c_eq'] . "</td>
               <td>" . $d['c_nt'] . "</td>
               <td><a href = 'vlan_edit?id=" . $d['id'] . "' ><img src='/res/img/change.png' width=24></a></td>
           </tr>
           ";
    }
    $table .= "</tbody></table>";
}

?><?= tpl('head', ['title' => '']) ?>
<form action="" method="post">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Управление вланами</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="col-lg-8 col-md-10 col-sm-10 col-xs-12">
                        <div class="table-responsive-light">
                            <?= $table ?>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-2 col-sm-2 col-xs-12">
                        <a href="vlan_edit?id=0" class="btn btn-primary">Добавить новый влан</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function() {
        $('#tbl-sort').DataTable( {
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
            "order": [[ 0, 'desc' ]],
            "searching": true,
            "ordering": true,
            "scrollX": true,
            "bLengthChange" : true,
            "lengthMenu": [[30, 50, 100, -1], [30, 50, 100, "Все"]]
        });
    });
</script>
<?= tpl('footer') ?>
