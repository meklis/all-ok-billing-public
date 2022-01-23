<?php
$rank = 21;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init(true);

$token = $auth->getToken();
setcookie('X-Auth-Key', $token,   time()+60*60*24*30,"/");
