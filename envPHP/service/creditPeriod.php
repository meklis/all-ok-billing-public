<?php
namespace envPHP\service;
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 29.01.2019
 * Time: 16:31
 */

class creditPeriod
{
    static function isEnableCreditAllowed($agreementId) {
        if(dbConnPDO()->query("SELECT * FROM  client_credit WHERE client_id = '$agreementId' and `status` = 'OPEN'")->rowCount() <= 0) {
            return (new creditPeriodStatus())
                ->setCode(creditPeriodStatus::OK)
                ->setMessage("Включение кредитного периода разрешено");
        } else {
           return (new creditPeriodStatus())
                ->setCode(creditPeriodStatus::ERR_HAS_ACTIVE_CREDIT)
                ->setMessage("Аккаунт уже имеет включенный кредитный период");
        }
    }
    static function isDisableCreditAllowed($agreementId) {
        $agreeInfo = dbConnPDO()->query("SELECT cl.balance
                            FROM client_balances_v cl 
                            JOIN client_credit cr on cr.client_id = cl.id 
                            WHERE cr.`status` = 'OPEN' and cl.id = '$agreementId'");
        if($agreeInfo->rowCount() == 0) {
            return (new creditPeriodStatus())
                ->setCode(creditPeriodStatus::ERR_NOT_DEFINED_OPEN_CREDIT)
                ->setMessage("Not defined open credits");
        } elseif ($agreeInfo->rowCount() != 1) {
            throw new \Exception("Критичная ошибка - найдено больше одного активного кредита, работа невозможна");
        } elseif ($agreeInfo->fetch()['balance'] < 0) {
            return (new creditPeriodStatus())
                ->setCode(creditPeriodStatus::ERR_NEGATIVE_BALANCE_DEFINED)
                ->setMessage("Аккаунт имеет отрицательный баланс, отключение кредитного периода запрещено");
        }
        return  (new creditPeriodStatus())
            ->setCode(creditPeriodStatus::OK)
            ->setMessage("Отключение кредитного периода разрешено");
    }
    static function enableCredit($agreementId, $employeeId) {
         if(self::isEnableCreditAllowed($agreementId)->code  != creditPeriodStatus::OK) throw new \Exception("Аккаунт уже имеет активный кредитный период");
         $creditAmount = self::getCreditAmountAllow($agreementId);

         $test = dbConnPDO()->query("INSERT INTO client_credit (created, created_employee, client_id, amount, days, status)
                               VALUES (NOW(), '{$employeeId}', '{$agreementId}', '{$creditAmount}', '". getGlobalConfigVar('CREDIT_PERIOD_COUNT_DAYS') ."', 'OPEN')");
         if($test) {
             BillingDisableDay::recalcDisableDay($agreementId);
             return dbConnPDO()->lastInsertId();
         }  else {
             throw new \Exception("SQL ERR: ".dbConnPDO()->error, dbConnPDO()->errno);
         }
    }
    static function disableCredit($agreementId, $employeeId) {
        $allowStatus = self::isDisableCreditAllowed($agreementId);
        if($allowStatus->code != creditPeriodStatus::OK) {
            throw new \Exception($allowStatus->message, $allowStatus->code);
        }
        $test = dbConnPDO()->query("UPDATE client_credit SET status = 'DIACTIVATED', closed_date=NOW(), closed_employee = '$employeeId' WHERE client_id = '$agreementId' and status = 'OPEN' ");
        if($test) {
            BillingDisableDay::recalcDisableDay($agreementId);
            return true;
        } else {
            throw new \Exception("SQL ERR: ".dbConnPDO()->error, dbConnPDO()->errno);
        }
    }
    static function getCreditAmountAllow($agreementId) {
        $test = dbConnPDO()->query("SELECT a.price 
                                FROM (SELECT sum(price_day) price 
                                FROM clients cl 
                                JOIN `client_prices` pr on pr.agreement = cl.id 
                                JOIN bill_prices bp on bp.id = pr.price 
                                WHERE (cl.id = $agreementId  ) and time_stop is null 
                                GROUP BY cl.id, cl.agreement 
                                UNION ALL 
                                SELECT sum(price_day) price 
                                FROM clients cl 
                                JOIN `client_prices` pr on pr.agreement = cl.id
                                JOIN (SELECT distinct activation FROM eq_bindings UNION SELECT distinct activation from trinity_bindings) b on b.activation = pr.id  
                                JOIN bill_prices bp on bp.id = pr.price 
                                WHERE  cl.id = '$agreementId' and time_stop is not null 
                                GROUP BY cl.id, cl.agreement 
                                ) a 
                                LIMIT 1 ");
        if($test->rowCount() == 0) {
            throw new \Exception("Не удалось подсчитать прайс, не найдены активации. Проверьте, что есть активные или замороженные услуги");
        }
        return $test->fetch()['price'] * getGlobalConfigVar('CREDIT_PERIOD_COUNT_DAYS');
    }

    //@TODO Переписать блок таким образом, что бы выбиралось несколько активаций, а не только одна
    static function enableCreditWithDefrost($agreementId, $employeeId) {
        $allowStatus = self::isEnableCreditAllowed($agreementId);
        if($allowStatus->code != creditPeriodStatus::OK) {
            throw new \Exception($allowStatus->message, $allowStatus->code);
        }
        $activationStatus = dbConnPDO()->query("SELECT * FROM (
		SELECT * FROM (
			SELECT pr.id activation, 'active' status 
			FROM client_prices pr 
			LEFT JOIN eq_bindings b on b.activation = pr.id
			LEFT JOIN trinity_bindings tb on tb.activation = pr.id 
			WHERE agreement = '$agreementId' and time_stop is null  and (b.activation is not null or tb.activation is not null)
			LIMIT 1
		) l
		UNION 
		SELECT * FROM (
			SELECT pr.id activation, 'frosted' status 
			FROM client_prices pr 
			LEFT JOIN eq_bindings b on b.activation = pr.id
			LEFT JOIN trinity_bindings tb on tb.activation = pr.id 
			WHERE agreement = '$agreementId' and time_stop is not null and (b.activation is not null or tb.activation is not null)  
			LIMIT 1
		) l 
) l LIMIT 1 ")->fetch();
        if($activationStatus['status'] == 'frosted') {
            $psth = dbConnPDO()->prepare("
                            SELECT p.id act_id
                FROM client_prices p 
                JOIN (
                    SELECT activation FROM trinity_bindings
                  UNION 
                  SELECT activation FROM eq_bindings
                ) b on b.activation = p.id 
                WHERE time_stop is not null  and p.agreement = ?
            ");
            $psth->execute([$agreementId]);
            foreach ($psth->fetchAll() as $act) {
                activations::defrost($act['act_id'],$employeeId);
            }
            BillingDisableDay::recalcDisableDay($agreementId);
        } elseif ($activationStatus['status'] != 'active') {
            throw new \Exception("Не найдено активных или приостановленных активаций. Сначало подключите услугу");
        }
        self::enableCredit($agreementId,$employeeId);
        return true;
    }
}
