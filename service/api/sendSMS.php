<?php 
/* 
Интерфейс для отправки СМС сообщений
*/
//Подключаем конфиг$DATABASE
require_once __DIR__ . "/../../envPHP/load.php";

$form = [
'type'=>1,
'message'=>'',
'phone'=>''
];

//Преобразование $_REQUEST 
if(isset($_REQUEST)) foreach($_REQUEST as $k=>$v) $form[$k]=addslashes($v);

//Проверка корректности данных 
if($form['message'] == '' || $form['phone'] == '') {
	$response = [
		'Status'=>1,
		'msgStatus'=>'Были получены не все данные'
	];
	goto response;
}

//Подгоним формат телефона
$phone = preg_replace("/[^0-9]/", '', $form['phone']);
if(strlen($phone) == 10) $phone = '38'.$phone;
if(strlen($phone) == 9) $phone = '380'.$phone;

//Обработает сообщение 
$message = trim(rus2lat($form['message']));

$sql = dbConn();
$test = $sql->query("INSERT INTO smsForSend(type,phone,message) values('{$form['type']}','{$phone}','{$message}')");
if(!$test) {
		$response = [
		'Status'=>2,
		'msgStatus'=>$sql->error
	];
	goto response;
} else {
	$response = [
		'Status'=>0,
		'msgStatus'=>"Отправлено в очередь"
	];
	goto response;
}
 response:
 echo json_encode($response, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );