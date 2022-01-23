<?php
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

?>
<?=tpl('head', ['title' => ''])?>
<h1 style="color: red; text-align: center; font-size: 72px">403</h1>
    <h3 align="center">Доступ к этой странице ограничен, обратитесь к своему руководителю</h3>
<?=tpl('footer', ['provider'=> CONF_MAIN_SITE_ADDR])?>