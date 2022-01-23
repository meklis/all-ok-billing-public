<?php
$rank = 21;
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init(true);
echo $auth->getToken();