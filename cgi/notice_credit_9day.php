<?php 
require('/www/envPHP/load.php');


function generSMS($a) {
$message  = "Shanovniy abonent!
Popovnit' vash osoboviy rakhunok {$a['agreement']} na {$a['need_to_pay']} grn. do {$a['disable_day']}";
return rus2lat($message);
}

$mysqli = dbConn();
$data = $mysqli->query("SELECT DISTINCT c.balance, 
c.agreement, 
ph.phone,  
em.email, 
c.notice_mail, 
c.notice_sms,  
DATE_FORMAT(min(ifnull(p.disable_day_static, p.disable_day)), '%d.%m.%Y') disable_day, 
GROUP_CONCAT(bp.`name`) prices, 
count(*) count_prices,
sum(bp.price_month) price_month,
sum(bp.price_month) - c.balance need_to_pay 
FROM clients c 
JOIN client_prices p on p.agreement = c.id and ifnull(p.disable_day_static, p.disable_day) is not null 
JOIN bill_prices bp on bp.id = p.price
LEFT JOIN (SELECT agreement_id, `value` phone FROM client_contacts WHERE main = 1 and type = 'PHONE') ph on ph.agreement_id = c.id 
LEFT JOIN (SELECT agreement_id, `value` email FROM client_contacts WHERE main = 1 and type = 'EMAIL') em on em.agreement_id = c.id  
GROUP BY c.id 
HAVING min(cast(ifnull(p.disable_day_static, p.disable_day) as date)) = CURDATE() + INTERVAL 9 DAY ");


while($d = $data->fetch_assoc()) {
	if($d['notice_sms'] == 1) {
		$message  = generSMS($d);
        envPHP\service\shedule::add(envPHP\service\shedule::SOURCE_NOTIFICATION_GENERATOR,"notification/sendSMS",['phone'=>$d['phone'],'message'=>$message]);
	}
 }
