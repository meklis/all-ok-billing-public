<?php

use envPHP\service\TrinityControl;
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();


if(!\envPHP\service\PSC::isPermitted('trinity_binding_add')) {
    pageNotPermittedAction();
}

//Блок для обработки формы
$form = [
        'agreement'=>'',
        'activation'=>'',
        'mac'=>'',
        'uuid'=>'',
        'code'=>'',
        'action'=>'',
        'type' => '',
];
$ht = [
        'activations'=>'',
        'tab_num' => 0,
        'url' => '',
];
$allowForm = false;

envPHP\classes\std::Request($form);

if($form['action'] == 'add') {
    $ht['tab_num'] = 1;
}
if($form['action'] == 'generate_playlist') {
    $ht['tab_num'] = 2;
}

if($form['agreement']) {
    //Проверка договора
    if($sql->query("SELECT id FROM clients WHERE agreement = '{$form['agreement']}' and house in (SELECT h.id from addr_houses h JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(',', \envPHP\service\PSC::getAllowedHouseGroups())."))")->num_rows == 0) {
        $html->addNoty('warning', "Указанный договор не найден!");
    } else {
        //Выбор активации
        $data = $sql->query("SELECT a.id, `name`, price_day FROM `client_prices` a
        JOIN bill_prices p on a.price = p.id 
        WHERE agreement = (SELECT id FROM clients WHERE agreement = '{$form['agreement']}')
        and work_type in ('trinity') and time_stop is null; ");
        if ($data->num_rows == 0) {
            $html->addNoty('info', 'Активаций trinity по выбранному договору не найдено');
        } else {
            while ($d = $data->fetch_assoc()) {
                if ($d['id'] == $form['activation']) $sel = "SELECTED"; else $sel = "";
                $ht['activations'] .= "<OPTION $sel value='{$d['id']}'>{$d['name']} ({$d['price_day']}грн/д)</OPTION>";
            }
            $allowForm = true;
        }
    }

}

if($form['action'] == 'add') {
    $result = @json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/trinity/device_add", ['employee'=>_uid,
                                                                             'activation'=>$form['activation'],
                                                                             'mac' => $form['mac'],
                                                                             'uuid' => $form['uuid'],
                          ]));

    if(!$result) {
        $html->addNoty('error', "Ошибка при работе с API");
    } elseif ($result->code != 0) {
        $error = substr($result->errorMessage, 0, 180);
        $html->addNoty('error', "Ошибка регистрации привязки: {$error}");
    } else {
        $html->addNoty('success', "Привязка успешно зарегистрирована!");
        $form['mac'] = '';
        $form['uuid'] = '';
        $form['code'] = '';

    }
}

if($form['action'] == 'add_by_code') {
    $result = @json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/trinity/device_add_by_code", ['employee'=>_uid,
                                                                             'activation'=>$form['activation'],
                                                                             'code' => $form['code'],
                          ]));
    if(!$result) {
        $html->addNoty('error', "Ошибка при работе с API");
    } elseif ($result->code != 0) {
        $html->addNoty('error', "Ошибка регистрации привязки: {$result->errorMessage}");
    } else {
        $html->addNoty('success', "Привязка успешно зарегистрирована!");
        $form['mac'] = '';
        $form['uuid'] = '';
        $form['code'] = '';
    }
}

if($form['action'] == 'generate_playlist') {
    try {
        $bindingId = envPHP\service\TrinityControl::reg($form['activation'], _uid);
        $bind = TrinityControl::getBindingById($bindingId);
        $ht['url'] = $bind['local_playlist_id'];
        $html->addNoty('success', "Плейлист успешно сгенерирован");
    } catch (Exception $e) {
        $html->addNoty('error', "Ошибка регистрации привязки: {$e->getMessage()}");
    }

}
 ?><?=tpl('head', ['title'=>''])?>
<div class="row justify-content-md-center">
    <div class="col-lg-offset-3 col-md-offset-2 col-sm-offset-1  col-sm-10 col-lg-6 col-md-8 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Внесение привязок Trinity</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form  method="POST" enctype="multipart/form-data">
                    <div class="form-horizontal form-label-left row">
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-4  form-group">
                            <label class="control-label">Номер договора</label>
                            <input name="agreement" id="agreement" class="form-control" value="<?=$form['agreement']?>">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-4  form-group">
                            <label class="control-label">&nbsp; </label>
                            <button class="btn btn-primary btn-block" type="submit">Поиск</button>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-4  form-group">
                            <label class="control-label">&nbsp; </label>
                            <button class="btn btn-primary btn-block" onclick="window.location.href = '/abonents/detail?agreement='+ $('#agreement').val(); return false;">Перейти к договору</button>
                        </div>
                    </div>
                    <?php if($ht['activations']) { ?>
                    <div class="clearfix"></div>
                    <div class="divider-dashed"></div>
                    <div class="form-horizontal form-label-left row">
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Выберите активацию
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <select name="activation" class="form-control btnPdn" onchange="submit();">
                                    <?=$ht['activations']?>
                                </select>
                            </div>
                        </div>
                        <ul  id="tabs"  class="nav nav-tabs">
                            <li><a data-toggle="tab" href="#panel_by_code">По коду</a></li>
                            <li><a data-toggle="tab" href="#panel_by_mac">По MAC/UUID</a></li>
                            <li><a data-toggle="tab" href="#panel_gener_playlist">Генерация плейлиста</a></li>
                        </ul>
                        <div class="tab-content">
                            <div id="panel_by_code" class="tab-pane fade">
                                <h4>Добавление устройства по коду</h4>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Код активации
                                    </label>
                                    <div class="col-md-8 col-xs-12">
                                        <input name="code" class="form-control " value="<?=$form['code']?>"  placeholder="1234">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class=" col-md-6 col-xs-12">
                                    </div>
                                    <div class=" col-md-6 col-xs-12">
                                        <button class="btn btn-primary btn-block clearfix" type="submit" name="action"  value="add_by_code" >Внести</button>
                                    </div>
                                </div>
                            </div>
                            <div id="panel_by_mac" class="tab-pane fade">
                                <h4>Добавление устройства по MAC/UUID</h4>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">MAC
                                    </label>
                                    <div class="col-md-8 col-xs-12">
                                        <input name="mac" class="form-control " value="<?=$form['mac']?>"  placeholder="AA:BB:CC:DD:EE:FF">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">UUID
                                    </label>
                                    <div class="col-md-8 col-xs-12">
                                        <input name="uuid" class="form-control " value="<?=$form['uuid']?>"  placeholder="abcdef-abcd....">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class=" col-md-6 col-xs-12">
                                    </div>
                                    <div class=" col-md-6 col-xs-12">
                                        <button class="btn btn-primary btn-block clearfix" type="submit" name="action"  value="add" >Внести</button>
                                    </div>
                                </div>
                            </div>
                            <div id="panel_gener_playlist" class="tab-pane fade">
                                <h4>Генерация плейлиста</h4>
                                <div class="form-group">
                                    <div class=" col-md-6 col-xs-12">
                                    </div>
                                    <div class=" col-md-6 col-xs-12">
                                        <button class="btn btn-primary btn-block clearfix" type="submit" name="action"  value="generate_playlist" >Сгенерировать плейлист</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        // Отображается 1 вкладка,
        // т.к. отсчёт начинается с нуля
        $("#tabs li:eq(<?=$ht['tab_num']?>) a").tab('show');
    });
</script>
<?=tpl('footer')?>
