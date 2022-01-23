<?php

session_start();

$name = $_SESSION['csv']['name'];
$arr = $_SESSION['csv']['data'];
$str = '';
foreach ($arr['head'] as $v) {
    $str .= $v . ";";
}
unset($arr['head']);
foreach ($arr as $v) {
    $str .="\n";
    foreach ($v as $d) {
        $str .= $d . ";";
    }
}
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$name.csv");
header("Pragma: no-cache");
header("Expires: 0");
echo $str;
unset($_SESSION['csv']);
?>