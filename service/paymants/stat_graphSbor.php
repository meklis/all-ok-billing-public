<?php
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init(true);
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


$form = [
    'start'=>'',
    'stop'=>'',
    'group_date'=>'',
    'group_type'=>'',
];
envPHP\classes\std::Request($form);
$title = "";

$categories = [];
if($form['group_date'] == 'day') {
    $categories = daysCounter($form['start'],$form['stop']);
    if($form['group_type'] == 'city') {
        $query = "SELECT cast(time as date) date, ci.name name, sum(money)  sum
        FROM paymants p 
        JOIN clients c on p.agreement = c.id 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        WHERE (cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y'))
        GROUP BY cast(time as date), ci.name  
        ORDER BY 1";
    } elseif ($form['group_type'] == 'group') {
        $query = "SELECT cast(time as date) date, gr.name name, sum(money)  sum
        FROM paymants p 
        JOIN clients c on p.agreement = c.id 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        WHERE (cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y'))
        GROUP BY cast(time as date), gr.name  
        ORDER BY 1";
    } else {
        $query = "SELECT cast(time as date) date,   p.payment_type  name, sum(money)  sum
        FROM paymants p 
        JOIN clients c on p.agreement = c.id 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        WHERE (cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y'))
        GROUP BY cast(time as date), p.payment_type
        ORDER BY 1";
    }
} else {
    $categories = monthCounter($form['start'],$form['stop']);
    if($form['group_type'] == 'city') {
        $query = "SELECT SUBSTRING(cast(time as date),1,7) date, ci.name name, sum(money)  sum
        FROM paymants p 
        JOIN clients c on p.agreement = c.id 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        WHERE (cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y'))
        GROUP BY SUBSTRING(cast(time as date),1,7), ci.name  
        ORDER BY 1";
    }  elseif ($form['group_type'] == 'group') {
        $query = "SELECT SUBSTRING(cast(time as date),1,7) date, gr.name name, sum(money)  sum
        FROM paymants p 
        JOIN clients c on p.agreement = c.id 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        WHERE (cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y'))
        GROUP BY SUBSTRING(cast(time as date),1,7), gr.name  
        ORDER BY 1";
    } else {
        $query = "SELECT SUBSTRING(cast(time as date),1,7) date,  p.payment_type  name, sum(money)  sum
        FROM paymants p 
        JOIN clients c on p.agreement = c.id 
        JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets st  on st.id = h.street
        JOIN addr_cities ci on ci.id = st.city
        LEFT JOIN addr_groups gr on gr.id = h.group_id
        WHERE (cast(time as date) BETWEEN STR_TO_DATE('{$form['start']}','%d.%m.%Y')  and STR_TO_DATE('{$form['stop']}','%d.%m.%Y'))
        GROUP BY SUBSTRING(cast(time as date),1,7), p.payment_type
        ORDER BY 1";
    }
}

$data = $sql->query($query);
$series_number_counter = 0;
$series_number = [];
$series = [];

$dataByDate = [];
while ($d = $data->fetch_assoc()) {
  if(!isset($series_number[$d['name']])) $series_number[$d['name']] = $series_number_counter++;
  $dataByDate[$d['date']][$d['name']] = $d;
}


foreach ($series_number as $ser_name => $ser_id) {
    foreach ($categories as $date) {
            $series[$ser_id]['name'] = $ser_name;
            $value = isset($dataByDate[$date][$ser_name]) ? $dataByDate[$date][$ser_name]['sum']: 0;
            $series[$ser_id]['data'][] = $value;
        }
}

$jSeries = json_encode($series, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE );
$jCategories = json_encode($categories,JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE );
echo <<<HTML
$('#payment_graph').highcharts({
        chart: {
            type: 'spline', 
 renderTo: 'chart', 
    defaultSeriesType: 'areaspline'
        },
        title: {
            text: 'Сбор за период {$form['start']} - {$form['stop']}'
        },
        subtitle: {
            text: ' '
        },
        xAxis: {
            type: 'datetime',
            dateTimeLabelFormats: {
                day: '%d:%m:%Y'
            },
            categories: {$jCategories}
        },
        yAxis: {
            title: {
                text: 'Сумма(грн)'
            } 
        },   
        tooltip: {
            shared: true,
            crosshairs: true
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: false
            },
            spline: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        series: {$jSeries}
    });
HTML;
