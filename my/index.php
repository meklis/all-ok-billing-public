<?php

use envPHP\classes\std;
use envPHP\ClientPersonalArea\ClientInfo;
use envPHP\ClientPersonalArea\LangTranslator;

session_start();
require_once __DIR__ . "/../envPHP/load.php";

$auth = new envPHP\ClientPersonalArea\Auth();
$CONF = getGlobalConfigVar('PERSONAL_AREA');
$siteAddr = getGlobalConfigVar('BASE')['site_addr'];
$USER_ID = 0;
//Проверка авторизации
if ((isset($_SESSION['agreement']) && isset($_SESSION['pass'])) && !isset($_SESSION['uid'])) {
    $USER_ID = $auth->auth($_SESSION['agreement'], $_SESSION['pass']);
    if ($USER_ID <= 0) {
        header('Location: autorize.php?error=true');
        exit;
    }
    $_SESSION['uid'] = $USER_ID;
} elseif (isset($_SESSION['uid'])) {
    $USER_ID = $_SESSION['uid'];
} else {
    header('Location: autorize.php');
    exit;
};

$form = [
    'p' => 'index',
    'pageno' => 1,
    'liqpay_prepaded' => false,
    'amount' => 0,
    'phone' => '',
    'message' => '',
    'goto_agreement' => 0,
    'price_id' => 0,
    'activation_id' => 0,
    'agreement' => 0,
    'code' => '',
    'mac' => '',
    'uuid' => '',
    'act' => '',
];
std::Request($form);

if(isset($_SESSION['form'])) {
    foreach ($_SESSION['form'] as $key=>$val) {
        $form[$key] = $val;
    }
    unset($_SESSION['form']);
}

//Initialize client
$client = new ClientInfo($USER_ID);

if($form['goto_agreement']) {
   $neighbors =  $client->getNeighborAgreements();
   foreach ($neighbors as $neighbor) {
       if($neighbor['id'] == $form['goto_agreement']) {
           $_SESSION['uid'] = $neighbor['id'];
           header('Location: /');
           exit;
       }
   }
   die('incorrect agreement id');
}

//Get general info for template, not use in pages
$info = $client->getGeneralInfo();
$payed_to = $client->getPayedTo();

$PROVIDER_NAME = getGlobalConfigVar('BASE')['provider_name'];
$SITE_ADDR = getGlobalConfigVar('BASE')['site_addr'];
//RENDERING COMPONENTS
$COMPONENT_BALANCE = require __DIR__ . '/components/balance.php';
$COMPONENT_HEAD = require __DIR__ . '/components/head.php';
$COMPONENT_MENU =  require __DIR__ . '/components/menu.php';


$HTML = <<<HTML
<html lang="ru">
<head>
    {$COMPONENT_HEAD()}
</head>
<body class="hold-transition sidebar-mini layout-boxed" style="background-color:rgb(42, 59, 71);">
<div class="wrapper" id="yak">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item  d-sm-inline-block">
                <h4 class="nav-link" style="color:#303f9f;">{{AGREEMENT}} № {$info['agreement']}</h4>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li><a href='autorize.php?exit=true' class='btn btn-warning btn-sm'><i class="fas fa-sign-out-alt"></i>{{BTN_EXIT}}</a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-light-warning elevation-4">
        <!-- Brand Logo -->
       <!-- <a href="/" class="brand-link" style="font-size:2em;">
            <img src="assets/img/logo-new.png" alt="Logo">
            <span class="brand-text font-weight-light">{$PROVIDER_NAME}</span>
        </a> -->
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image" width="100%">
            <i class="fas fa-user-edit" style="font-size:1.5em;color:#303f9f;"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{PERSONAL_AREA_LABEL}}</a>
                </div>
            </div>
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    {$COMPONENT_MENU($CONF['menu'], $form['p'])}
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
    <!-- /.Main Sidebar Container -->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 " style="letter-spacing:0.2em;font-size:1em;color:#303f9f;">All-Ok-Billing</h1>
                    </div><!-- /.col -->

                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">
                               <a href="{$siteAddr}" style="color:#303f9f;">{{PAGE_OFFICIAL_SITE}}</a> 
                            </li>
                        </ol>
                    </div>
                    <div class="col-sm-12" style="text-align:center;font-size:1.15em;">
                        {$COMPONENT_BALANCE($info['balance'], $payed_to)}
                    </div>
                </div>
            </div>

        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content"> 
