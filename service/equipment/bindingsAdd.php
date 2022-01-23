<?php
require $_SERVER['DOCUMENT_ROOT'] . "/include/load.php";
init();

if(!\envPHP\service\PSC::isPermitted('eq_binding_create')) {
    pageNotPermittedAction();
}

//Блок для обработки формы
$form = [
        'agreement'=>'',
        'activation'=>'',
        'ip'=>'',
        'mac'=>'',
        'port'=>'',
        'switch'=>'',
        'action'=>'',
    'real_ip' => false,
    'allow_static' => false,
];
$ht = [
        'message'=>'',
        'activations'=>''
];
$allowForm = false;

envPHP\classes\std::Request($form);

if($form['agreement']) {
    //Проверка договора
    if($sql->query("SELECT id FROM clients WHERE agreement = '{$form['agreement']}' and house in (SELECT h.id from addr_houses h JOIN addr_groups ag on h.group_id = ag.id and ag.id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups())."))")->num_rows == 0) {
        $html->addNoty('warning', "Указанный договор не найден!");
    } else {
        //Выбор активации
        $data = $sql->query("SELECT a.id, `name`, price_day FROM `client_prices` a
        JOIN bill_prices p on a.price = p.id 
        WHERE agreement = (SELECT id FROM clients WHERE agreement = '{$form['agreement']}')
        and work_type in ('inet') and time_stop is null; ");
        if ($data->num_rows == 0) {
            $html->addNoty('info', 'Активаций по выбранному договору не найдено');
        } else {
            while ($d = $data->fetch_assoc()) {
                if ($d['id'] == $form['activation']) $sel = "SELECTED"; else $sel = "";
                $ht['activations'] .= "<OPTION $sel value='{$d['id']}'>{$d['name']} ({$d['price_day']}грн/д)</OPTION>";
            }
            $allowForm = true;
        }
    }

}

if($form['action'] == 'search') {
    $store = new \envPHP\NetworkCore\SearchIp\DbStore();
    $wildcore = envPHP\Wildcore\ClientInitializer::getClient();
    $ip = $_SERVER['REMOTE_ADDR'];
    try {
        $searching = $wildcore->searchDevice()->searchArpAndFdbOverIP(
            $store->getSwitchesListByIp($ip),
            $store->getRouterListByIp($ip),
            $ip
        );
        $form['mac'] = $searching->getArp()->getMac();
        $form['port'] = $searching->getFdb()->getInterface()->getName();
        $form['switch'] = $searching->getFdb()->getDevice()->getIp();
    } catch (\Exception $e) {
        $html->addNoty('error', $e->getMessage());
    }

}

if($form['action'] == 'add') {
    $result = @json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/binding/add", ['employee'=>_uid,
                                                                             'activation'=>$form['activation'],
                                                                             'switch'=>$form['switch'],
                                                                             'port'=>$form['port'],
                                                                             'ip'=>$form['ip'],
                                                                             'mac'=>$form['mac'],
                                                                             'real_ip' => $form['real_ip'],
        'allow_static' => $form['allow_static'],
                          ]));
    if(!$result) {
        $html->addNoty('error', "Ошибка при работе с API");
    } elseif ($result->code != 0) {
        $html->addNoty('error', "Ошибка регистрации привязки: {$result->errorMessage}");
    } else {
        $html->addNoty('success', "Привязка успешно зарегистрирована!");
        $form['ip'] = '';
        $form['mac'] = '';
        $form['switch'] = '';
        $form['port'] = "";
        $form['real_ip'] = false;
        $form['allow_static'] = false;
    }
}

?><?=tpl('head', ['title'=>''])?>
<div class="row justify-content-md-center">
    <div class="col-lg-offset-3 col-md-offset-2 col-sm-offset-1  col-sm-10 col-lg-6 col-md-8 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Внесение привязок</h2>
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
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">MAC абонента
                            </label>
                            <div class="col-md-8 col-xs-12">
                                <input name="mac" class="form-control " value="<?=$form['mac']?>"  placeholder="AA:BB:CC:DD:EE:FF">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Свитч
                            </label>
                            <div class="col-md-8 col-xs-12">
                                <input name="switch" class="form-control " value="<?=$form['switch']?>"  placeholder="10.0.0.1">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Порт
                            </label>
                            <div class="col-md-3 col-xs-4">
                                <input name="port" class="form-control " value="<?=$form['port']?>"  placeholder="1">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">IP абонента
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input name="ip" class="form-control" value="<?=$form['ip']?>"  placeholder="1.2.3.4">
                                <small>Можно не указывать, будет выбран свободный</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Выдать реальный IP
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input type="checkbox" name="real_ip" class="form-control" value="1" <?=$form['real_ip'] ? 'checked': ''?>>
                                <small>При установленном флаге будет производится поиск белого свободного IP</small>
                            </div>
                        </div>
                        <?php if(getGlobalConfigVar('RADIUS') && getGlobalConfigVar('RADIUS')['enabled']) {?>
                        <div class="form-group" style="display:none;">
                            <label class="control-label col-md-4 col-sm-4 col-xs-12" for="agree">Разрешить статический IP
                            </label>
                            <div class=" col-md-8 col-xs-12">
                                <input type="checkbox" name="allow_static" class="form-control" value="1" <?=$form['allow_static'] ? 'checked': ''?> >
                                <small>В этом случае ARP будет разрешен, если абонент прописал себе статический IP</small>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="form-group">
                            <div class=" col-md-6 col-xs-12">
                                <button class="btn btn-primary btn-block" type="submit" name="action"  value="search" >Найти порт</button>
                            </div>
                            <div class=" col-md-6 col-xs-12">
                                <button class="btn btn-primary btn-block" type="submit" name="action"  value="add" >Внести</button>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</div>
<?=tpl('footer')?>
