<?php

namespace envPHP\service;

use envPHP\classes\std;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 14.08.2017
 * Time: 22:20
 */
class activations
{
    static protected $bindObj = null;

    static function frost($activationId, $employeeId, $sendSMS = true)
    {
        $activationInfo = dbConnPDO()->query("SELECT agreement, price, b.name price_name, b.work_type FROM client_prices p JOIN bill_prices b on b.id = p.price  and  p.id = $activationId")->fetchAll();
        if (!count($activationInfo)) {
            throw new \Exception("Activation with id $activationId not found");
        }
        $activationInfo = $activationInfo[0];
        $mustRecalcBalance = false;
        $mustDiactivate = false;
        $mustSendSms = false;
        try {
            try {
                switch ($activationInfo['work_type']) {
                    case 'inet':
                        $bindings = self::bindDB()->getBindingsByActivation($activationId);
                        foreach ($bindings as $binding) {
                            $device = new bindingsDevice($binding['id']);
                            $device->delBinding();
                        }
                        $mustRecalcBalance = true;
                        $mustDiactivate = true;
                        $mustSendSms = true;
                        break;
                    case 'trinity':
                        $bindings = self::bindDB()->getBindingsByActivation($activationId);
                        foreach ($bindings as $binding) {
                            TrinityControl::frost($binding['id']);
                        }
                        $mustRecalcBalance = true;
                        $mustDiactivate = true;
                        $mustSendSms = true;
                        break;
                    case 'question':
                        if (in_array($employeeId, [
                            getGlobalConfigVar('BASE')['billing_user_id'],
                            getGlobalConfigVar('BASE')['system_user_id'],
                        ])) {
                            self::createDeActivateQuestion($activationId, $employeeId);
                        }
                        break;
                }
            } catch (\Exception $e) {
                throw  new \Exception($e. $e->getTraceAsString());
            }

            if($mustDiactivate) {
                dbConnPDO()->query("UPDATE client_prices SET time_stop = NOW(), deact_employee_id = $employeeId WHERE id = $activationId");
                dbConnPDO()->query("UPDATE client_prices SET time_stop = NOW(), deact_employee_id = $employeeId WHERE parent = $activationId");
            }
            if ($sendSMS && $mustSendSms) BillingMessage::sendDeactivateSMS($activationId);

            if($mustRecalcBalance) {
                $sth = dbConnPDO()->prepare("SELECT agreement, price FROM client_prices WHERE id = ?");
                $sth->execute([$activationId]);
                $d = $sth->fetch();
                $minus = self::calcMinusForStartPrice($d['price']);
                if ($minus !== 0) {
                    self::adjustClientBalance($d['agreement'], $minus, 'Возмещение баланса после приостановки услуги, id: ' . $activationId);
                }
                BillingDisableDay::recalcDisableDay($d['agreement']);
            }
            \envPHP\EventSystem\EventRepository::getSelf()->notify('activation:frost', [
                'activation_id' => $activationId,
                'employee_id' => $employeeId,
                'sendSMS' => $sendSMS,
                'activation_info' => $activationInfo,
            ]);
        } catch (\Exception $e) {
            throw  new \Exception($e);
        }
        return true;
    }

