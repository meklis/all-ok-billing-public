<?php
$rank = 20;
$table = "<center><h3>По Вашему запросу ничего не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(isset($_REQUEST['form'])) foreach($_REQUEST as $k=>$v) $form[$k]=$v;

if(!isset($form['page'])) $form['page'] = 1;

$data = $sql->query("SELECT h.id
, k.name type 
, h.name 
, h.startIp
, h.stopIp
, h.gateway
, h.mask
, vl.vlan vlans 
,(SELECT count(*) FROM eq_bindings WHERE INET_ATON(ip) BETWEEN INET_ATON(startIp) and INET_ATON(stopIp)) bindings
 FROM `eq_neth` h
JOIN eq_kinds k on k.id = h.type
LEFT JOIN (SELECT GROUP_CONCAT(vl.vlan) vlan, neth FROM eq_vlan_neth n JOIN eq_vlans vl on vl.id = n.vlan GROUP BY neth) vl on vl.neth = h.id
ORDER  by 2,4
");
if($data->num_rows != 0) {
    $table = "<table class='table table-striped' id='tbl-sort'>
            <thead>
              <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Name</th>
                <th>StartIP</th>
                <th>StopIP</th>
                <th>Gateway</th>
                <th>Mask</th>
                <th>Vlan Ids</th>
                <th>count bindings</th>
                <th><img src='/res/img/change.png' width=24></th>
                </tr>
                </thead><tbody>";
    while($d = $data->fetch_assoc()) {
        $table .= "<tr>
            <td>".$d['id']."</td>
            <td>".$d['type']."</td>
            <td>".$d['name']."</td>
            <td>".$d['startIp']."</td>
            <td>".$d['stopIp']."</td>
            <td>".$d['gateway']."</td>
            <td>".$d['mask']."</td>
            <td>".$d['vlans']."</td>
            <td>".$d['bindings']."</td>
            <td><a href = 'network_edit?id=".$d['id']."' ><img src='/res/img/change.png' width=24></a></td>
            </tr>
       ";
    }
    $table .= "</tbody></table>";
}

?><?=tpl('head', ['title'=>''])?>
    <form action="" method="post">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Управление подсетями</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="col-lg-10 col-md-10 col-sm-10 col-xs-12">
                            <div class="table-responsive-light">
                                <?=$table?>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
                            <a href="network_edit?id=0" class="btn btn-primary">Добавить новую подсеть</a>
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
<?=tpl('footer')?>
