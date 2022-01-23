<?php

use envPHP\classes\std;

require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init(true);
$form = [
  'file_path' => '',
];
std::Request($form);


if($form['file_path']) {
    $form['file_path'] = trim($form['file_path'], '/');
    $form['file_path'] = '/www/files/' . $form['file_path'];
}
if(strpos($form['file_path'], '..') !== false) {
    header("HTTP/1.0 403 Forbidden");
    echo "Incorrect path";
}

$names = explode("/", $form['file_path']);
$name = $names[count($names) -1];

if(file_exists($form['file_path'])) {
    header("Content-type: application/pdf");
   header("Content-Disposition: attachment; filename=$name");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($form['file_path']);
} else {
    header("HTTP/1.0 404 Not Found");
    echo "File not found";
}