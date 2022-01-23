<?php
require_once __DIR__ . "/../../envPHP/load.php";

if(!isset($_POST['data']) || !isset($_POST['signature'])) {echo "Empty data"; exit;}

$agreementId = 0;
foreach (getGlobalConfigVar('LIQPAY_ACCESS') as $providerId => $liqPay) {
    $sign =  base64_encode(sha1($liqPay['private_key'] . $_POST['data'] . $liqPay['private_key'], 1));
    if($sign == $_POST['signature']) {
        $data = json_decode(base64_decode($_POST['data']), true);
        list($agreement, $time) = explode("-", $data['order_id']);
        $agreementId = $agreement;
        break;
    }
}
if(!$agreementId) {
    throw new \Exception("Incorrect sign");
}

file_put_contents("/tmp/liqpay_body.json", base64_decode($_POST['data']));

//Преобразовуем дату
$data = json_decode(base64_decode($_POST['data']), true);

//$data = (array)json_decode('{"action":"pay","payment_id":184199149,"status":"fail","version":3,"type":"buy","paytype":"privat24","public_key":"i29971458300","acq_id":414963,"order_id":"1040-1462130596","liqpay_order_id":"FEF7Z1T31465130707058139","description":"Оплата услуг интернет, счет: 53145","sender_phone":"380634190768","sender_card_mask2":"516875*18","sender_card_bank":"pb","sender_card_country":804,"ip":"37.57.212.100","amount":20.22,"currency":"UAH","sender_commission":0.0,"receiver_commission":0.03,"agent_commission":0.0,"amount_debit":1.0,"amount_credit":1.0,"commission_debit":0.0,"commission_credit":0.03,"currency_debit":"UAH","currency_credit":"UAH","sender_bonus":0.0,"amount_bonus":0.0,"mpi_eci":"7","is_3ds":false,"customer":"1040","transaction_id":184199149}');
//Коннектимся к БД
$sql = dbConn();
$sql->set_charset("utf8");

//Пишем в базу весь борщ
$test  = $sql->query("INSERT INTO liqpay (action,payment_id,status,paytype,acq_id,order_id,liqpay_order_id,description,amount,currency,sender_commission,
receiver_commission,agent_commission,amount_debit,amount_credit,commission_debit,is_3ds,customer,transaction_id) 
VALUES ('".$data['action']."','".$data['payment_id']."','".$data['status']."','".$data['paytype']."',
'".$data['acq_id']."','".$data['order_id']."','".$data['liqpay_order_id']."','".$data['description']."','".$data['amount']."','".$data['currency']."','".$data['sender_commission']."',
'".$data['receiver_commission']."','".$data['agent_commission']."','".$data['amount_debit']."','".$data['amount_credit']."','".$data['commission_debit']."',
'".$data['is_3ds']."','".$data['customer']."','".$data['transaction_id']."')");

if(!$test) {
	syslog(LOG_ERR, "Liqpay insert pay err: ". $sql->error);
	echo $sql->error."\n";
}
//Если платеж успешный
if($data['status'] == 'success') {
	list($agreement, $time) = explode("-", $data['order_id']);
    @json_decode(envPHP\classes\std::sendRequest(getGlobalConfigVar('BASE')['api_addr'] . "/payment/add", ['agreement'=>$agreement, 'money'=>$data['amount'],  'comment'=>'Оплата через LiqPay', 'paymentType'=>'LiqPay', 'payment_id'=>$data['order_id'], 'debug_info'=>json_encode(['status'=>$data['status'], 'order_id'=>$data['order_id'], 'paytype'=>$data['paytype'], 'liqpay_order_id'=>$data['liqpay_order_id']])]));
} 

//Если не успешный
if($data['status'] != 'success') {
	//Проверяем наличие платежа
		$sql->query("DELETE FROM paymants WHERE payment_id = '".$data['order_id']."'");
}


