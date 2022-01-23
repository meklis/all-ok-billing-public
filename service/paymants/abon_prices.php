<?php
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");


function daysCounter($start,$stop, $format="Y-m-d") {
    $countDays = round((strtotime($stop) - strtotime($start))/86400);
    $DATES = [];
    for($day=0;$day<=$countDays;$day++) {
        $DATES[] = date($format, strtotime("+$day days", strtotime($start)));
    }
    return $DATES;
}
function monthCounter($start,$stop, $format="Y-m") {
    $startTime = new DateTime($start);
    $stopTime = new DateTime($stop);
    $i = 0;
    $DATES = [];
    while ($i++ < 300 && $startTime->getTimestamp() <= $stopTime->getTimestamp()) {
        $DATES[] = $startTime->format("Y-m");
        $startTime->modify("+1 month");
    }
    return $DATES;
}
init();


if(!\envPHP\service\PSC::isPermitted('payment_summary_price')) {
    pageNotPermittedAction();
}


$form = [
    'message' => '',
    'date'=>'',
    'prices'=>[],
    'action' => '',
];
envPHP\classes\std::Request($form);
$ht = [
    'date_default' => date("d.m.Y", time() - 604800),
    'table_city'    => '',
    'table_prices' => '',
    'table_group'   => '',
    'prices' => '',
];
$message = $form['message'] ? $form['message'] : "";

//Choose sources
$data = $sql->query("SELECT distinct id, name FROM bill_prices ORDER BY  1 ");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['prices']) ? "SELECTED " : "";
    $ht['prices'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}
if($form['action'] == 'show') {
    $prices = "";
    foreach ($form['prices'] as $price) {
        if($price) $prices .= "'$price',";
    }
    $prices = trim($prices, ",");
    $filter_prices = $prices ? " and b.id in ($prices) " : "";
    $queryCities = "SELECT ci.name, count(*) count, sum(b.price_day) sum 
        FROM clients c 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        LEFT JOIN client_prices p on p.agreement = c.id 
        LEFT JOIN bill_prices b on b.id = p.price
        WHERE STR_TO_DATE('{$form['date']}','%d.%m.%Y') BETWEEN cast(p.time_start as date)  and  cast(IFNULL(p.time_stop, NOW()) as date)  $filter_prices and c.status != 'DISABLED'
        GROUP BY ci.name 
        ORDER BY 1 ";
    $queryGroups = "SELECT gr.name, count(*) count, sum(b.price_day) sum 
        FROM clients c 
        JOIN addr_houses h on h.id = c.house  in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        LEFT JOIN client_prices p on p.agreement = c.id 
        LEFT JOIN bill_prices b on b.id = p.price
        WHERE STR_TO_DATE('{$form['date']}','%d.%m.%Y') BETWEEN cast(p.time_start as date)  and  cast(IFNULL(p.time_stop, NOW()) as date)   $filter_prices and c.status != 'DISABLED'
        GROUP BY gr.name
        ORDER BY 1 ";
    $queryPrices = "SELECT b.name, count(*) count, sum(b.price_day) sum 
        FROM clients c 
        JOIN addr_houses h on h.id = c.house  in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        LEFT JOIN client_prices p on p.agreement = c.id 
        LEFT JOIN bill_prices b on b.id = p.price
        WHERE STR_TO_DATE('{$form['date']}','%d.%m.%Y') BETWEEN cast(p.time_start as date)  and  cast(IFNULL(p.time_stop, NOW()) as date)   $filter_prices and c.status != 'DISABLED'
        GROUP BY b.name
        ORDER BY 1 ";
    $data = $sql->query($queryPrices);
    while ($d = $data->fetch_assoc()) {
        $ht['table_prices'] .= "<tr><td style='background:#FAFAFA'>{$d['name']}</td><td style='background:#FAFAFA'>{$d['count']}</td><td style='background:#FAFAFA'>{$d['sum']}</td></tr>";
    }
    $data = $sql->query($queryGroups);
    while ($d = $data->fetch_assoc()) {
        $ht['table_group'] .= "<tr><td style='background:#FAFAFA'>{$d['name']}</td><td style='background:#FAFAFA'>{$d['count']}</td><td style='background:#FAFAFA'>{$d['sum']}</td></tr>";
    }
    $data = $sql->query($queryCities);
    while ($d = $data->fetch_assoc()) {
        $ht['table_city'] .= "<tr><td style='background:#FAFAFA'>{$d['name']}</td><td style='background:#FAFAFA'>{$d['count']}</td><td style='background:#FAFAFA'>{$d['sum']}</td></tr>";
    }

}

echo tpl('head', ['title'=>'']);
?>

<form method="POST">
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Сводная по прайсу</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="form-horizontal form-label-left input_mask row" >
                        <div class=" col-xs-6 col-sm-3 col-md-3 col-lg-2 form-group">
                            <label class="control-label">Дата</label>
                            <input type='text' name='date' class='form-control' value="<?=$form['date']?>" id="date" >
                        </div>
                        <div class=" col-xs-6 col-sm-3 col-md-3 col-lg-2 form-group">
                            <label class="control-label">Прайсы</label>
                            <select id="prices"  name="prices[]" multiple="multiple"><?=$ht['prices']?></select>
                        </div>
                        <div class=" col-xs-12  col-sm-3  col-md-3  col-lg-2 form-group">
                            <label class="control-label">&nbsp; </label>
                            <button class="btn btn-primary btn-block" name='action' value="show">Поиск</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4 col-lg-4 col-md-4 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>По прайсам</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <table class=" table table-striped"><thead>
                        <tr><th>Имя</th><th>Количество абонентов<th>Прайс(грн)</th></tr></thead><tbody>
                        <?=$ht['table_prices']?></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-lg-4 col-md-4 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>По группам</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <table class=" table table-striped"><thead>
                        <tr><th>Имя</th><th>Количество абонентов<th>Прайс(грн)</th></tr></thead><tbody>
                        <?=$ht['table_group']?></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-lg-4 col-md-4 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>По городам</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <table class=" table table-striped"><thead>
                        <tr><th>Имя</th><th>Количество абонентов<th>Прайс(грн)</th></tr></thead><tbody>
                        <?=$ht['table_city']?></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    $(function () {
        $('#date').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?=$ht['date_default']?>'});
    });
    $(document).ready(function() {
        $('#prices').multiselect({
        includeSelectAllOption: true,
        });
    });
</script>
<?=tpl('footer')?>;
