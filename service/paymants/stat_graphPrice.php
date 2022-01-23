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

$result = [];
$groups = [];
$group_number = 0;
if($form['group_date'] == 'day') {
    $days = daysCounter($form['start'],$form['stop']);
    if($form['group_type'] == 'city') {
        foreach ($days as $date) {
            $data = $sql->query("
                    SELECT  sum(price_day) sum, ci.name  
                    FROM client_prices p 
                    JOIN clients c on c.id = p.agreement 
                    JOIN bill_prices b on b.id = p.price
                    JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
                    JOIN addr_streets s on s.id = h.street
                    JOIN addr_cities ci on ci.id = s.city
                    LEFT JOIN addr_groups g on g.id = h.group_id 
                    WHERE '$date' BETWEEN cast(time_start as date) and cast(IFNULL(time_stop,NOW()) as date) 
                    GROUP BY ci.name 
                    ORDER BY 2");
            while ($d = $data->fetch_assoc()) {
                $result[$date][$d['name']] = $d['sum'] ? $d['sum'] : 0.0;
                if(!isset($groups[$d['name']])) $groups[$d['name']] = $group_number++;
            }
        }
    } else {
        foreach ($days as $date) {
            $data = $sql->query("
                    SELECT  sum(price_day) sum, g.name  
                    FROM client_prices p 
                    JOIN clients c on c.id = p.agreement 
                    JOIN bill_prices b on b.id = p.price
                    JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
                    JOIN addr_streets s on s.id = h.street
                    JOIN addr_cities ci on ci.id = s.city
                    LEFT JOIN addr_groups g on g.id = h.group_id 
                    WHERE '$date' BETWEEN cast(time_start as date) and cast(IFNULL(time_stop,NOW()) as date) 
                    GROUP BY g.name 
                    ORDER BY 2");
            while ($d = $data->fetch_assoc()) {
                $result[$date][$d['name'] ? $d['name'] : "Unknown" ] = $d['sum'] ? $d['sum'] : 0.0;
                if(!isset($groups[$d['name']])) $groups[$d['name']] = $group_number++;
            }
        }
    }
} else {
    $days = daysCounter($form['start'],$form['stop']);
    if($form['group_type'] == 'city') {
        foreach ($days as $date) {
            $data = $sql->query("
                    SELECT  sum(price_day) sum, ci.name  
                    FROM client_prices p 
                    JOIN clients c on c.id = p.agreement 
                    JOIN bill_prices b on b.id = p.price
                    JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
                    JOIN addr_streets s on s.id = h.street
                    JOIN addr_cities ci on ci.id = s.city
                    LEFT JOIN addr_groups g on g.id = h.group_id 
                    WHERE '$date' BETWEEN cast(time_start as date) and cast(IFNULL(time_stop,NOW()) as date) 
                    GROUP BY ci.name 
                    ORDER BY 2");
            while ($d = $data->fetch_assoc()) {
                $result[$date][$d['name']] = $d['sum'] ? $d['sum'] : 0.0;
                if(!isset($groups[$d['name']])) $groups[$d['name']] = $group_number++;
            }
        }
    } else {
        foreach ($days as $date) {
            $data = $sql->query("
                    SELECT  sum(price_day) sum, g.name  
                    FROM client_prices p 
                    JOIN clients c on c.id = p.agreement 
                    JOIN bill_prices b on b.id = p.price
                    JOIN addr_houses h on h.id = c.house and h.group_id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups()).")
                    JOIN addr_streets s on s.id = h.street
                    JOIN addr_cities ci on ci.id = s.city
                    LEFT JOIN addr_groups g on g.id = h.group_id 
                    WHERE '$date' BETWEEN cast(time_start as date) and cast(IFNULL(time_stop,NOW()) as date) 
                    GROUP BY g.name 
                    ORDER BY 2");
            while ($d = $data->fetch_assoc()) {
                $result[$date][$d['name'] ? $d['name'] : "Unknown" ] = $d['sum'] ? $d['sum'] : 0.0;
                if(!isset($groups[$d['name']])) $groups[$d['name']] = $group_number++;
            }
        }
    }
}

$categories = [];
foreach ($groups as $group_name => $group_id) {
    if ($form['group_date'] == 'day') {
        $categories = daysCounter($form['start'], $form['stop']);
        foreach ($categories as $date) {
            $series[$group_id]['name'] = $group_name;
            $series[$group_id]['data'][] = round(isset($result[$date][$group_name]) ? $result[$date][$group_name] : 0, 2);
        }
    } else {
        $resultGrouped = [];
        foreach ($result as $day => $groups_values) {
            $day = substr($day, 0, 7);
            foreach ($groups_values as $grp_name => $value) {
                if (!isset($resultGrouped[$day][$grp_name])) $resultGrouped[$day][$grp_name] = 0;
                    $resultGrouped[$day][$grp_name] += $value;
            }
        }
        $categories = monthCounter($form['start'], $form['stop']);
        foreach ($categories as $date) {
            $series[$group_id]['name'] = $group_name;
            $series[$group_id]['data'][] = round(isset($resultGrouped[$date][$group_name]) ? $resultGrouped[$date][$group_name] : 0,2);
        }
    }
}



$jSeries = json_encode($series, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE );
$jCategories = json_encode($categories,JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE );
echo <<<HTML
$('#price_graph').highcharts({
        chart: {
            type: 'spline', 
 renderTo: 'chart', 
    defaultSeriesType: 'areaspline'
        },
        title: {
            text: 'Прайс за период {$form['start']} - {$form['stop']}'
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
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: true
            },
            spline: {
                dataLabels: {
                    enabled: true
                }
            }
        }, 
        tooltip: {
            shared: true,
            crosshairs: true
        },
        series: {$jSeries}
    });
HTML;
