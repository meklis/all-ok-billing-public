<?php

use envPHP\classes\std;
use envPHP\ClientPersonalArea\ClientActions;
use envPHP\ClientPersonalArea\ClientInfo;
use envPHP\ClientPersonalArea\PasswordReminder;

require_once __DIR__ . "/../envPHP/load.php";
session_start();

$config = getGlobalConfigVar('PERSONAL_AREA');

$form = [
    'action' => 'set_phone',
    'phone' => '',
    'code' => '',
    'new_pwd' => '',
    'new_pwd_repeat' => '',
    'uuid' => '',
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

$recovery = new PasswordReminder();
try {
    switch ($form['action']) {
        case 'set_phone':
            $ht['view'] = 'set_phone';
            if ($form['phone']) {
                $phone = $recovery->formatPhone($phone);
                $exist = $recovery->isPhoneExist($phone);
                if (!$exist) {
                    throw new \Exception("Телефон не знайдено");
                }
            }
            break;
        case 'send_code':
            $ht['view'] = 'send_code';
            $setMessage("Введіть код надісланний в SMS-повідомленні", 'info');
            if ($form['phone']) {
                $phone = $recovery->formatPhone($form['phone']);
                if (!$recovery->isPhoneExist($phone)) {
                    $ht['view'] = 'set_phone';
                    throw new \Exception("Телефон не знайдено в базі даних");
                }
                $uuid = $recovery->generateUid();
                $code = $recovery->generateCode();
                $recovery->sendConfirmation($phone, $uuid, $code);
                $form['uuid'] = $uuid;
            } else {
                $form['action'] = 'set_phone';
                throw new \Exception("Поле телефон обов'язкове");
            }
            break;
        case 'confirm_code':
            $ht['view'] = 'send_code';
            if (!$form['uuid'] || !$form['code']) {
                throw new \Exception("Заповніть поля");
            }
            $code = $recovery->getCode($form['uuid']);
            if ($code != $form['code']) {
                throw new \Exception("Невірний код");
            }
            $recovery->setCodeConfirmed($form['uuid']);
            $agreements = $recovery->findAgreements($form['uuid']);
            foreach ($agreements as $agree) {
                if (!$form['agreement']) {
                    $form['agreement'] = $agree['id'];
                }
                $sel = $form['agreement'] == $agree['id'] ? 'checked' : '';
                $ht['agreements'] .= "<input  type='radio' $sel name='agreement' id='agree{$agree['id']}' value='{$agree['id']}'   >
                                  <label for='agree{$agree['id']}'>{$agree['agreement']}</label>";
            }
            $ht['view'] = 'change_pwd';
            break;
        case 'change_pwd':
            $ht['view'] = 'change_pwd';
            $setMessage('info', 'Ведіть новий пароль');
            $agreements = $recovery->findAgreements($form['uuid']);
            foreach ($agreements as $agree) {
                if (!$form['agreement']) {
                    $form['agreement'] = $agree['id'];
                }
                $sel = $form['agreement'] == $agree['id'] ? 'checked' : '';
                $ht['agreements'] .= "<div class='form-group'><input  type='radio' $sel name='agreement' id='agree{$agree['id']}' value='{$agree['id']}'>
                                  <label for='agree{$agree['id']}'>{$agree['agreement']}</label></div>";
            }
            if (!$form['uuid'] || !$form['new_pwd']) {
                throw new \Exception("Заповніть поля");
            }
            if (!$form['agreement']) {
                throw new \Exception("Договір не вибран");
            }
            $allowed = false;
            foreach ($recovery->findAgreements($form['uuid']) as $agree) {
                if ($agree['id'] == $form['agreement']) $allowed = true;
            }
            if (!$allowed) {
                throw new \Exception("Невірний договір");
            }
            if (!$recovery->isCodeConfirmed($form['uuid'])) {
                throw new \Exception("Телефон не підтверджений");
            }

            $client_auth = new envPHP\ClientPersonalArea\Auth();
            $client_auth->changePasswd($form['agreement'], $form['new_pwd']);
            $setMessage('Пароль успішно змінено', 'success');
            $_SESSION['message'] = "Пароль успішно змінено. Спробуйте увійти";
            header('Location: /autorize.php');
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
Код відновлення пароля буде надісланий на вказаний номер телефону<br><br>
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
                    <small>Код відновлення</small>
                <div class="input-group mb-3">
                    <input class="form-control" placeholder="code" type='text' name="code" value="{$form['code']}">
                    <input name="uuid" value="{$form['uuid']}" hidden hidden="hidden">
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

if ($ht['view'] == 'change_pwd') {
    $HTML .= <<<HTML
            <form method='POST'>
               <small>Виберіть ваш договір</small>
                <div class="input-group mb-3">   
                    <input name="uuid" value="{$form['uuid']}" hidden hidden="hidden">
                        {$ht['agreements']} 
                </div> 
                    <small>Введіть новий пароль</small> 
                <div class="input-group mb-3">
                    <input class="form-control" placeholder="Password" type='password' name="new_pwd" value="{$form['new_pwd']}">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-warning btn-md btn-block" type="submit" name="action" value="change_pwd">Змінити пароль</button>
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
