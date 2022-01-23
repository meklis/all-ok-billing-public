<?php
$rank = 5;
ini_set('pcre.backtrack_limit', 500000000);
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();
if(isset($_COOKIE['last_page'])) $page = $_COOKIE['last_page']; else $page ='';

if(!\envPHP\service\PSC::isPermitted('customer_purpose_of_payment')) {
    pageNotPermittedAction();
}

$form = [
   'search'=>'',
   'groups' => [],
   'prices' => [],
   'action' => '',
   'period' => '',
   'choosed' => [],
   'start' => date("d.m.Y", time() - 2592000),
   'stop' => date("d.m.Y", time()),
];
$ht = [
   'groups' => '',
   'prices' => '',
   'table' => '',
   'start' => date("d.m.Y", time() - 2592000),
   'stop' => date("d.m.Y", time() ),
];


envPHP\classes\std::Request($form);


$data = $sql->query("SELECT id, name FROM addr_groups WHERE id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).") order by name");
$ht['groups'] .= "<OPTION value=''>Не указано</OPTION>";
while ($d = $data->fetch_assoc()) {
    $sel =  in_array($d['id'], $form['groups']) ? "SELECTED ": "";
    $ht['groups'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

$data = $sql->query("SELECT id, CONCAT(name, ' (', price_day, ')') name FROM bill_prices WHERE `show` = 1 ORDER BY name ");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['prices']) ? "SELECTED ": "";
    $ht['prices'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}


if($form['action']) {
    $qus =  implode("%", explode(' ', $form['search']));
    $qus = "%".$qus."%";
    $where = "";

    $prices = "";
    foreach ($form['prices'] as $price) {
        if($price) $prices .= "'$price',";
    }
    $prices = trim($prices, ",");
    $where .= $prices ? " and pn.id in ($prices) " : "";

    $groups = "";
    foreach ($form['groups'] as $group) {
        if($group) $groups .= "'$group',";
    }
    $groups = trim($groups, ",");
    $where .= $groups ? " and ha.group_id in ($groups) " : "";

    $data = $sql->query("SELECT s.id, 
        s.agreement, 
        s.`name`, 
        s.apartment, 
        s.entrance,
        ph.phone, 
        s.balance, 
        ha.name house, 
        sa.name street, 
        ca.name city,
        gr.name `group`,
        GROUP_CONCAT(DISTINCT pn.`name` ORDER BY pn.name, '<br>') prices,
        cp.purpose_of_payment,
        ifnull(prn.price, 0.0) price
        FROM clients s 
        JOIN addr_houses ha on ha.id = s.house and ha.group_id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")
        JOIN addr_streets sa on sa.id = ha.street
        JOIN addr_cities ca on ca.id = sa.city
        LEFT JOIN (
                SELECT a.agreement
                , sum(p.price_day * (TIMESTAMPDIFF(DAY, time_start, IFNULL(time_stop,NOW())))) price
                FROM client_prices a
                JOIN clients c on c.id = a.agreement
                JOIN bill_prices p on p.id = a.price
                WHERE cast(a.time_start as date) >= STR_TO_DATE('{$form['start']}','%d.%m.%Y') and  cast(IFNULL(a.time_stop,NOW()) as date) <= STR_TO_DATE('{$form['stop']}','%d.%m.%Y')
                GROUP BY a.agreement
        ) prn on prn.agreement = s.id 
        LEFT JOIN addr_groups gr on gr.id = ha.group_id
        LEFT JOIN client_prices pr on pr.agreement = s.id 
        LEFT JOIN bill_prices pn on pn.id = pr.price
        LEFT JOIN (SELECT agreement_id, `value` phone FROM client_contacts WHERE main = 1 and type = 'PHONE') ph on ph.agreement_id = s.id     
        LEFT JOIN (
                SELECT client_id, purpose_of_payment
                    FROM bill_prices bp
                    JOIN client_prices cp on cp.price = bp.id 
                    JOIN (
                                SELECT 
                                c.id client_id, 
                                max(p.id) price_id
                                FROM clients c 
                                JOIN client_prices p on p.agreement = c.id 
                                GROUP BY c.id 
                        ) pp on pp.price_id = cp.id 
                ) cp on cp.client_id = s.id 
        WHERE CONCAT(s.name,s.agreement,ca.`name`, sa.`name`, ha.`name`) like '$qus' $where
        GROUP BY s.agreement");
  $messages =[];
  if($data->num_rows == 0) {
      $html->addNoty('info', "По указанным параметрам ничего не найдено");
      $ht['table'] = "<h4 align='center'>По запросу ничего не найдено</h4>";
  } else {
      $ht['table'] = "<table class='table table-striped' id='myT'>
                    <thead>
                        <tr>
                            <th>Договор</th>
                            <th>Имя</th>
                            <th>Адрес</th>
                            <th>Группа</th>
                            <th>Баланс</th>
                            <th>Активные прайсы</th>
                            <th>Причина платежа</th>
                            <th>Начислено</th>
                            <th>Долг</th>
                            <th>Всего к оплате</th>
                            <th><input type='checkbox' name='checkedss' onclick='checkAll(\"search\")'>
                            </th></tr></thead><tbody>";
       while($d = $data->fetch_assoc()) {
           $href = "<a href = 'detail?id=".$d['id']."'>".$d['agreement']."</a>";
          $addr = "г. ".$d['city'].", ".$d['street'].", д.".$d['house'].", кв. <b>".$d['apartment'];
          $debt = $d['price'] - $d['balance'];
          if($debt < 0) {
              $debt = 0;
          }
          $for_pay = abs($debt) + abs($d['price']);
          if($for_pay == 0) continue;
          $debt = sprintf("%.2f", $debt);
          $for_pay = sprintf("%.2f", $for_pay);
           $d['price'] = sprintf("%.2f", $d['price']);
          $addr = "{$d['city']}, {$d['street']}, {$d['house']}, пiд.{$d['entrance']}, кв.{$d['apartment']} ";
          $ht['table'] .= "<tr>
                   <td>$href<small> ({$d['id']})</small></td>
                   <td>".$d['name']."</td>
                   <td>$addr</td>
                   <td>{$d['group']}</td>
                   <td>{$d['balance']}</td>
                   <td>{$d['prices']}</td>
                   <td>{$d['purpose_of_payment']}</td>
                   <td>{$d['price']}</td>
                   <td>{$debt}</td>
                   <td>{$for_pay}</td>
                   <td><input type='checkbox' name='choosed[]' value='{$d['id']}'></td>
                   </tr>";
          if(in_array($d['id'], $form['choosed'])) {
              $messages[] = [
                  'Призначення платежу' => $d['purpose_of_payment'],
                  'Період платежу' => $form['period'],
                  'Код виду платежу' => $d['agreement'],
                  'Нараховано' => $d['price'],
                  'Заборгованість' => $debt,
                  'Всього до оплати' => $for_pay,
                  'Имя' => $d['name'],
                  'Адреса платника' => $addr,
              ];
          }
      }
      $ht['table'] .="</tbody></table>";
  }
}
if($form['action'] == 'print') {
    $pdf = new envPHP\pdf\PdfPrinter(new \Mpdf\Mpdf([
        'tempDir' => '/tmp/php/pdf',
        'format' => [200, 297 ],
    ]));
    $properties = getGlobalConfigVar('PDF_PRINTING')['message_of_payment'];
    $pdf->setTemplate(file_get_contents($properties['path']))
        ->setVariables($properties['params'])
        ->write($messages)
        ->outputHTML();
    exit;
}

?><?=tpl('head', ['title'=>''])?>
<form name="search" method="POST">
<div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Печать квитанций</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="form-horizontal form-label-left input_mask row" >
                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                            <span style="font-size: 16px; font-weight:  500">Параметры поиска</span>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                            <label class="control-label">Группа</label>
                            <select id="groups" name='groups[]'  multiple="multiple"><?=$ht['groups']?></select>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                            <label class="control-label">Прайсы</label>
                            <select id="prices"  name="prices[]"  multiple="multiple"><?=$ht['prices']?></select>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                            <label class="control-label">Номер договора/Адрес/Имя</label>
                            <input name='search' value = '<?=$form['search']?>' class="form-control"  placeholder="Ленина 10, 15">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                            <label class="control-label">&nbsp </label>
                            <button type="submit" name="action" value="search" class="btn btn-primary btn-block">Искать</button>
                        </div>
                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                            <div class="divider-dashed"></div>
                            <span style="font-size: 16px; font-weight:  500">Период расчета начислений</span>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                            <label class="control-label">Начало</label>
                            <input class="form-control" name="start" id="start" value="<?=$form['start']?>"  >
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                            <label class="control-label">Конец</label>
                            <input class="form-control" name="stop" id="stop" value="<?=$form['stop']?>"  >
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                            <label class="control-label">Период расчета</label>
                            <input class="form-control" name="period" id="period" value="<?=$form['period']?>">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-3  form-group">
                            <label class="control-label"> &nbsp</label>
                            <button type="submit" name="action" value="print" class="btn btn-success btn-block">Сформировать квитанции</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <?=$ht['table']?>
    </div>
</div>
</form>
<script>
    $(function () {
        $('#start').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?=$ht['start']?>'});
        $('#stop').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?=$ht['stop']?>'});
    });
    $('#start').change(function() {
        $('#period').val($('#start').val()+' - '+$('#stop').val());
    });

    $('#stop').change(function() {
        $('#period').val($('#start').val()+' - '+$('#stop').val());
    });
    $(document).ready(function() {
        $('#prices').multiselect({
            includeSelectAllOption: true,
        });
        $('#groups').multiselect({
            includeSelectAllOption: true,
        });
        $('#period').val($('#start').val()+' - '+$('#stop').val());
    });
    var statusCheckbox = false;
    function checkAll(formname)
    {
        var checkboxes = new Array();
        checkboxes = document[formname].getElementsByTagName('input');
        statusCheckbox = !statusCheckbox;
        for (var i=0; i<checkboxes.length; i++)  {
            if (checkboxes[i].type == 'checkbox')   {
                checkboxes[i].checked = statusCheckbox;
            }
        }
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
            "order": [[ 3, 'asc' ]],

            "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "Все"]]
        });
    });
</script>
<?=tpl('footer')?>
