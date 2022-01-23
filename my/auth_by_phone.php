<?php

use envPHP\classes\std;
use envPHP\ClientPersonalArea\LangTranslator;

require_once __DIR__ . "/../envPHP/load.php";
session_start();

$config = getGlobalConfigVar('PERSONAL_AREA');

$form = [
    'action' => 'set_phone',
    'phone' => '',
    'code' => '',
    'agreement' => '',
];
$ht = [
    'message' => '',
    'type' => '',
    'agreements' => '',
    'view' => 'set_phone',
];
std::Request($form);

$setMessage = function ($message, $type) use (&$ht) {
    $ht['message'] = $message;
    $ht['type'] = $type;
};

$auth = new \envPHP\ClientPersonalArea\Auth();

$config = getGlobalConfigVar('PERSONAL_AREA');
$langForm = '';
if ($config['multi_lang']['enabled']) {
    $langForm = '<SELECT class="form-control" name="lang">';
    foreach ($config['multi_lang']['langs'] as $k => $v) {
        $selected = isset($_SESSION['lang']) && $_SESSION['lang'] === $k ? 'SELECTED' : "";
        $langForm .= "<OPTION value='$k'>$v</OPTION>";
    }
    $langForm .= "</SELECT>";
}


try {
    switch ($form['action']) {
        case 'set_phone':
            $ht['view'] = 'set_phone';
            break;
        case 'send_code':
            $ht['view'] = 'send_code';
            $setMessage("Введіть код надісланний в SMS-повідомленні", 'info');
            if ($form['phone']) {
                //Send confirmation code
                $code = $auth->getAuthByPhoneCode($form['phone']);
                envPHP\service\shedule::add(26, 'notification/sendSMS', [
                    'phone' => $form['phone'],
                    'message' => sprintf(getGlobalConfigVar('PERSONAL_AREA')['auth_by_phone']['text'], $code),
                ]);
            } else {
                $form['action'] = 'set_phone';
                throw new \Exception("Поле телефон обов'язкове");
            }
            break;
        case 'confirm_code':
            $ht['view'] = 'send_code';
            if (!$form['code']) {
                throw new \Exception("Заповніть поля");
            }
            if (!$auth->isAuthByPhoneCodeValid($form['phone'], $form['code'])) {
                throw new \Exception("Невірний код");
            }
            $clients = $auth->getClientsByPhone($form['phone']);
            foreach ($clients as $cl) {
                if (!$form['agreement']) {
                    $form['agreement'] = $cl->getId();
                }
                $sel = $form['agreement'] == $cl->getId() ? 'checked' : '';
                $ht['agreements'] .= "<div style='padding: 5px;'><input  style='padding: 5px' type='radio' $sel name='agreement' id='agree{$cl->getId()}' value='{$cl->getId()}'   >
                                  <label for='agree{$cl->getId()}'>{$cl->getAgreement()}</label></div>";
            }
            $ht['view'] = 'show_agreements';
            break;
        case 'start_login':
            if (!$form['code']) {
                throw new \Exception("Заповніть поля");
            }
            if (!$auth->isAuthByPhoneCodeValid($form['phone'], $form['code'])) {
                $ht['view'] = 'send_code';
                throw new \Exception("Невірний код");
            }
            if(!$form['agreement']) {
                throw new \Exception("Необходимо выбрать договор");
            }
            $client = null;
            foreach ($auth->getClientsByPhone($form['phone']) as $cl) {
                if($cl->getId() == $form['agreement']) {
                    $client = $cl;
                    break;
                }
            }
            $_SESSION['agreement'] = $client->getAgreement();
            $_SESSION['pass'] = $client->getPassword();

            //Регистрация перевода
            $lang = 'ua';
            $langTranslate = new LangTranslator(__DIR__ . '/langs');
            if (isset($_REQUEST['lang']) && $langTranslate->isTranslateExists($_REQUEST['lang'])) {
                $lang = $_REQUEST['lang'];
            }
            $_SESSION['lang'] = $lang;
            header('Location: index.php');
            exit();
            break;
    }
} catch (Exception $e) {
    $setMessage($e->getMessage(), 'danger');
}

$provider_name = getGlobalConfigVar('BASE')['provider_name'];
$HTML = <<<HTML
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{$provider_name} - особистий кабінет</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="assets/css/all.min.css">
    <!-- IonIcons -->
    <link rel="stylesheet" href="assets/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="assets/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="#">{$provider_name}</a>
    </div> 
    <div class="card">
        <div class="card-body login-card-body">
<div class="alert alert-{$ht['type']} alert-dismissible">{$ht['message']}</div>
HTML;

if ($ht['view'] == 'set_phone') {
    $HTML .= <<<HTML
<form method='POST'> 
    <small>Введіть свій номер телефону</small>
    <div class="input-group mb-3">
        <input class="form-control" placeholder="+38063000000" name="phone" id="phone"
           data-inputmask="'mask': '+99 (999) 999-99-99'">
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-phone"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <button class="btn btn-warning btn-md btn-block" name="action" value="send_code">
                Надіслати код
            </button>
        </div>
    <!-- /.col -->
    </div>
</form>
HTML;

}

if ($ht['view'] == 'send_code') {
    $HTML .= <<<HTML
            <form method='POST'>
                    <small>Код пiдтвердження</small>
                <div class="input-group mb-3">
                    <input class="form-control" placeholder="code" type='text' name="code" value="{$form['code']}">
                    <input name="phone" value="{$form['phone']}" hidden hidden="hidden">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-warning btn-md btn-block" type="submit" name="action" value="confirm_code">Підтвердити</button>
                    </div> 
                    <!-- /.col -->
                </div>
            </form>
HTML;
}

if ($ht['view'] == 'show_agreements') {
    $HTML .= <<<HTML
            <form method='POST'>
               <small>Виберіть договір</small>
                <div class="input-group mb-3">   
                        {$ht['agreements']} 
                </div> 
                    <input name="phone" value="{$form['phone']}" hidden hidden="hidden">
                    <input name="code" value="{$form['code']}" hidden hidden="hidden">
                    
                    
               <small>Мова</small>
                    <div class="input-group mb-3">
                        {$langForm}
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-globe"></span>
                            </div>
                        </div>
                    </div>
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-warning btn-md btn-block" type="submit" name="action" value="start_login">Вiйти</button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
HTML;
}


$HTML .= <<<HTML
        </div>
        <!-- /.login-card-body -->
    </div>
</div>


<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="assets/js/jquery.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/js/adminlte.min.js"></script>
<script src="/assets/inputmask/dist/inputmask.js"></script>
<script src="/assets/inputmask/dist/bindings/inputmask.binding.js"></script>
</body>
</html>

HTML;

echo $HTML;
