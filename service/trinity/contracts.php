<?php 
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();


if(!\envPHP\service\PSC::isPermitted('trinity_contracts')) {
    pageNotPermittedAction();
}


//Блок для обработки формы
$form = [
        'agreement'=>'',
        'mac'=>'',
        'uuid'=>'',
        'binding'=>0,
        'action'=>'',
        'message'=>''
];
$ht = [
        'table'=>'',
];

envPHP\classes\std::Request($form);
$wrapContracts = getGlobalConfigVar('TRINITY')['services_associate'];
$wrapper = function ($trinity_price) use ($wrapContracts) {
   foreach ($wrapContracts as $name => $price) {
       if($trinity_price == $price['trinity']) {
           return [$name, $price['local']];
       }
   }
};
    $data = $sql->query("SELECT id, subscr_id, contract_trinity, devices_count, contract_date , subscr_price
FROM `trinity_contracts` 
order by contract_date desc 
");
    while ($d = $data->fetch_assoc()) {
        list($localName, $localPriceId) = $wrapper($d['subscr_id']);
        $ht['table'] .= <<<HTML
 <tr style=''>
             <td>{$d['id']}</td>
             <td>{$localName}({$d['subscr_id']})</td>
             <td>{$d['subscr_price']}</td>
             <td>{$d['contract_trinity']}</td>
             <td>{$d['devices_count']}</td>
             <td>{$d['contract_date']}</td>
 </tr>
HTML;

    }
if(!$ht['table']) {
    $html->addNoty('info', "Привязок не найдено");
}
?><?=tpl('head', ['title'=>''])?>
<form action="" method="GET" name="search">
    <div class="row">
        <div class="col-sm-6 col-md-8 col-xs-12 col-lg-8"></div>
        <div class="col-sm-6 col-md-4 col-xs-12 col-lg-4">
            <div class="x_panel">
                <div class="x_title">
                    <h2 align="right">Просмотр контрактов Trinity</h2>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-xs-12">
            <table class='table table-striped nowrap' width="100%" id='myT' >
                <thead>
                <tr>
                    <th>ID (локальный контракт)</th>
                    <th>Подписка</th>
                    <th>Прайс (грн/мес)</th>
                    <th>Контракт тринити</th>
                    <th>Количество устройств</th>
                    <th>Дата</th>
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
        $.getJSON("/binding/get?TOKEN_ID="+TOKEN_ID+"&binding="+id, function(json) {
            if(json.code == 0) {
                val = json.data;
                $("#emac").val(val.mac);
                $("#eip").val(val.ip);
                $("#eport").val(val.port);
                $("#eswitch").val(val.switch);
                $("#ebinding").val(val.id);
            } else {
                $('#bindMessage').html("<h3 align='center' style='color: red; margin-top: 0;'>Ошибка загрузки данных, попробуйте позже</h3>");
            }
            bindPreload.hide();
            bindForm.show();
        });
    }
    function editBindingSave() {
        console.log("Save binding edit");
        bindForm.hide();
        bindPreload.show();
        newBind = $('#bindEditor').serialize();
        $.post("/binding/edit", newBind, function(data, textStatus) {
            if(data.code != 0) {
                $('#bindMessage').html("<h3 align='center'  style='color: red; margin-top: 0;'>Ошибка обновления привязки<br>("+data.errorMessage+")</h3>");
            } else {
                alert("Привязка успешно обновлена, страница будет перезагружена");
                window.location = window.location.href;
            }
            bindPreload.toggle();
            bindForm.toggle();
        }, "json");
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
