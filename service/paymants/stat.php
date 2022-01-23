<?php
$rank = 20;
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 31.08.2017
 * Time: 15:14
 */
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


if(!\envPHP\service\PSC::isPermitted('payment_summary_source')) {
    pageNotPermittedAction();
}


$form = [
  ''
];
envPHP\classes\std::Request($form);
$ht = [
  'pricesNow'=>'',
  'pricesActs'=>'',
  'pricesDeacts'=>'',
  'sbor10Days'=>'',
];

//Деактивировано за сегодня
$data = $sql->query("SELECT pr.`name`, count(*) count, sum(pr.price_day) priceDay
FROM bill_prices pr 
JOIN client_prices act on act.price = pr.id 
WHERE cast(act.time_stop as date) = CURDATE()
GROUP BY pr.`name`
ORDER BY 1 ");
while($d = $data->fetch_assoc())  $ht['pricesDeacts'] .= "<tr><td><b>{$d['name']}</b></td><td>{$d['count']}</td><td>{$d['priceDay']}</td>";

//Активировано за сегодня
$data = $sql->query("SELECT pr.`name`, count(*) count, sum(pr.price_day) priceDay
FROM bill_prices pr 
JOIN client_prices act on act.price = pr.id 
WHERE cast(act.time_start as date) = CURDATE() and time_stop is null 
GROUP BY pr.`name`
ORDER BY 1");
while($d = $data->fetch_assoc())  $ht['pricesActs'] .= "<tr><td><b>{$d['name']}</b></td><td>{$d['count']}</td><td>{$d['priceDay']}</td>";

//Активировано за сегодня
$data = $sql->query("SELECT cast(time as date) date,sum(money)  money FROM paymants GROUP BY cast(time as date) ORDER BY 1 desc LIMIT 10");
while($d = $data->fetch_assoc())  $ht['sbor10Days'] .= "<tr><td><b>{$d['date']}</b></td><td>{$d['money']}</td>";


?><?=tpl('head', ['title'=>''])?>
    <div class="row">
        <div class="col-lg-12 col-xs-12 col-md-12 col-sm-12">
            <form id="form_payment" onsubmit="return drawGraphPayment();">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Сбор по периодам</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="form-horizontal form-label-left input_mask row justify-content-md-center justify-content-lg-center" >
                        <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                            <label class="control-label">Дата от</label>
                            <input type='text' name='start' class='form-control'  id="payment_graph_start" >
                        </div>
                        <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                            <label class="control-label">Дата до</label>
                            <input type='text' name='stop' class='form-control' id="payment_graph_stop">
                        </div>
                        <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                            <label class="control-label">Отображать по</label>
                            <select name="group_date" class="form-control"  >
                                <option value="day">Дням</option>
                                <option value="month">Месяцам</option>
                            </select>
                        </div>
                        <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                            <label class="control-label">Группировать по</label>
                            <select name="group_type" class="form-control"  >
                                <option value="city">Городам</option>
                                <option value="group">Группам</option>
                                <option value="source">Источникам платежей</option>
                            </select>
                        </div>
                        <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                            <label class="control-label">&nbsp; </label>
                            <button class="btn btn-primary btn-block"  >Загрузить</button>
                        </div>
                        <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                            <label class="control-label">&nbsp; </label>
                            <button class="btn btn-primary btn-block" onclick="$('#payment_graph').html('<br><br>'); return false;" >Очистить</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-lg-12 col-md-12">
                            <div  id="payment_graph" style="width: 100%">
                                <br><br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 col-xs-12 col-md-12 col-sm-12">
            <form  id="form_price" onsubmit="return drawGraphPrice();">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Прайс по периодам</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="form-horizontal form-label-left input_mask row justify-content-md-center justify-content-lg-center" >
                            <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                                <label class="control-label">Дата от</label>
                                <input type='text' name='start' class='form-control'  id="price_graph_start" >
                            </div>
                            <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                                <label class="control-label">Дата до</label>
                                <input type='text' name='stop' class='form-control' id="price_graph_stop">
                            </div>
                            <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                                <label class="control-label">Отображать по</label>
                                <select name="group_date" class="form-control"  >
                                    <option value="day">Дням</option>
                                    <option value="month">Месяцам</option>
                                </select>
                            </div>
                            <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                                <label class="control-label">Группировать по</label>
                                <select name="group_type" class="form-control"  >
                                    <option value="city">Городам</option>
                                    <option value="group">Группам</option>
                                    <option value="source">Источникам платежей</option>
                                </select>
                            </div>
                            <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                                <label class="control-label">&nbsp; </label>
                                <button class="btn btn-primary btn-block"  >Загрузить</button>
                            </div>
                            <div class=" col-xs-12  col-sm-6  col-md-6  col-lg-2 form-group">
                                <label class="control-label">&nbsp; </label>
                                <button class="btn btn-primary btn-block" onclick="$('#price_graph').html('<br><br>'); return false;" >Очистить</button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 col-lg-12 col-md-12">
                                <div  id="price_graph" style="width: 100%">
                                    <br><br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
<!--
<div class="container-fluid" style="margin-top: 35px">

    <div class="row">
        <div class="col-sm-12">
            <button class="btn btn-primary btn-block btn-lg" data-toggle=" " data-target="#payment_graph_form" onclick="return false;" style="font-size: 22px; margin-top: 15px;">Сбор по периодам(график)</button>
        </div>
    </div>
    <div class="  row" id="payment_graph_form">
        <div class="col-sm-12">
            <form id="form_payment" onsubmit="return drawGraphPayment();">
            <div class="abonCardBlock">
                Укажите период:
            <input type='text' name='start' class='form-control' style='width: 100%;  display: inline; max-width: 120px' id="payment_graph_start" >
                -
            <input type='text' name='stop' class='form-control' style='width: 100%;  display: inline; max-width: 120px' id="payment_graph_stop">
                Отображать:
                <select name="group_date" class="form-control" style='width: 100%;  display: inline; max-width: 120px' >
                    <option value="day">По дням</option>
                    <option value="month">По месяцам</option>
                </select>
                Группировать:
                <select name="group_type" class="form-control" style='width: 100%;  display: inline; max-width: 120px' >
                    <option value="city">По городам</option>
                    <option value="group">По группам</option>
                    <option value="source">По источникам платежей</option>
                </select>


            <div  id="payment_graph" style="width: 100%">
                <br><br>
            </div>
            </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <button class="btn btn-primary btn-block btn-lg" data-toggle=" " data-target="#payment_graph_form" onclick="return false;" style="font-size: 22px; margin-top: 15px;">Прайс по периодам(график)</button>
        </div>
    </div>

    <div class="  row" id="payment_graph_form">
        <div class="col-sm-12">
            <form id="form_price" onsubmit="return drawGraphPrice();">
                <div class="abonCardBlock">
                    Укажите период:
                    <input type='text' name='start' class='form-control' style='width: 100%;  display: inline; max-width: 120px'   id="price_graph_start" >
                    -
                    <input type='text' name='stop' class='form-control' style='width: 100%;  display: inline; max-width: 120px' id="price_graph_stop" >
                    Отображать:
                    <select name="group_date" class="form-control" style='width: 100%;  display: inline; max-width: 120px' >
                        <option value="day">По дням</option>
                        <option value="month">По месяцам</option>
                    </select>
                    Группировать:
                    <select name="group_type" class="form-control" style='width: 100%;  display: inline; max-width: 120px' >
                        <option value="city">По городам</option>
                        <option value="group">По группам</option>
                    </select>
                    <button class="btn btn-primary" style="margin: 5px; display: inline" >Загрузить</button>

                    <div  id="price_graph" style="width: 100%">
                        <br><br>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
-->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script>
    $(function () {
        $('#payment_graph_start').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?php echo date("d.m.Y", time() - 604800)?>'});
        $('#payment_graph_stop').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?php echo date("d.m.Y")?>'});
        $('#price_graph_start').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?php echo date("d.m.Y", time() - 604800)?>'});
        $('#price_graph_stop').datetimepicker({language: 'ru', pickTime: false, defaultDate: '<?php echo date("d.m.Y")?>'});
    });
    function drawGraphPayment() {
        $('#payment_graph').show();
        $('#payment_graph').html("<br><br><h4 align='center'>Загрузка...</h4><br><br>");
        $.getScript('stat_graphSbor?' + $('#form_payment').serialize(), function(){  });
        return false;
    }
    function drawGraphPrice() {
        $('#price_graph').show();
        $('#price_graph').html("<br><br><h4 align='center'>Загрузка...</h4><br><br>");
        $.getScript('stat_graphPrice?' + $('#form_price').serialize(), function(){  });
        return false;
    }
</script>
<?=tpl('footer')?>