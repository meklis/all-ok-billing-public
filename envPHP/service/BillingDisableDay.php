<?php


namespace envPHP\service;


class BillingDisableDay
{

    protected function __construct() { }

    function buildQueriesForRecalcDisableDay(int $agreement) {
        $queries = [];
        $queries[] = "UPDATE client_prices SET disable_day = null WHERE agreement = '{$agreement}'";
        $queries[] = "DROP TABLE IF EXISTS disable_days;";
        $queries[] = "
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
		WHERE p.time_stop is null and price_month != 0  and c.id = '{$agreement}'
		GROUP BY p.agreement
     ) c
         JOIN client_prices p on p.agreement = c.agreement
         JOIN bill_prices bp on bp.id = p.price
WHERE p.time_stop is null and c.agreement = '{$agreement}';
        ";
        $queries[] = "
        UPDATE disable_days d
    JOIN (SELECT created, client_id, days
          FROM client_credit cc
                   JOIN clients c on c.id = cc.client_id and c.id = '{$agreement}'
          WHERE cc.`status` = 'OPEN' and c.balance < 0 ) c on c.client_id = d.agreement_id
SET d.disable_day = c.created + INTERVAL c.days DAY;";
        $queries[] = "
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
                        WHERE time_stop is null and p.agreement = '{$agreement}'
                    ) op on p.id = op.activation_id
                    WHERE c.id = '${agreement}'
                ) c on c.activation_id = d.activation_id
            SET d.disable_day = d.disable_day + INTERVAL c.days DAY
            WHERE c.days != 0;
        ";
        $queries[] = "UPDATE client_prices  
            JOIN disable_days d on d.activation_id = client_prices.id 
            SET client_prices.disable_day = CONCAT(CAST(d.disable_day as date), ' 00:00:01')
            WHERE client_prices.agreement = $agreement
        ";
        return $queries;
    }

    /**
     * Action to recalculate disable day in activation
     *
     * @param int $agreementId Agreement ID (clients.id)
     * @return bool
     */
    public static function recalcDisableDay($agreementId = 0) {
        $queries = self::buildQueriesForRecalcDisableDay($agreementId);
        foreach ($queries as $query) {
            try {
                dbConnPDO()->exec($query);
            } catch (\Exception $e) {
                if($e->getCode() == 1213) {
                    dbConnPDO()->exec($query);
                } else {
                    throw $e;
                }
            }
        }
        return true;
    }

    /**
     * Return disable day by agreement
     *
     * @param int $agreementId
     * @return string | null
     */
    public static function getByAgreement(int $agreementId) {
        return dbConnPDO()->query("SELECT cast(min(ifnull(p.disable_day_static, p.disable_day)) as date) disable_day 
FROM clients c 
JOIN client_prices p on p.agreement = c.id 
WHERE c.id = '{$agreementId}'")->fetch()['disable_day'];
    }

    /**
     * Return disable day by activation
     *
     * @param int $activationId
     * @return string | null
     */
    public static function getByActivation(int $activationId) {
        return dbConnPDO()->query("SELECT  cast(ifnull(p.disable_day_static, p.disable_day) as date) disable_day 
FROM client_prices   
WHERE  id = '{$activationId}'")->fetch()['disable_day'];
    }
}

