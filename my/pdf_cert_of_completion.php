<?php

use envPHP\classes\std;

require(__DIR__ . "/../envPHP/load.php");

session_start();

$form = [
  'id' => '',
];
std::Request($form);


if(!isset($_SESSION['uid']) || !$_SESSION['uid']) {
    header("HTTP/1.0 403 Forbidden");
    die("You must auth first");
}
$uid = $_SESSION['uid'];
$form['id'] = (int) $form['id'];
if(dbConn()->query("SELECT * FROM questions_full WHERE report_id = '{$form['id']}' and agreement = '$uid'")->num_rows == 0) {
    header("HTTP/1.0 404 Not Found");
    die("File not found or permission denied");
}
$cfg = getGlobalConfigVar('CERT_OF_COMPLETION');
if(!$cfg['enabled']) {
    header("HTTP/1.0 403 Forbidden");
    die("Loading PDF disabled by administrator");
}
$path = $cfg['subscribed_path'] . "/" . $form['id'] . ".pdf";
if(file_exists($path)) {
    header("Content-type: application/pdf");
    header("Content-Disposition: attachment; filename=certificate_of_complete_{$form['id']}.pdf");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($path);
} else {
    header("HTTP/1.0 404 Not Found");
    echo "File not found";
}
