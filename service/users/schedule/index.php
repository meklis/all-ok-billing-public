<?php
$rank = 20;
$table = "<center><h3>По Вашему запросу пользователей не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


$isReadOnly = 'true';
if(!\envPHP\service\PSC::isPermitted('employees_schedule_show')) {
    pageNotPermittedAction();
}
if(\envPHP\service\PSC::isPermitted('employees_schedule_edit')) {
    $isReadOnly = 'false';
}

?>

<?=tpl('head', ['title'=>''])?>
<iframe style="border: 0; width: 100%; height: 100%; height: 700px; overflow: hidden" src="/users/schedule/schedule"></iframe>
<?=tpl('footer')?>
