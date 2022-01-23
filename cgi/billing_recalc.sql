BEGIN;
#################### СПИСАНИЕ СРЕДСТВ ######################
### Перерасчет баланса по дневным прайсам
DROP TABLE IF EXISTS for_credit;
CREATE TEMPORARY table for_credit
SELECT c.id, ROUND(sum(b.price_day),2) credit, GROUP_CONCAT(CONCAT(b.name, '(', b.price_day, ')')) charge_description, CONCAT('[', GROUP_CONCAT(p.id), ']') price_ids, c.balance last_balance
FROM clients c
         JOIN client_prices p on c.id = p.agreement
         JOIN bill_prices b on b.id = p.price
WHERE p.time_stop is NULL  and b.recalc_time = 'day'
GROUP BY c.id
ORDER BY credit desc;

#Запишем списание баланса в историю
INSERT INTO billing_charge_off (agreement, price, type, balance, price_ids)
SELECT id, credit * -1, charge_description, last_balance - (credit), price_ids FROM for_credit;

#Обновляем текущее состояние баланса абонента
UPDATE clients c JOIN for_credit p on p.id = c.id
SET c.balance = c.balance - p.credit;

### Перерасчет баланса по месячным прайсам
DROP TABLE IF EXISTS for_credit;
CREATE TEMPORARY table for_credit
SELECT c.id, ROUND(sum(b.price_month),2) credit, GROUP_CONCAT(CONCAT(b.name, '(', b.price_month, ')')) charge_description, CONCAT('[', GROUP_CONCAT(p.id), ']') price_ids, c.balance last_balance
FROM clients c
         JOIN client_prices p on c.id = p.agreement
         JOIN bill_prices b on b.id = p.price
WHERE p.time_stop is NULL  and b.recalc_time = 'month' and DAYOFMONTH(NOW()) = 1
GROUP BY c.id
ORDER BY credit desc;

#Запишем списание баланса в историю
INSERT INTO billing_charge_off (agreement, price, type, balance, price_ids)
SELECT id, credit * -1, charge_description, last_balance - (credit), price_ids FROM for_credit;

#Обновляем текущее состояние баланса абонента
UPDATE clients c JOIN for_credit p on p.id = c.id
SET c.balance = c.balance - p.credit;
COMMIT;

################### ПЕРЕСЧЕТ ДНЕЙ ОТКЛЮЧЕНИЯ ########################
UPDATE client_prices SET disable_day = null ;
#получение дня отключения на основе балансов
DROP TABLE IF EXISTS disable_days;
CREATE TEMPORARY TABLE disable_days ENGINE=MyISAM
SELECT c.agreement agreement_id,
       p.id activation_id,
       if(bp.recalc_time = 'day',
          c.pd,
          first_date(c.pd + INTERVAL 1 MONTH)
           ) disable_day
FROM (
         SELECT
             p.agreement,
             if(
                         bp.work_type = 'day',
                         NOW() + INTERVAL FLOOR(balance / sum(price_day))  DAY,
                         first_date(NOW()) + INTERVAL FLOOR(balance / sum(price_month)) MONTH
                 )  pd
         FROM client_prices p
                  JOIN clients c on c.id = p.agreement
                  JOIN bill_prices bp on bp.id = p.price
         WHERE p.time_stop is null and price_month != 0
         GROUP BY p.agreement
     ) c
         JOIN client_prices p on p.agreement = c.agreement
         JOIN bill_prices bp on bp.id = p.price
WHERE p.time_stop is null;

UPDATE disable_days d
    JOIN (SELECT created, client_id, days
          FROM client_credit cc
                   JOIN clients c on c.id = cc.client_id
          WHERE cc.`status` = 'OPEN' and c.balance < 0) c on c.client_id = d.agreement_id
SET d.disable_day = c.created + INTERVAL c.days DAY;

#Добавление смещения дня отключения по дням смещений
UPDATE disable_days d
    JOIN (
        SELECT p.id activation_id,
               if(oc.days is null, op.days, oc.days) days
        FROM clients c
                 JOIN client_prices p on p.agreement = c.id and p.time_stop is null
                 LEFT JOIN (
            SELECT client agreement_id, days FROM client_disable_days_last
        ) oc on c.id = oc.agreement_id
                 LEFT JOIN (
            SELECT days_to_disable  days, p.agreement agreement_id, p.id activation_id
            FROM client_prices p
                     JOIN bill_prices b on b.id = p.price
            WHERE time_stop is null
        ) op on p.id = op.activation_id
    ) c on c.activation_id = d.activation_id
SET d.disable_day = d.disable_day + INTERVAL c.days DAY
WHERE c.days != 0;


#LOCK TABLES client_prices WRITE, disable_days READ;
UPDATE client_prices
    JOIN disable_days d on d.activation_id = client_prices.id
SET client_prices.disable_day = CONCAT(CAST(d.disable_day as date), ' 00:00:01');
#UNLOCK TABLES ;
