<?php
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();

if(!\envPHP\service\PSC::isPermitted('eq_binding_search')) {
    pageNotPermittedAction();
}


//Блок для обработки формы
$form = [
        'ip'=>'',
        'switch'=>'',
        'port'=>'',
        'agreement'=>'',
        'id'=>'',
        'mac'=>'',
        'search'=>'',
        'binding'=>0,
        'action'=>'',
        'message'=>''
];
$ht = [
        'table'=>'',
        'message'=>'',
];

envPHP\classes\std::Request($form);

if(isset($form['message'])) {
    $ht['message']  = $form['message'];
}
if($form['action'] == 'del') {
    if(!\envPHP\service\PSC::isPermitted('eq_binding_delete')) {
        $html->addNoty('error', "Недостаточно прав для удаления привязки");
    } else {
        $test = @json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/binding/delete", ['employee' => _uid, 'binding' => $form['id']]));
        if (!$test) {
            $html->addNoty('error', "Неизвестный ответ от API");
        } elseif ($test->code == 0) {
            $html->addNoty('success', "Привязка успешно удалена");
        } else {
            $html->addNoty('error', "Ошибка при снятии привязки: {$test->errorMessage}");
        }
    }
}

if($form['search']) {
    //Построим форму для обработки запроса
    $WHERE = "b.id != 0 ";
    if ($form['binding']) $WHERE .= " and b.id = '{$form['binding']}' ";
    if ($form['ip']) $WHERE .= " and b.ip = '{$form['ip']}' ";
    if ($form['switch']) $WHERE .= " and eq.ip = '{$form['switch']}' ";
    if ($form['port']) $WHERE .= " and b.port = '{$form['port']}' ";
    if ($form['agreement']) $WHERE .= " and cl.agreement = '{$form['agreement']}' ";
    if ($form['mac']) $WHERE .= " and b.mac = '{$form['mac']}' ";
    if ($form['id']) $WHERE .= " and b.id = '{$form['id']}' ";

    $data = $sql->query("SELECT b.id
, cl.id agreeId
, cl.agreement
, CONCAT(pr.`name`, ' (',pr.price_day,'грн/день)') price 
, addr.full_addr
,b.ip
,b.port
,b.mac
,eq.ip switch
,m.`name` model 
,IF(act.time_stop is null, 'Активна', 'Заморожена') status 
FROM `eq_bindings` b 
JOIN equipment eq on eq.id = b.switch
JOIN equipment_models m  on m.id = eq.model
JOIN client_prices act on act.id = b.activation 
JOIN clients cl on cl.id = act.agreement
JOIN (SELECT h.id from addr_houses h JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")) houses on houses.id = cl.house
JOIN addr on addr.id = cl.house
JOIN bill_prices pr on pr.id = act.price
WHERE $WHERE 
");
    while ($d = $data->fetch_assoc()) {
        if($form['id'] == $d['id']) $color = "yellow"; else $color = "";
        $status = $d['status'] == 'Активна'?"<span style='color: #3e8f3e'>Активна</span>":"<span style='color: #FF0000; font-weight: bold'>Заморожена</span>";
        $ht['table'] .= "<tr style=''>
            <td>{$d['id']}</td>
            <td><a href='/abonents/detail?id={$d['agreeId']}'><b>{$d['agreement']}</b></a></td>
            <td>{$d['full_addr']}</td>
            <td>{$d['price']}</td>
            <td><a href='/equipment/edit?ip={$d['switch']}'><b>{$d['switch']}</b></a></td>
            <td>{$d['model']}</td>
            <td><b>{$d['port']}</b></td>
            <td><b>{$d['ip']}</b></td>
            <td><b>{$d['mac']}</b></td>
            <td>{$status}</td>
            <td><a target='_blank' href='/system/redirect-to-wildcore?binding_id={$d['id']}&type=to_dev_from_binding'><img src='/res/img/eye.png' style=\"height: 15px\"></a></td>
            <td><a href='#' title='Изменить привязку' onclick='editBinding({$d['id']}); return false;'><img src='/res/img/change.png' style=\"width: 15px\"></a></td>
             <td><a href='#' title='Удалить привязку' onclick='confirm(\"Уверены, что хотите удалить привязку? Данное действие приведет так же к закрытию доступа к интернету на порту\")?location.href=\"?action=del&id={$d['id']}&ip={$form['ip']}&agreement={$form['agreement']}&switch={$form['switch']}&port={$form['port']}&mac={$form['mac']}&search=1\":false;'><img src='/res/img/del.png' style=\"width: 15px\"></a></td>
             </tr>
        ";
    }
    if(!$ht['table']) {
        $html->addNoty('info', "Привязок не найдено");
    }
}
?><?=tpl('head', ['title'=>''])?>
<form action="" id="bindEditor" method="POST">
    <div class="modal fade" id="bindEditModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div style="height: 20px;">
                    <h4 class="modal-title" style="float: left">Изменение привязки</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    </div>
                </div>
                <div  id="bindPreload">
                <div class="modal-body">
                    <h3 align="center">Ожидайте, работа с API...<br><img src="/res/img/spinner-blue.gif" style="height: 64px; width: 64px; margin-top: 10px"></h3>
                </div>
                </div>
                <div id="bindForm" style="display: none;">
                <div class="modal-body" >
                        <div class="row">
                         <div class="col-sm-12" id="bindMessage" style="margin-top: 0"></div>
                        <input name="binding" value="" id="ebinding" hidden type="hidden">
                        <input name= "TOKEN_ID"  value="<?= _uid ?>" hidden type="hidden">
                        <div class="col-sm-6" style="<?= !\envPHP\service\PSC::isPermitted('eq_binding_change_mac') ? 'display: none;' : ''?>">
                            <small>MAC-адрес абонента</small>
                            <input name="mac" value="" id="emac" class="form-control" placeholder="AA:BB:CC:DD:EE:FF" pattern="^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$" required>
                        </div>
                        <div class="col-sm-6" style="<?= !\envPHP\service\PSC::isPermitted('eq_binding_change_ip') ? 'display: none;' : ''?>">
                            <small>IP абонента</small>
                            <input name="ip" value="" id="eip" class="form-control" placeholder="10.10.10.10" pattern="\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}" required>
                        </div>
                        <div class="col-sm-6" style="<?= !\envPHP\service\PSC::isPermitted('eq_binding_change_port') ? 'display: none;' : ''?>">
                            <small>Свитч</small>
                            <input name="switch" value="" id="eswitch" class="form-control" placeholder="10.10.10.10" pattern="\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}" required>
                        </div>
                        <div class="col-sm-6" style="<?= !\envPHP\service\PSC::isPermitted('eq_binding_change_port') ? 'display: none;' : ''?>">
                            <small>Порт</small>
                            <input name="port" value="" id="eport" class="form-control" placeholder="" pattern="" required>
                        </div>
                            <?php if(getGlobalConfigVar('RADIUS') && getGlobalConfigVar('RADIUS')['enabled']) { ?>
                        <div class="col-sm-6"  style="<?= !\envPHP\service\PSC::isPermitted('eq_binding_change_static') ? 'display: none;' : ''?>">
                            <small>Разрешить статический IP</small>
                            <input name="allow_static" value="1" type="checkbox" id="allow_static" class="form-control">
                        </div>
                            <?php } ?>
                        </div>
                </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отменить</button>
                    <button type="button" class="btn btn-primary" onclick="editBindingSave()">Сохранить</button>
                </div>
            </div>
        </div>
</div>
</form>
<form action="" method="GET" name="search">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Поиск привязок</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="form-horizontal form-label-left input_mask row" >
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                            <label class="control-label">IP адрес</label>
                            <input name="ip" class="form-control" value="<?=$form['ip']?>" placeholder="IP адрес абонента">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                            <label class="control-label">Договор</label>
                            <input name="agreement" class="form-control" value="<?=$form['agreement']?>" placeholder="Номер договора абонента">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                            <label class="control-label">Свитч</label>
                            <input name="switch" class="form-control" value="<?=$form['switch']?>" placeholder="IP свитча">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                            <label class="control-label">Порт</label>
                            <input name="port" class="form-control" value="<?=$form['port']?>"   placeholder="Включение на свитче">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                            <label class="control-label">MAC-адрес</label>
                            <input name="mac" class="form-control" value="<?=$form['mac']?>" placeholder="MAC-адрес абонента">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                            <label class="control-label">&nbsp </label>
                            <button type="submit" name="search" value="1" class="btn btn-primary btn-block">Поиск</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <table class='table table-striped nowrap' width="100%" id='myT' >
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Номер договора</th>
                    <th>Адрес</th>
                    <th>Прайс</th>
                    <th>Свитч</th>
                    <th>Модель</th>
                    <th>Порт</th>
                    <th>IP адрес</th>
                    <th>MAC-адрес</th>
                    <th>Состояние</th>
                    <th><img src='/res/img/eye.png' style="height: 20px"></th>
                    <th><img src='/res/img/change.png' style="width: 15px"></th>
                    <th><img src='/res/img/del.png' style="width: 15px"></th>
                </tr>
                </thead>
                <tbody>
                <?=$ht['table']?>
                </tbody>
            </table>
        </div>
    </div>
</form>
<script>
var TOKEN_ID = <?= _uid ?> ;
var bindForm =  $('#bindForm');
var bindPreload =  $('#bindPreload');
    function editBinding(id) {
        console.log("Load form for bind "+id);
        $('#bindMessage').html(' ');
        bindForm.hide();
        bindPreload.show();
        $('#bindEditModal').modal('show');
        $.ajax({
            "url": "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>/v2/private/equipment/binding/" + id ,
            "method": "GET",
            "dataType": 'json',
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        }).done(function (data) {
            $("#emac").val(data.data.mac);
            $("#eip").val(data.data.ip);
            $("#eport").val(data.data.port);
            $("#eswitch").val(data.data.switch);
            $("#ebinding").val(data.data.id);
            $('#allow_static').prop("checked", data.data.allow_static);
            bindPreload.hide();
            bindForm.show();
        }).error(function (data) {
            console.log(data);
            $('#bindMessage').html("<h3 align='center' style='color: red; margin-top: 0;'>Ошибка загрузки данных, попробуйте позже</h3>");
            bindPreload.hide();
            bindForm.show();
        });
    }
    function editBindingSave() {
        console.log("Save binding edit");
        bindForm.hide();
        bindPreload.show();

        var checks = $("#allow_static").is(':checked');
        formData = {
          'mac':   $("#emac").val(),
          'ip':   $("#eip").val(),
          'port':   $("#eport").val(),
          'switch':   $("#eswitch").val(),
          'allow_static': checks ? 1 : 0 ,
        };

        $.ajax({
            "url": "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>/v2/private/equipment/binding/" +  $("#ebinding").val(),
            "method": "PUT",
            "dataType": 'json',
            "data": JSON.stringify(formData),
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        }).done(function (data) {
            alert("Привязка успешно обновлена, страница будет перезагружена");
            window.location = window.location.href;
        }).error(function (data) {
            $('#bindMessage').html("<h3 align='center'  style='color: red; margin-top: 0;'>Ошибка обновления привязки<br>("+data.error.description+")</h3>");
            bindPreload.toggle();
            bindForm.toggle();
            alert(data.error.description);
        });
    }

$(document).ready(function() {
    $('#myT').DataTable( {
        "language": {
            "lengthMenu": "Отображено _MENU_ записей на странице",
            "zeroRecords": "Записей не найдено",
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