    static function getActivePrices($agreementId)
    {
        $data = dbConnPDO()->query("SELECT pr.id 
                    FROM `client_prices` pr 
                    JOIN eq_bindings b on b.activation = pr.id
                    WHERE pr.agreement = '$agreementId' and pr.time_stop is null ");
        $resp = [];
        while ($d = $data->fetch()) {
            $resp[] = $d['id'];
        }
        return $resp;
    }

    static function frostWithCheckBalance($activationId, $employeeId)
    {
        if (dbConnPDO()->query("SELECT id FROM client_prices WHERE disable_day > NOW() and id = '{$activationId}';")->rowCount() > 0) {
            throw  new \Exception("Agreement has a positive balance, canceling", 403);
        }
        return self::frost($activationId, $employeeId);
    }

    static function defrost($activationId, $employeeId, $sendSMS = true, $newActivationPrice = 0)
    {
        //OldActivationData
        $activation = dbConnPDO()->query("SELECT agreement, price, b.name price_name, b.work_type FROM client_prices p JOIN bill_prices b on b.id = p.price  and  p.id = $activationId")->fetchAll();
        if (!count($activation)) {
            throw new \Exception("Activation with id $activationId not found");
        }

        $activation = $activation[0];
        //Change price if setted
        if ($newActivationPrice) {
            $activation['price'] = $newActivationPrice;
        }
        $newActivationId = 0;
        $apiResponse = [];
        switch ($activation['work_type']) {
            case 'inet':
                //Create activation
                dbConnPDO()->beginTransaction();
                dbConnPDO()->query("INSERT INTO client_prices (agreement, price, time_start, act_employee_id) 
            VALUES ('{$activation['agreement']}', '{$activation['price']}', NOW(), '{$employeeId}');");
                $newActivationId = dbConnPDO()->lastInsertId();
                $apiResponse= [
                  'type' => 'inet',
                  'activation_id' => $newActivationId,
                ];
                //Create parent activations if exists
                dbConnPDO()->query("INSERT INTO client_prices (agreement, price, time_start, act_employee_id, parent)
                                  SELECT agreement, price, NOW(), $employeeId, $newActivationId FROM client_prices WHERE parent = $activationId");
                $bindings = self::bindDB()->getBindingsByActivation($activationId, true);
                foreach ($bindings as $binding) {
                    self::bindDB()->editBinding($binding['id'], $employeeId, $newActivationId);
                    try {
                        $device = new bindingsDevice($binding['id']);
                        $device->addBinding();
                    } catch (\Exception $e) {
                        dbConnPDO()->rollBack();
                        throw  new \Exception(__METHOD__ . "->" . $e->getMessage());
                    }
                }
                dbConnPDO()->commit();
                break;
            case 'trinity':
                std::msg("Defined work type as trinity, defrosting...");
                //Create activation
                dbConnPDO()->beginTransaction();
                dbConnPDO()->query("INSERT INTO client_prices (agreement, price, time_start, act_employee_id) 
            VALUES ('{$activation['agreement']}', '{$activation['price']}', NOW(), '{$employeeId}');");
                $newActivationId = dbConnPDO()->lastInsertId();
                $apiResponse= [
                    'type' => 'trinity',
                    'activation_id' => $newActivationId,
                ];
                //Create parent activations if exists
                dbConnPDO()->query("INSERT INTO client_prices (agreement, price, time_start, act_employee_id, parent)
                                  SELECT agreement, price, NOW(), $employeeId, $newActivationId FROM client_prices WHERE parent = $activationId");
                $bindings = self::bindDB()->getBindingsByActivation($activationId, true);
                foreach ($bindings as $binding) {
                    try {
                        TrinityControl::defrost($activationId, $newActivationId, $binding['id']);
                    } catch (\Exception $e) {
                        std::msg("Error defrost activation: " . $e->getMessage());
                        dbConnPDO()->rollBack();
                        throw new \Exception($e->getMessage());
                    }
                }
                dbConnPDO()->commit();
                break;
            case 'question':
                if (in_array($employeeId, [
                    getGlobalConfigVar('BASE')['billing_user_id'],
                    getGlobalConfigVar('BASE')['system_user_id'],
                ])) {
                    $questId = self::createActivateQuestion($activationId, $employeeId);
                    $apiResponse= [
                        'type' => 'question',
                        'activation_id' => $questId,
                    ];
                }
                break;
        }
        //Пересчет балансов для месячных прайсов
        if ($newActivationId) {
            $sth = dbConnPDO()->prepare("SELECT agreement, price FROM client_prices WHERE id = ?");
            $sth->execute([$newActivationId]);
            $d = $sth->fetchAll()[0];
            $minus = self::calcMinusForStartPrice($d['price']);
            if ($minus !== 0) {
                $minus *= -1;
                self::adjustClientBalance($d['agreement'], $minus, "Оплата текущего месяца (до конца месяца) по прайсу ID: {$newActivationId}");
            }
        }
        BillingDisableDay::recalcDisableDay($activation['agreement']);
        \envPHP\EventSystem\EventRepository::getSelf()->notify('activation:defrost', [
            'activation_id' => $activationId,
            'employee_id' => $employeeId,
            'sendSMS' => $sendSMS,
            'id' => $newActivationId,
            'response' => $apiResponse,
        ]);
        return $apiResponse;
    }

    private static function createActivateQuestion($activationId, $employeeId)
    {
        //Create question for activate
        $agreementFetch = dbConnPDO()->query("SELECT DISTINCT  c.id, cc.value phone 
        FROM client_prices pr 
        JOIN clients c on c.id = pr.agreement 
        JOIN client_contacts cc on c.id = cc.agreement_id and cc.type = 'PHONE' and cc.main = 1
        WHERE pr.id = '$activationId' LIMIT 1");
        if ($agreementFetch->rowCount() == 0) {
            throw new \Exception("Not found frosted activation for creating questions");
        }
        $date = (new \DateTime())->add(new \DateInterval('P1D'))->format("Y-m-d") . " 08:00:00";
        $agree = $agreementFetch->fetchAll()[0];
        return Question::create(
            (new \envPHP\structs\Client())->fillById($agree['id']),
            6,
            (new \envPHP\structs\Employee())->fillById($employeeId),
            $agree['phone'],
            "Активировать услугу после оплаты долга",
            $date
        );
    }

    private static function createDeActivateQuestion($activationId, $employeeId)
    {
        //Create question for activate
        $agreementFetch = dbConnPDO()->query("SELECT c.id, cc.value phone, p.id activation_id, if(quest.agreement is null, 'NO_QUESTIONS', 'QUESTION_EARLY_CREATED') quest_status, bp.recalc_time
                FROM clients c
				JOIN client_prices p on p.agreement = c.id 
				JOIN bill_prices bp on bp.id = p.price
                JOIN client_contacts cc on c.id = cc.agreement_id and cc.type = 'PHONE' and cc.main = 1
				LEFT JOIN  (
						SELECT DISTINCT agreement
						FROM questions_full q
						WHERE  q.reason_id = 8  and q.created > NOW() - INTERVAL 7 DAY and (q.`report_status` is null OR report_status in ('IN_PROCESS', 'CANCEL') )
				) quest on quest.agreement = c.id
                WHERE bp.work_type = 'question' and p.time_stop is null   and p.id = '{$activationId}'");
        if ($agreementFetch->rowCount() == 0) {
            throw new \Exception("activation not exist or incorrect");
        }

        $date = (new \DateTime())->format("Y-m-d") . " 17:00:00";
        $agree = $agreementFetch->fetchAll()[0];
        if($agree['quest_status'] == 'QUESTION_EARLY_CREATED') {
                return  'QUESTION_EARLY_CREATED';
        }
        if(date("d") === "01" && $agree['recalc_time'] == 'month') {
            return Question::create(
                (new \envPHP\structs\Client())->fillById($agree['id']),
                8,
                (new \envPHP\structs\Employee())->fillById($employeeId),
                $agree['phone'],
                "",
                $date
            );
        } elseif ($agree['recalc_time'] == 'day') {
            return Question::create(
                (new \envPHP\structs\Client())->fillById($agree['id']),
                8,
                (new \envPHP\structs\Employee())->fillById($employeeId),
                $agree['phone'],
                "",
                $date
            );
        } else {
            return "CREATE_QUESTION_BLOCKED_BY_RULES";
        }
    }

    static function deactivate($activationId, $employeeId, $sendSMS = true)
    {
        //Test deactivation
        $sth = dbConnPDO()->prepare("SELECT * FROM client_prices WHERE time_stop is null and id = ?");
        $sth->execute([$activationId]);
        if ($sth->rowCount() == 0) {
            throw new \Exception("Activation is disabled earlier or not exist");
        }
        if ($bindings = self::bindDB()->getBindingsByActivation($activationId)) {
            foreach ($bindings as $binding) {
                switch ($binding['type']) {
                    case 'inet':
                        try {
                            $device = new bindingsDevice($binding['id']);
                            $device->delBinding();
                            self::bindDB()->deleteBinding($binding['id']);
                        } catch (\Exception $e) {
                            throw new \Exception($e->getMessage());
                        }
                        break;
                    case 'trinity':
                        try {
                            TrinityControl::deregBindById($binding['id']);
                        } catch (\Exception $e) {
                            std::msg("Error deactivate trinity activation: " . $e->getMessage());
                            throw new \Exception($e->getMessage());
                        }
                        break;
                }
            }
        }
        dbConnPDO()->query("UPDATE client_prices SET time_stop = NOW(), deact_employee_id = $employeeId, parent = null WHERE id = $activationId");
        dbConnPDO()->query("UPDATE client_prices SET time_stop = NOW(), deact_employee_id = $employeeId, parent = null WHERE parent = $activationId");

        //For month balance recalculation
        $sth = dbConnPDO()->prepare("SELECT agreement, price FROM client_prices WHERE id = ?");
        $sth->execute([$activationId]);
        $d = $sth->fetchAll()[0];
        $minus = self::calcMinusForStartPrice($d['price']);
        if ($minus !== 0) {
            self::adjustClientBalance($d['agreement'], $minus, "Возмещение баланса после деактивации услуги, ID: {$activationId}");
        }
        BillingDisableDay::recalcDisableDay($d['agreement']);
        if ($sendSMS) BillingMessage::sendDeactivateSMS($activationId);
        \envPHP\EventSystem\EventRepository::getSelf()->notify('activation:deactivate', [
            'activation_id' => $activationId,
            'employee_id' => $employeeId,
            'sendSMS' => $sendSMS,
        ]);
        return $activationId;
    }

    static function changePrice($activationId, $newActivationPriceId, $employeeId)
    {
        self::frost($activationId, $employeeId, false);
        return self::defrost($activationId, $employeeId, false, $newActivationPriceId);
    }

    static function activate($agreement, $price, $employeeId, $sendSMS = true)
    {
        //Получим информацию о прайсе, можно ли регать его
        $parent = null;
        foreach (getGlobalConfigVar('PARENT_PRICES') as $limit_name => $limit) {
            if (in_array($price, $limit['prices'])) {
                $sth = dbConnPDO()->prepare("SELECT id FROM client_prices WHERE agreement = :agreement and time_stop is null and price in (" . join(",", $limit['parent']) . ") order by id desc LIMIT 1");
                $sth->execute([
                    ':agreement' => $agreement,
                ]);
                $parent = $sth->fetch()['id'];
                if (!$parent) throw new \Exception("Прайс не может быть добавлен без наличия родительского. Добавьте родительский прайс");
            }
        }
        $sth = dbConnPDO()->prepare("INSERT INTO client_prices (agreement,price,time_start,act_employee_id, parent) 
                            VALUES (:agreement, :price, NOW(), :employee, :parent)");
        $sth->execute([
            ':agreement' => $agreement,
            ':price' => $price,
            ':employee' => $employeeId,
            ':parent' => $parent,
        ]);
        $insertedId = dbConnPDO()->lastInsertId();
        \envPHP\EventSystem\EventRepository::getSelf()->notify('activation:activate', [
            'agreement_id' => $agreement,
            'price_id' => $price,
            'employee_id' => $employeeId,
            'parent' => $parent,
        ]);
        if (!$insertedId) {
            throw new \Exception("Return null id of inserted row");
        }


        //For month balance recalculation
        $minus = self::calcMinusForStartPrice($price);
        if ($minus !== 0) {
            $minus *= -1;
            self::adjustClientBalance($agreement, $minus, "Оплата услуги до конца месяца, ID: {$insertedId}");
//            try {
//                creditPeriod::enableCredit($agreement, getGlobalConfigVar('BASE')['billing_user_id']);
//            } catch (\Exception $e) {
//                std::msg("Error enable credit period - " . $e->getMessage());
//            }
        }

        BillingDisableDay::recalcDisableDay($agreement);
        if ($sendSMS) BillingMessage::sendActivateSMS($insertedId);
        return $insertedId;
    }

    /**
     * Списывает сумму с баланса абонента
     *
     * @param $agreement_id
     * @param $amount
     * @param string $type
     * @return bool
     */
    static protected function adjustClientBalance($agreement_id, $amount, $type = '')
    {
        dbConnPDO()->prepare("INSERT INTO billing_charge_off (agreement, price, type) VALUES (?,?,?)")->execute([$agreement_id, $amount, $type]);
        dbConnPDO()->prepare("UPDATE clients SET balance = balance + ? WHERE id = ?")->execute([$amount, $agreement_id]);
        return true;
    }

    /**
     * Возврашает сумму, которую необходимо списать при старте активации
     *
     * @param $priceId
     * @return float|int
     */
    static protected function calcMinusForStartPrice($priceId)
    {
        $func_calcDays = function () {
            $date = \DateTime::createFromFormat('Y-m-d', date("Y-m-t"));
            $diff = (new \DateTime())->diff($date);
            return $diff->days;
        };
        $sth = dbConnPDO()->prepare("SELECT id, name, price_day, price_month, recalc_time FROM `bill_prices` WHERE id = ?");
        $sth->execute([$priceId]);
        $data = $sth->fetch();
        if ($data['recalc_time'] === 'day') {
            return 0;
        }
        $price = $data['price_month'] / date("t");
        return ($func_calcDays() + 1) * $price;
    }


    /**
     * @return bindingsDB|null
     */
    static protected function bindDB()
    {
        if (self::$bindObj) return self::$bindObj;
        self::$bindObj = new bindingsDB();
        return self::$bindObj;
    }
}