<?php

namespace envPHP\service;
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 20.08.2017
 * Time: 1:38
 */
class payment
{
    static function add($agreement, $money, $paymentType = "custom", $payment_id = "", $comment = "", $debug_info = "") {
        $sql = dbConn();
        $test = $sql->query("INSERT INTO paymants (money, agreement, comment, debug_info, payment_type, payment_id)
          VALUES  ('$money', $agreement, '$comment', '".addslashes($debug_info)."', '$paymentType', '$payment_id')");
        if(!$test) throw new \Exception(__METHOD__ . "->".$sql->error);
        BillingDisableDay::recalcDisableDay($agreement);
        $insert_id = $sql->insert_id;
        if($activations = self::checkActPossibility($agreement)) {
            try {
                foreach ($activations as $act) {
                    shedule::add(19, "activation/defrost", ['employee'=>20, 'activation'=>$act]);
                }
            } catch (\Exception $e) {
                syslog(LOG_ERR, __METHOD__ . "->".$e);
            }
        }
        return $insert_id;
    }
    static function delete($id) {
        $sql = dbConn();
        $agreeId = $sql->query("SELECT agreement FROM paymants WHERE id = $id")->fetch_assoc()['agreement'];
        $test = $sql->query("DELETE FROM paymants WHERE id = $id");
        BillingDisableDay::recalcDisableDay($agreeId);
        if(!$test) throw  new \Exception(__METHOD__ . "->".$sql->error);
        return true;
    }
    static function getByEasyPayId($payment_id) {
        $sql = dbConn();
        return $sql->query("SELECT id FROM paymants WHERE payment_type = 'EasyPay' and payment_id = $payment_id")->fetch_assoc()['id'];
    }
    static function getByCity24Id($payment_id) {
        $sql = dbConn();
        return $sql->query("SELECT id FROM paymants WHERE payment_type = 'City24' and payment_id = $payment_id")->fetch_assoc()['id'];
    }
    static function getByBank24PayId($payment_id) {
        $sql = dbConn();
        return $sql->query("SELECT id FROM paymants WHERE payment_type = 'Bank24' and payment_id = '$payment_id'")->fetch_assoc()['id'];
    }
    static function getByIBoxId($payment_id) {
        $sql = dbConn();
        return $sql->query("SELECT id FROM paymants WHERE payment_type = 'iBox' and payment_id = '$payment_id'")->fetch_assoc()['id'];
    }
    static function getByTimeId($payment_id) {
        $sql = dbConn();
        return $sql->query("SELECT id FROM paymants WHERE payment_type = 'Time' and payment_id = '$payment_id'")->fetch_assoc()['id'];
    }
    static function checkActPossibility($agreement) {
        $sql = dbConn();
        $activations = $sql->query(" 
                                    SELECT pr.id  
                                    FROM clients c 
                                    JOIN client_prices pr on pr.agreement = c.id  
                                    JOIN bill_prices bp on bp.id = pr.price 
                                    JOIN (SELECT DISTINCT activation FROM eq_bindings UNION SELECT DISTINCT activation FROM trinity_bindings) b on b.activation = pr.id  
                                    WHERE pr.time_stop is not null 
                                        and pr.deact_employee_id = 20
                                        and c.enable_credit = 1 
                                    and bp.work_type in ('inet', 'trinity') 
                                        and c.id = '$agreement'
                                        and balance > 0 
                                        UNION 
                                    SELECT pr.id
                                    FROM ( 
                                     SELECT max(pr.id) id, agreement 
                                     FROM client_prices pr  
                                     JOIN bill_prices bp on bp.work_type in ('question') and pr.price = bp.id 
                                     GROUP BY agreement
                                    ) max_price 
                                    JOIN client_prices pr on pr.id = max_price.id 
                                    JOIN clients c on c.id = pr.agreement 
                                    LEFT JOIN (
                                        SELECT DISTINCT agreement FROM questions_full WHERE (report_status != 'DONE' or report_status is null) and created_employee = 20 and created > NOW() - INTERVAL 14 DAY  and reason_id = 6 
                                    ) quest on quest.agreement = c.id 
                                    WHERE pr.agreement = $agreement 
                                    and pr.time_stop is not null 
                                    and quest.agreement is null 
                                    and c.balance > 0 
                                    and c.enable_credit = 1");
        if($activations->num_rows == 0) return false;
        $RESPONSE = [];
        while ($a = $activations->fetch_assoc()) {
            array_push($RESPONSE, $a['id']);
        }
        return $RESPONSE;
    }
}