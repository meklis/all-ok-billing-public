<?php
$rank = 5;
$urank = 8;
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


if(!\envPHP\service\PSC::isPermitted('payment_source')) {
    pageNotPermittedAction();
}
$form = [
    'message' => '',
    'start'=>'',
    'stop'=>'',
    'group_type'=>'',
    'action' => '',
    'hidden_priviligies' => '',
    'pay_source' => [],
    'prices' => [],
];
envPHP\classes\std::Request($form);
$ht = [
    'table' => '',
    'start_default' => date("d.m.Y", time() - 604800),
    'stop_default'  => date("d.m.Y" ),
    'table_city'    => '',
    'table_sources' => '',
    'table_group'   => '',
    'hidden_priviligies' => '',
    'pay_source' => '',
    'prices' => '',
];
if($form['hidden_priviligies']) $ht['hidden_priviligies'] = "checked";
$message = $form['message'] ? $form['message'] : "";

//Choose sources
$data = $sql->query("SELECT distinct payment_type name FROM paymants ORDER BY  1 ");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['name'], $form['pay_source']) ? "SELECTED " : "";
    $ht['pay_source'] .= "<OPTION value='{$d['name']}' $sel>{$d['name']}</OPTION>";
}

//Choose prices
$data = $sql->query("SELECT distinct id, name FROM bill_prices ORDER BY  2 ");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['prices']) ? "SELECTED " : "";
    $ht['prices'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}


if($form['action'] == 'show') {
    $types = "";
    foreach ($form['pay_source'] as $type) {
        if($type) $types .= "'$type',";
    }
    $types = trim($types, ",");
    $filter_types = $types ? " and payment_type in ($types) " : "";


    $prices = "";
    foreach ($form['prices'] as $price) {
        if($price) $prices .= "'$price',";
    }
    $prices = trim($prices, ",");
    $filter_prices = $prices ? "
    JOIN (
					SELECT DISTINCT c.id client 
					FROM bill_prices bp
					JOIN client_prices pr on pr.price = bp.id 
					JOIN clients c on c.id = pr.agreement
					WHERE bp.id in ({$prices})
    ) price on price.client = c.id 
    " : "";

    $queryGroupCity = "SELECT ci.name name, sum(money)  sum
        FROM paymants p 
        JOIN clients c on p.agreement = c.id 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        {$filter_prices}
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        WHERE (cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y')) {$filter_types }  
        GROUP BY ci.name  
        ORDER BY 1";
    $queryGroupTypes = "SELECT gr.name name, sum(money)  sum
        FROM paymants p 
        JOIN clients c on p.agreement = c.id 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        {$filter_prices}
        WHERE (cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y')) {$filter_types}  
        GROUP BY gr.name  
        ORDER BY 1";
    $queryGroupSources = "SELECT  p.payment_type name, sum(money)  sum
        FROM paymants p 
        JOIN clients c on p.agreement = c.id 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        {$filter_prices}
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        WHERE (cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y')) {$filter_types} 
        GROUP BY  p.payment_type
        ORDER BY 1";
    $data = $sql->query($queryGroupSources);
    while ($d = $data->fetch_assoc()) {
        $ht['table_sources'] .= "<tr><td style='background:#FAFAFA'>{$d['name']}</td><td style='background:#FAFAFA'>{$d['sum']}</td></tr>";
    }
    $data = $sql->query($queryGroupTypes);
    while ($d = $data->fetch_assoc()) {
        $ht['table_group'] .= "<tr><td style='background:#FAFAFA'>{$d['name']}</td><td style='background:#FAFAFA'>{$d['sum']}</td></tr>";
    }
    $data = $sql->query($queryGroupCity);
    while ($d = $data->fetch_assoc()) {
        $ht['table_city'] .= "<tr><td style='background:#FAFAFA'>{$d['name']}</td><td style='background:#FAFAFA'>{$d['sum']}</td></tr>";
    }

}
echo tpl('head', ['title'=>'']);
echo <<<HTML
<form method="GET">
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Источники платежей</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="form-horizontal form-label-left input_mask row" >
                    <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                        <label class="control-label">Дата ОТ</label>
                        <input type='text' name='start' class='form-control' value="{$form['start']}" id="start_date" >
                    </div>
                    <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                        <label class="control-label">Дата ДО</label>
                        <input type='text' name='stop' class='form-control' value="{$form['stop']}"  id="stop_date"> 
                    </div>
                    <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                        <label class="control-label">Источники</label>
                        <select id="sources"  name="pay_source[]" multiple="multiple">{$ht['pay_source']}</select> 
                    </div>
                    <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                        <label class="control-label">Прайсы</label>
                        <select id="prices"  name="prices[]" multiple="multiple">{$ht['prices']}</select> 
                    </div>
                    <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                         <label class="control-label">&nbsp; </label>
                         <button class="btn btn-primary btn-block" type="submit" name="action" value="show" >Отобразить</button> 
                    </div> 
                </div>
            </div>
        </div>  
    </div>
</div>
</form>
<div class="row">
    <div class="col-sm-4 col-lg-4 col-md-4 col-xs-12">
         <div class="x_panel">
                <div class="x_title">
                    <h2>По источникам платежей</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <table class="table-bordered table table-striped">
                    <tr><th>Имя</th><th>Сумма(грн)</th></tr>
                       {$ht['table_sources']}
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
                    <table class="table-bordered table table-striped">
                    <tr><th>Имя</th><th>Сумма(грн)</th></tr>
                       {$ht['table_group']}
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
                    <table class="table-bordered table table-striped">
                    <tr><th>Имя</th><th>Сумма(грн)</th></tr>
                       {$ht['table_city']}
                    </table>
                </div>
         </div>
    </div>
</div> 
<script>
    $(function () {
        $('#start_date').datetimepicker({language: 'ru', pickTime: false, defaultDate: '{$ht['start_default']}'});
        $('#stop_date').datetimepicker({language: 'ru', pickTime: false, defaultDate: '{$ht['stop_default']}'});
    });
    $(document).ready(function() {
        $('#sources').multiselect({
        includeSelectAllOption: true,
        });
        $('#prices').multiselect({
        includeSelectAllOption: true,
        });
    });
</script>
HTML;

echo tpl('footer');
