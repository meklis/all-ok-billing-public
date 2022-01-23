<?php 
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();


//Блок для обработки формы
$form = [
        'agreement'=>'',
        'phone'=>'',
    'id' => 0,
    'search' => false,
];
$ht = [
        'table'=>'',
        'message'=>'',
];

envPHP\classes\std::Request($form);

if(isset($form['message'])) {
    $ht['message']  = $form['message'];
}
if($form['search']) {
    //Построим форму для обработки запроса
    $WHERE = "b.id != 0 and d.status = 'BINDED' ";
    if ($form['id']) $WHERE .= " and b.id = '{$form['id']}' ";
    if ($form['agreement']) $WHERE .= " and c.agreement = '{$form['agreement']}' ";
    if ($form['phone']) $WHERE .= " and u.phone like '%{$form['phone']}%'";
    $data = $sql->query("SELECT b.id, b.created_at, b.active, d.device_uid, u.phone, c.agreement, c.id agreement_id, a.full_addr, d.entrance
FROM omo_device_bindings b 
JOIN omo_devices d on d.id = b.device_id
JOIN omo_users u on u.id = b.user_id
JOIN omo_agreement_bindings oab on u.id = oab.user_id
LEFT JOIN clients c on c.id = oab.agreement_id
LEFT JOIN addr a on a.id = d.house 
WHERE $WHERE
ORDER BY b.id desc 
");
    while ($d = $data->fetch_assoc()) {
        if($form['id'] == $d['id']) $color = "yellow"; else $color = "";
        $status = $d['active'] == 'YES'?"<span style='color: #3e8f3e'>Активна</span>":"<span style='color: #FF0000; font-weight: bold'>Приостановлена</span>";
        $ht['table'] .= "<tr style=''>
            <td>{$d['id']}</td>
            <td>{$d['created_at']}</td>
            <td><a href='/abonents/detail?id={$d['agreement_id']}'><b>{$d['agreement']}</b></a></td>
            <td><b>{$d['phone']}</td>
            <td>{$d['full_addr']}, под. {$d['entrance']}</td>
            <td>{$d['device_uid']}</td> 
            <td>{$status}</td>
            </tr>
        ";
    }
    if(!$ht['table']) {
        $html->addNoty('info', "Привязки к OMO");
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
                        <div class="col-sm-6">
                            <small>MAC-адрес абонента</small>
                            <input name="mac" value="" id="emac" class="form-control" placeholder="AA:BB:CC:DD:EE:FF" pattern="^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$" required>
                        </div>
                        <div class="col-sm-6">
                            <small>IP абонента</small>
                            <input name="ip" value="" id="eip" class="form-control" placeholder="10.10.10.10" pattern="\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}" required>
                        </div>
                        <div class="col-sm-6">
                            <small>Свитч</small>
                            <input name="switch" value="" id="eswitch" class="form-control" placeholder="10.10.10.10" pattern="\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}" required>
                        </div>
                        <div class="col-sm-6">
                            <small>Порт</small>
                            <input name="port" value="" id="eport" class="form-control" placeholder="" pattern="" required>
                        </div>
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
                            <label class="control-label">Номер договора</label>
                            <input name="agreement" class="form-control" value="<?=$form['agreement']?>" placeholder="Номер договора">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                            <label class="control-label">Номер телефона</label>
                            <input name="phone" class="form-control" value="<?=$form['phone']?>" placeholder="+380634190768">
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
                    <th>Создана</th>
                    <th>Договор</th>
                    <th>Номер телефона</th>
                    <th>Адрес устройства</th>
                    <th>ID устройства</th>
                    <th>Статус привязки</th>
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
