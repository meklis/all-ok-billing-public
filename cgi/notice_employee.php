<?php

if (!function_exists('cidrToRange') && !function_exists('dbConn')) {
    require __DIR__ . "/../envPHP/load.php";
}


 
 if(isset($argv[3])) {
	 $notice = new generNotice();
	 $notice->setMail($argv[2]);
	 $notice->setSMS($argv[3]);
	 $notice->send($argv[1]);	 
 }
