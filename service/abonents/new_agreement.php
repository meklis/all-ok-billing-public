<?php
$rank = 5;
$urank = 8;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(!\envPHP\service\PSC::isPermitted('customer_create')) {
    pageNotPermittedAction();
}

$form = [
        'provider'=>0,
];
envPHP\classes\std::Request($form);

$ht = [
    'providerList' => '',
];

$test_port = '';
$message = '';
if(isset($form['action'])) {
    $check = function() use (&$form,&$message){
        if($form['house'] == 0) {$message="<div id='message_fail'>Не указан адрес</div>"; return false;}
        if((int)trim($form['apartment']) == '') {$message="<div id='message_fail'>Некоректный номер квартиры</div>"; return false;}
        if((int)trim($form['entrance']) == '') {$message="<div id='message_fail'>Некоректный номер подьезда</div>"; return false;}
        if((int)trim($form['agreement']) == '') {$message="<div id='message_fail'>Некоректный номер договора</div>"; return false;}
        if(trim($form['phone']) == '') {$message="<div id='message_fail'>Указывать номер телефона обязательно</div>"; return false;}

       return true;
    };
    $agreementId = 0;
   if($check()) {
       try {
           $sqlD = dbConnPDO();
           $password = randomPassword(6);
           $sqlD->prepare("
            INSERT INTO clients (
                agreement,name,apartment,house,password,provider,entrance
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?      
            ) 
       ")->execute([
               trim($form['agreement']), $form['name'], $form['apartment'], $form['house'], $password, $form['provider'], $form['entrance']
           ]);
           $agreementId = dbConnPDO()->lastInsertId();
           (new \envPHP\structs\ClientContact('PHONE', $agreementId, $form['phone'], 'Основной', _uid, true))->save();
           if($form['email']) (new \envPHP\structs\ClientContact('EMAIL', $agreementId, $form['email'], 'Основной', _uid, true))->save();

           \envPHP\EventSystem\EventRepository::getSelf()->notify("customer:create", [
               'id' => $agreementId,
               'agreement' => $form['agreement'],
               'name' => $form['name'],
               'apartment' => $form['apartment'],
               'house_id' => $form['house'],
               'phone' => $form['phone'],
               'email' => $form['email'],
               'provider' => $form['provider'],
               'employee_id' => _uid,
           ]);
           header("Location: detail?id=$agreementId");
           exit;
       } catch (\Exception $e) {
           dbConnPDO()->prepare("DELETE FROM clients WHERE id = ?")->execute([$agreementId]);
           html()->addNoty('error', $e->getMessage());
       }

   }
}
if(!isset($form['price'])) $form['price'] = 0;
if(!isset($form['entrance'])) $form['entrance'] = '';
if(!isset($form['apartment'])) $form['apartment'] = '';
if(!isset($form['name'])) $form['name'] = '';
if(!isset($form['phone'])) $form['phone'] = '';
if(!isset($form['email'])) $form['email'] = '';
//Выборка адреса 
if(!isset($form['city'])) $form['city']=0;
if(!isset($form['street'])) $form['street']=0;
if(!isset($form['house'])) $form['house']=0;
$html->getHouses($form['city'],$form['street'],$form['house'], $sql);


$agreement = $sql->query("SELECT get_free_agreement() v ")->fetch_assoc()['v'];

if(isset($form['agreement'])) $agreement = $form['agreement'];

$provData = $sql->query("SELECT id, name FROM service.providers ORDER BY name");
while ($d = $provData->fetch_assoc()) {
    if($d['id'] == $form['provider']) $sel = "SELECTED"; else $sel = "";
    $ht['providerList'] .= "<OPTION value='{$d['id']}' $sel>{$d['name']}</OPTION>";
}

?>
<?=tpl('head', ['title'=>''])?>
<div class="row">
    <form name="form" method="POST">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Создание договора</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <h3> <center><?=$message?></center></h3>
                    <div class="form-horizontal form-label-left input_mask row" >
                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                            <span style="font-size: 16px; font-weight:  500">Основное</span>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Номер договора</label>
                            <input name='agreement' value="<?=$agreement?>" class="form-control">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2  form-group">
                            <label class="control-label">Провайдер</label>
                            <SELECT name="provider" class="form-control">
                                <?=$ht['providerList']?>
                            </SELECT>
                        </div>
                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group"  style="margin: 0">
                            <div class="divider-dashed"></div>
                            <span style="font-size: 16px; font-weight:  500">Адрес</span>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                            <label class="control-label">Город</label>
                            <?=$html->listCities?>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                            <label class="control-label">Улица</label>
                            <?=$html->listStreets?>
                        </div>
                        <div class=" col-xs-12 col-sm-4 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Дом</label>
                            <?=$html->listHouses?>
                        </div>
                        <div class=" col-xs-6 col-sm-2 col-md-2 col-lg-1 form-group">
                            <label class="control-label">Подьезд</label>
                            <input name='entrance' class="form-control"  value='<?=$form['entrance']?>' placeholder="1" pattern="[0-9]{1,3}">
                        </div>
                        <div class=" col-xs-6 col-sm-2 col-md-2 col-lg-1 form-group">
                            <label class="control-label">Квартира</label>
                            <input name='apartment' class="form-control" value='<?=$form['apartment']?>' required placeholder="1" >
                        </div>
                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group"  style="margin: 0">
                            <div class="divider-dashed"></div>
                            <span style="font-size: 16px; font-weight:  500">Связь с абонентом</span>
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Обращение</label>
                            <input name='name' class="form-control"   value='<?=$form['name']?>' required  placeholder="Иван Иванов">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Номер телефона</label>
                            <input name='phone' class="form-control"  value='<?=$form['phone']?>' required  placeholder="+380631234567">
                        </div>
                        <div class=" col-xs-6 col-sm-6 col-md-4 col-lg-2 form-group">
                            <label class="control-label">Email</label>
                            <input name='email' class="form-control"  value='<?=$form['email']?>' placeholder="test@mail.ua" >
                        </div>

                        <div class=" col-xs-12 col-sm-12 col-md-12 col-lg-12 form-group" style="margin: 0">
                            <span style="font-size: 16px; font-weight:  500"></span>
                        </div>
                        <div class=" col-xs-12 col-sm-6 col-md-6 col-lg-2 form-group">
                            <label class="control-label">&nbsp; </label>
                            <button class="btn btn-primary btn-block" type="submit" name='action' value="search">Внести договор</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?=tpl('footer')?>