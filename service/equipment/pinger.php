<?php
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();

if(!\envPHP\service\PSC::isPermitted('eq_pinger')) {
    pageNotPermittedAction();
}
$ht  = [
    'data' => '',
    'status' => '',
    'status_by_groups' => '',
];

$data  = $sql->query("
SELECT 
gr.`id` `group_id`,
gr.`name` `group_name`,
gr.`description` `group_description`,
eq.id id,
eq.ip, 
eq.mac, 
mo.name model, 
concat('г. ', ci.`name`,', ',st.name , ', д.', ho.name, ', под. ', eq.entrance ) addr,
if(eq.ping > 0, 1, 0) status ,
eq.last_ping 
FROM `equipment` eq
JOIN equipment_models mo on mo.id = eq.model
JOIN equipment_access ac on ac.id = eq.access
JOIN equipment_group gr on gr.id = eq.`group`
JOIN addr_houses ho on ho.id = eq.house and ho.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
JOIN addr_streets st on st.id = ho.street
JOIN addr_cities ci on ci.id = st.city
ORDER BY group_name, status , addr
");

$DATA = [];
$color_summary = '';
$count = 0;
$count_down = 0;
while ( $d = $data->fetch_assoc() ) {
	$count++;
    $down = 0;
    if(!isset($DATA[$d['group_id']]['count_down'])) {
        $DATA[$d['group_id']]['count_down'] = 0;
    }
    if(!isset($DATA[$d['group_id']]['count_all'])) {
        $DATA[$d['group_id']]['count_all'] = 0;
    }
    $DATA[$d['group_id']]['count_all']++;
    if(!$d['status']) {
		$color="#B40404";
        $color_summary="#B40404";
		$status = "Лежит";
        $DATA[$d['group_id']]['count_down']++;
		$count_down++;
	} else {
		$color = "";
		$status = "Работает";
	}
    $DATA[$d['group_id']]['hosts'][] = $d;
    $DATA[$d['group_id']]['group_id'] = $d['group_id'];
    $DATA[$d['group_id']]['group_name'] = $d['group_name'];
    $DATA[$d['group_id']]['description'] = $d['group_description'];
}
$count_groups = count($DATA);
foreach ($DATA as $group_id=>$group) {
    $style = 'background: #F3F9FF ; border: 1px solid #2A3F54;  ';
    if($group['count_down']) {
        $style = 'background: #FFD2D2; border: 1px solid darkred';
    }
    $hosts = "";
    foreach ($group['hosts'] as $num=>$host) {
        if(!$host['status']) {
            $styleHost = 'background: #FFD2D2; border-bottom: 1px solid darkred;  padding: 4px; vertical-align: middle';
        } else {
            if( ($num % 2)  === 0) {
                $styleHost = 'background: #F0F0F0 ; border-bottom: 1px solid #2A3F54; padding: 4px; vertical-align: middle';
            } else {
                $styleHost = 'background: #FFF; border-bottom: 1px solid #2A3F54; padding: 4px; vertical-align: middle';
            }
        }
        $hosts .= "<tr>
                <td style='$styleHost'>{$host['ip']}</td>
                <td  style='$styleHost'>{$host['model']}</td> 
                <td  style='$styleHost'>{$host['addr']}</td>
                <td  style='$styleHost'>
                    <a href='".conf('BASE.wildcore')."/info/device?ip={$host['ip']}' target='_blank'><i class='fa fa-table' style=' font-size: 24px; margin-left: 5px'></i></a>
                    <a href='/equipment/show?id={$host['id']}'><i class='fa fa-info-circle' style=' font-size: 24px;  margin-left: 5px'></i></a>
                    <a href='/equipment/edit?id={$host['id']}'><i class='fa fa-edit' style=' font-size: 24px; margin-left: 5px; margin-right: 5px'></i></a>
                </td>
                </tr>
                ";
    }

    $ht['data'] .= "
        <div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>
           <button style='$style' data-toggle='collapse' aria-expanded='false' class='btn-block btn btn-default' aria-controls='#group-{$group_id}' data-target='#group-detailed-{$group_id}' >
               <div class='row' style='word-wrap: normal;'>
                    <div class='col-xs-9 col-sm-10 col-sm-10 col-lg-10'  style='word-wrap: normal;'>
                        <h4 style='word-wrap: normal;  overflow-wrap: normal; '>{$group['group_name']}</h4>
                    </div>
                    <div class='col-xs-3 col-sm-2 col-sm-2 col-lg-2'>
                        <div style='float: right'>Всего: <b>{$group['count_all']}</b></div><br>
                        <div style='float: right'>Лежит: <b>{$group['count_down']}</b></div>
                    </div>
                </div>   
           </button>
           <div class='group-detailed collapse out' style='$style' id='group-detailed-{$group_id}' aria-expanded='true'> 
               <div class='row p-o'>
                    <div class='col-xs-12 col-sm-12 col-sm-12 col-lg-12 p-o'>
                        <div class='table-responsive-light ' style='width: 100%; border-collapse: collapse; '>
                            <table class='table  table-striped table-condensed table-sm' style='width: 100%; font-size: 95%; margin: 0 !important; '>
                                <thead>
                                    <tr  style='background: #F3F9FF !important;'>
                                        <th  style='border: 0; border-bottom: 1px solid black;background: #F3F9FF  !important; color: black; font-weight: bold'>IP</th>
                                        <th  style='border: 0; border-bottom: 1px solid black;background: #F3F9FF  !important;; color: black; font-weight: bold'>Модель</th> 
                                        <th  style='border: 0; border-bottom: 1px solid black;background: #F3F9FF  !important;; color: black; font-weight: bold'>Адрес</th>
                                        <th  style='border: 0; border-bottom: 1px solid black;background: #F3F9FF  !important;; color: black; font-weight: bold'>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$hosts}                                
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
           </div>  
        </div>  
";
}
?>
<?=tpl('head', ['title'=>''])?>
<div class="row">
    <div class="col-lg-1 col-sm-12 col-xs-12 col-md-12"></div>
        <div class="col-lg-10 col-sm-12 col-xs-12 col-md-12">
            <form>
                <div class="row">
                    <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3" style='margin-bottom: 5px'>
                        <small>Информация по IP</small><br>
                            <input name="ip" class="form-control" style="font-size: 24px; height: 40px" id="dev-ip">
                        <div id="dev-not-found" style="display: none; color: darkred">IP не найден, проверьте</div>
                    </div>
                    <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3"  style='margin-bottom: 5px'>  <br>
                        <button onclick="loadIpInfo('switcher'); return false;" class="btn btn-primary"><i class='fa fa-table' style='   margin: 5px'></i></button>
                        <button onclick="loadIpInfo('info'); return false;" class="btn btn-primary"><i class='fa fa-info-circle' style='    margin: 5px'></i></button>
                        <button onclick="loadIpInfo('edit'); return false;" class="btn btn-primary"><i class='fa fa-edit' style='   margin: 5px; '></i></button>
                    </div>
                    <div class="col-xs-12 hidden-lg hidden-md hidden-sm">
                    <hr class="">
                    </div>
                    <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3"  style='margin-bottom: 5px'>
                        <div class="x_panel" style="background: #F3F9FF ; border-color: #2A3F54; border-radius: 3px">
                            <h3 align="center" style="margin: 0">
                                <small>Лежит / Всего</small><br>
                                <?=$count_down?> / <?=$count?>
                            </h3>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3" >
                        <div class="x_panel" style="background: #F3F9FF ; border-color: #2A3F54; border-radius: 3px">
                            <h3 align="center" style="margin: 0">
                                <small>Кол. групп</small><br>
                                <?=$count_groups?>
                            </h3>
                        </div>
                    </div>
                </div>
            </form>
            <div class='row'>
                <?=$ht['data']?>
            </div>
    </div>
</div>
    <style>
        .group-detailed {
            margin-top: -8px;
            margin-bottom: 10px;
            border-bottom: 0 !important;
        }
        .group-btn {
            width: 100%;
            padding: 5px;
        }
    </style>
<script>
    function loadIpInfo(method) {
        var settings = {
            "url": "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>/v2/private/equipment/device/find?ip=" + $('#dev-ip').val(),
            "method": "GET",
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        };
        $.ajax(settings).success(function (response) {
            console.log(response.data);
            if(response.data.length  === 0) {
                $('#dev-not-found').show();
                return false;
            } else {
                $('#dev-not-found').hide();
            }
            var id = response.data[0].id;
            switch (method) {
                case 'switcher':
                    document.location.href = '<?=conf('BASE.wildcore')?>/info/device?ip=' + $('#dev-ip').val();
                    break;
                case 'info':
                    document.location.href = '/equipment/show?id=' + id;
                    break;
                case 'edit':
                    document.location.href = '/equipment/edit?id=' + id;
                    break;
                default:
                    alert('Action not found');
            }
        });

    }
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
