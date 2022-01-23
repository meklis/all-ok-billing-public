#Подсчитываем дневной прайс для каждого абонента
BEGIN;
DROP TABLE IF EXISTS for_credit;
CREATE TEMPORARY table for_credit
    SELECT c.id, ROUND(sum(b.price_month),2) credit
    FROM clients c
      JOIN client_prices p on c.id = p.agreement
      JOIN bill_prices b on b.id = p.price
    WHERE p.time_stop is NULL and b.recalc_time = 'month'
    GROUP BY c.id
    ORDER BY credit desc;

INSERT INTO billing_charge_off (agreement, price)
  SELECT id, credit FROM for_credit;

#Обновляем текущее состояние баланса абонента
UPDATE clients c JOIN for_credit p on p.id = c.id
SET c.balance = c.balance - p.credit;

#Сгенерируем таблицу суммы отключений
DROP TABLE IF EXISTS bill_disable_days ;
CREATE TEMPORARY TABLE bill_disable_days ENGINE = MyISAM
SELECT c.id, if(ofclient.days is null, ofprice.days, ofclient.days) days, if(ofclient.days is null, ofprice.days, ofclient.days) * ofprice.sum_day sum_to_dis
FROM clients c
JOIN (
     SELECT max(days_to_disable) days, p.agreement, sum(b.price_month / DAYOFMONTH(LAST_DAY(NOW()))) sum_day
     FROM client_prices p
     JOIN bill_prices b on b.id = p.price
     WHERE time_stop is null
     GROUP BY p.agreement
   ) ofprice on ofprice.agreement = c.id
LEFT JOIN (SELECT max(id) id, client FROM client_disable_days GROUP BY client) last_day on last_day.client = c.id
LEFT JOIN client_disable_days ofclient on ofclient.client = c.id and ofclient.id = last_day.id;


#Генерация задач на отключение для абонентов, которые могут отключится автоматом
INSERT INTO shedule (generator, method, start, request)
  SELECT 18, 'activation/frostWithCheckBalance', NOW()+INTERVAL 10 HOUR , concat('{"employee":20,"activation":', pr.id , '}')
  FROM clients c
    JOIN bill_disable_days min_to_disable on min_to_disable.id = c.id
    JOIN for_credit cr on cr.id = c.id
    JOIN client_prices pr on pr.agreement = c.id
    JOIN bill_prices bp on bp.id = pr.price
    JOIN (
			SELECT DISTINCT activation FROM eq_bindings
			UNION
			SELECT distinct activation FROM trinity_bindings
		)  b on b.activation = pr.id
    LEFT JOIN client_credit cl_cr on cl_cr.client_id = c.id and cl_cr.`status` = 'OPEN'
  WHERE IF(cl_cr.client_id is not null, cl_cr.amount + c.balance, c.balance) - cr.credit < min_to_disable.sum_to_dis
        and c.enable_credit = 1
        and pr.time_stop is null
				and work_type in ('inet', 'trinity');

#Создание заявки для абонентов, которых не возможно отключить автоматом
INSERT INTO shedule (generator, method, start, request)
  SELECT 18, 'question/create', NOW()+INTERVAL 8 HOUR, concat('{"employee":20,"reason":8,"agreement":', c.id,'}')
  FROM clients c
    JOIN bill_disable_days min_to_disable on min_to_disable.id = c.id
    JOIN for_credit cr on cr.id = c.id
    JOIN client_prices pr on pr.agreement = c.id
    JOIN bill_prices bp on bp.id = pr.price
    LEFT JOIN client_credit cl_cr on cl_cr.client_id = c.id and cl_cr.`status` = 'OPEN'
    LEFT JOIN (
                SELECT DISTINCT agreement
                FROM questions_full q
                WHERE  q.reason_id = 8  and q.created > NOW() - INTERVAL 14 DAY and (q.`report_status` is null OR report_status in ('IN_PROCESS', 'CANCEL') )
              ) quest on quest.agreement = c.id
  WHERE
    IF(cl_cr.client_id is not null, cl_cr.amount + c.balance, c.balance) - cr.credit < min_to_disable.sum_to_dis
    and c.enable_credit = 1
    and pr.time_stop is null
		and work_type in ('question')
    and quest.agreement is null;

COMMIT;
