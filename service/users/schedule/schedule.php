<?php
$rank = 20;
$table = "<center><h3>По Вашему запросу пользователей не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init(true);


$isReadOnly = 'true';
if(!\envPHP\service\PSC::isPermitted('employees_schedule_show')) {
    pageNotPermittedAction();
}
if(\envPHP\service\PSC::isPermitted('employees_schedule_edit')) {
    $isReadOnly = 'false';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" href="/favicon.ico">
    <title>all-ok-shedule-vue</title>
    <link href="css/app.b8902836.css" rel="preload" as="style">
    <link href="css/chunk-vendors.3ebefe38.css" rel="preload" as="style">
    <link href="js/app.073497d8.js" rel="preload" as="script">
    <link href="js/chunk-vendors.f03d975f.js" rel="preload" as="script">
    <link href="css/chunk-vendors.3ebefe38.css" rel="stylesheet">
    <link href="css/app.b8902836.css" rel="stylesheet">
</head>
<body>
<noscript><strong>We're sorry but all-ok-shedule-vue doesn't work properly without JavaScript enabled. Please enable it
        to continue.</strong></noscript>

<div id="schedule" get-token-url="/users/get_token" hour-start="<?=getGlobalConfigVar('TOS')['hours_schedule']['start']?>" hour-end="<?=getGlobalConfigVar('TOS')['hours_schedule']['end']?>" api-base-url="<?=conf('BASE.api2_front_addr')?>" is-read-only="<?=$isReadOnly?>"></div>
<div style="height: 5px; width: 100%"></div>
<script src="js/chunk-vendors.f03d975f.js"></script>
<script src="js/app.073497d8.js"></script>
</body>
</html>