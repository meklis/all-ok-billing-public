<?php
$rank = 5;

$message = '';
$table = "";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(!\envPHP\service\PSC::isPermitted('customer_search')) {
    pageNotPermittedAction();
}


if(isset($_COOKIE['last_page'])) $page = $_COOKIE['last_page']; else $page ='';

$form = [
   'search'=>'',
   'group_id' => [],
   'price_id' => [],
    'contact' => '',
    'action' => '',
    'agreement' => '',
    'show_disabled' => '',
];
$ht = [
  'groups' => '',
  'prices' => '',
    'table' => '',
    'table_blc' => '',
];

envPHP\classes\std::Request($form);


$data = $sql->query("SELECT id, name FROM addr_groups where id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).") order by name");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['group_id']) ? "SELECTED ": "";
    $ht['groups'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

$data = $sql->query("SELECT id, CONCAT(name, ' (', price_day, ')') name FROM bill_prices WHERE `show` = 1 ORDER BY name ");
while ($d = $data->fetch_assoc()) {
    $sel = in_array($d['id'], $form['price_id']) ? "SELECTED ": "";
    $ht['prices'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}


if($form['action']) {
    $qus =  implode("%", explode(' ', $form['search']));
    $qus = "%".$qus."%";
    $where = "";
    if($form['group_id']) {
        $elems = join(",", $form['group_id']);
        $where .= " and ha.group_id in ($elems)";
    }
    if($form['price_id']) {
        $elems = join(",", $form['price_id']);
        $where .= " and pn.id in ($elems)";
    }
    if($form['contact']) {
        $psth = dbConnPDO()->prepare("SELECT agreement_id FROM client_contacts WHERE `value` like ?");
        $psth->execute(["%{$form['contact']}%"]);
        $agree_contacts = [];
        if($psth->rowCount() > 0) {
            foreach ($psth->fetchAll() as $contact) {
                $agree_contacts[] = $contact['agreement_id'];
            }
            $where .= " and s.id in (" . join(',', $agree_contacts) . ") ";
        } else {
            $where .= " and s.id = 0";
        }

    }
    if($form['agreement']) {
        $where = " and s.agreement like '%{$form['agreement']}%'";
    }
    if(!$form['show_disabled']) {
        $where .= " and s.status = 'ENABLED'";
    }
   $data = $sql->query("SELECT s.id, 
s.agreement, 
s.entrance,
s.`name`, 
s.apartment, 
s.balance, 
ha.name house, 
sa.name street, 
ca.name city,
gr.name `group`,
GROUP_CONCAT(DISTINCT pn.`name` ORDER BY pn.name, '<br>') prices 
FROM clients s 
JOIN addr_houses ha on ha.id = s.house and ha.group_id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")
JOIN addr_streets sa on sa.id = ha.street
JOIN addr_cities ca on ca.id = sa.city
LEFT JOIN addr_groups gr on gr.id = ha.group_id
LEFT JOIN client_prices pr on pr.agreement = s.id   
LEFT JOIN bill_prices pn on pn.id = pr.price  
WHERE CONCAT(s.name,' ',ca.`name`,' ', sa.`name`,' ', ha.`name`,' ', s.apartment) like '$qus' $where
GROUP BY s.agreement 
");
  if($data->num_rows == 0) {
      $html->addNoty('info', "???? ?????????????????? ???????????????????? ?????????????? ???? ??????????????");
      $ht['table'] = "<h4 align='center'>???? ?????????????? ???????????? ???? ??????????????</h4>";
  } else {
      $ht['table'] = "
        <table class='table table-bordered table-striped' id='myT' >
           <thead>
             <th>??????????????</th>
             <th>??????</th>
             <th>??????????</th>
             <th>????????????</th>
             <th>????????????</th>
             <th>????????????</th>
             </thead>
             <tbody>";
      $displayed = 0;
      $allResult = $data->num_rows;
      while($d = $data->fetch_assoc()) {
          if($displayed >= 200) break;
          $href = "<b><a href = 'detail?id=".$d['id']."'>".$d['agreement']."</a></b>";
          $addr = "??. ".$d['city'].", ".$d['street'].", ??.".$d['house'].", ??????.{$d['entrance']}, ????. <b>".$d['apartment'];
          $ht['table'] .= "<tr><td>$href</td><td>".$d['name']."</td><td>$addr</td><td>{$d['group']}</td><td>{$d['balance']}</td><td>{$d['prices']}</td></tr>";
          $ht['table_blc'] .= "
            
          ";
          $displayed++;
      }
      $ht['table'] .="</tbody></table>";
  }
}
?>
<?=tpl('head', ['title'=>''])?>
<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>?????????? ??????????????????<small> ???? ???????????? ???????????????? / ???????????? ???????????????? / ???????????? </small></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form class="form-horizontal form-label-left input_mask row" method="GET" action="/abonents/search">
                    <div class=" col-xs-6 col-sm-4 col-md-4 col-lg-2">
                        <label class="control-label">?????????? ????????????????</label>
                        <input type="text" class="form-control has-feedback-left" name="agreement" value="<?=$form['agreement']?>" id="agreement" placeholder="????????????????, 1404">
                    </div>
                    <div class=" col-xs-6  col-sm-4  col-md-4  col-lg-3">
                        <label class="control-label">????????????</label>
                        <select name='group_id[]' multiple="multiple" id="group_id" class="form-control btn-block"><?=$ht['groups']?></select>
                    </div>
                    <div class=" col-xs-6  col-sm-4  col-md-4  col-lg-3 ">
                        <label class="control-label">???????????????? ??????????</label>
                        <select name='price_id[]' multiple="multiple" id="price_id" class="form-control"  ><?=$ht['prices']?></select>
                    </div>
                    <div class=" col-xs-6  col-sm-6  col-md-3  col-lg-2 ">
                        <label class="control-label">??????????/??????</label>
                        <input name='search' id="search" value = '<?=$form['search']?>' class="form-control" placeholder="???????????? 10, 15">
                    </div>
                    <div class=" col-xs-6  col-sm-6  col-md-4  col-lg-2 ">
                        <label class="control-label">??????????????/Email</label>
                        <input name='contact' id="contact" value = '<?=$form['contact']?>' class="form-control" placeholder="0440000000">
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12 col-sm-6 col-md-6  col-lg-1 " style="margin-top: 10px">
                            <button type="submit" name="action" value="search" class="btn btn-block btn-primary">??????????</button>
                        </div>
                    </div>
                    <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-3 form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="exampleCheck1" name="show_disabled" style="margin: 3px; position: relative; bottom: -5px" value="checked" <?=$form['show_disabled']?>
                            <label class="form-check-label" for="exampleCheck1">???????????????????? ??????????????????????</label>
                        </div>
                    </div>
                    <div class=" col-xs-12  col-sm-12  col-md-12  col-lg-12 form-group">
                        <span style="color: gray; font-size: 70%">???? ?????????????? ?????????? ???????? ???????????????? ???????????? 200 ??????????????. ???????? ???? ???? ?????????? ?????????????? ???????????????? - ???????????????? ?????????? ?????? ???????????????????? ???????????????????? ??????????????????</span>

                        <?php if(isset($displayed) && isset($allResult)) {
                            echo "<div style='float: right'>???????????????? $displayed ?????????????? ???? $allResult</div>";
                        }
                        ?>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12  col-sm-12 col-md-12 col-lg-12">
<!--        <div class="table-responsive-light">-->
            <?=$ht['table']?>
<!--        </div>-->
    </div>
</div>
    <script>
        $('#agreement').on("change paste keyup",function(){
            if($('#agreement').val() !== "") {
                $('#group_id').attr('disabled', 'disabled');
                $('#price_id').attr('disabled', 'disabled');
                $('#search').attr('disabled', 'disabled');
            } else {
                $('#group_id').removeAttr('disabled');
                $('#price_id').removeAttr('disabled');
                $('#search').removeAttr('disabled');
            }
        })
        $(document).ready(function () {
            $('#group_id').multiselect({
                includeSelectAllOption: true,
                maxHeight: 300,
                enableFiltering: true,
            });
            $('#price_id').multiselect({
                includeSelectAllOption: true,
                maxHeight: 300,
                enableFiltering: true,
            });
            if($('#agreement').val() !== "") {
                $('#group_id').attr('disabled', 'disabled');
                $('#price_id').attr('disabled', 'disabled');
                $('#search').attr('disabled', 'disabled');
            } else {
                $('#group_id').removeAttr('disabled');
                $('#price_id').removeAttr('disabled');
                $('#search').removeAttr('disabled');
            }
        })
        $(document).ready(function() {
            $('#myT').DataTable( {
                "language": {
                    "lengthMenu": "???????????????????? _MENU_ ?????????????? ???? ????????????????",
                    "zeroRecords": "?? ??????????????????, ?????????????? ???? ??????????????",
                    "info": "????????????????  ???????????????? _PAGE_ ?? _PAGES_",
                    "infoEmpty": "?????? ??????????????",
                    "infoFiltered": "(filtered from _MAX_ total records)",
                    "search": "?????????? ????????????:",
                    "paginate": {
                        "first":      "????????????",
                        "last":       "??????????????????",
                        "next":       "??????????????????",
                        "previous":   "????????????????????"
                    },
                },
                "scrollX": true,
                "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "??????"]]
            });
        });
    </script>
<?=tpl('footer')?>