HTML;
$PAGE_MAIN = require  __DIR__ . '/pages/main.php';
$PAGE_SETTINGS = require  __DIR__ . '/pages/settings.php';
$PAGE_CHANGE_PWD = require  __DIR__ . '/pages/change_pwd.php';
$PAGE_MAIL = require  __DIR__ . '/pages/mail.php';
$PAGE_PAYMENTS = require  __DIR__ . '/pages/payments.php';
$PAGE_CONTACTS = require  __DIR__ . '/pages/contacts.php';
$PAGE_PAY = require  __DIR__ . '/pages/pay.php';
$PAGE_QUESTIONS = require  __DIR__ . '/pages/questions.php';
//$PAGE_ZABBIX = require  __DIR__ . '/pages/zabbix.php';
$PAGE_OTT = require  __DIR__ . '/pages/trinity.php';

switch ($form['p']) {
case 'index':
case 'main':
case 'info':
      $HTML .= $PAGE_MAIN($client, $info['name'], $info['phone'], $info['email']);
      break;
case 'settings': //Страница настроек абонента
      $HTML .=  $PAGE_SETTINGS($client);
      break;
case 'change_pwd':
      $HTML .=  $PAGE_CHANGE_PWD();
      break;
case 'mail':
      $HTML .=  $PAGE_MAIL($form['phone'] ? $form['phone'] : $info['phone']);
      break;
case 'payments':
      $HTML .= $PAGE_PAYMENTS($client->getPayments(), $form['pageno']);
      break;
case 'contacts':
      $HTML .= $PAGE_CONTACTS();
      break;
case 'pay':
      $HTML .= $PAGE_PAY($client, $form['liqpay_prepaded'], $form['amount']);
      break;
case 'questions':
      $HTML .= $PAGE_QUESTIONS($client->getQuestions(), $form['pageno']);
      break;
/*case 'zabbix':
      $HTML .= $PAGE_ZABBIX($client->getBindings());
      break;*/
    default:
        $HTML .= "<h3 align='center' style='padding-top: 30px; padding-bottom: 30px'>{{PAGE_NOT_FOUND}}</h3>";
}

//Work with notifications
$NOTY = "";
if(isset($_SESSION['action_response'])) {
    $note = $_SESSION['action_response'];
    $NOTY .= "
<script>
new Noty({
   type: '{$note['status']}',
   layout: 'topRight',
   theme: 'metroui',
   text: '{$note['message']}',
   timeout: '10000',
   progressBar: true,
   closeWith: ['click'],
   killer: true
}).show();
</script>";
    unset($_SESSION['action_response']);
}


$HTML .= <<<HTML
                             
        </div>
    </div>
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            <a href="assets/img/pub_ofert.pdf" style="color:#303f9f">{{PUB_OFFER}}</a>
        </div>
        <ul style="list-style:none;">
            <li>
                <a href="#yak"><i class="fas fa-hand-pointer" style="font-size:2em;color:#303f9f;"></i></a>
            </li>
            <li>
                <strong>Copyright &copy; 2019-2020 <a href="$SITE_ADDR" style="color:#303f9f;">$PROVIDER_NAME</a>.</strong> All
                rights reserved.
            </li>
        </ul>
    </footer>
</div>
<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="assets/js/jquery.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/js/adminlte.min.js"></script>
<script src="assets/noty/noty.min.js"></script> 
<script>   

</script>
$NOTY
</body>
</html>
HTML;


//Work with notifications


//TRANSLATION
$lang = 'ua';
$langTranslate = new LangTranslator(__DIR__ . '/langs');
if (isset($_GET['lang']) && $langTranslate->isTranslateExists($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $_GET['lang'];
} elseif (isset($_SESSION['lang']) && $langTranslate->isTranslateExists($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}
echo $langTranslate->setLang($lang)->parse($HTML);


