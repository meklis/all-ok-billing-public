<?php


namespace envPHP\service\Customer;


use envPHP\service\activations;
use envPHP\service\Exceptions\NotFoundException;
use envPHP\service\ServiceDataWrapper;

class Activation extends ServiceDataWrapper
{
    protected $editableFields = [
        'bindings_exist' => ['', 'bool'],
    ];
    function get($activationId) {
        $psth = dbConnPDO()->prepare("SELECT  p.id
                , cast(p.time_start as date) time_start
                ,cast(p.time_stop as date) time_stop
                , b.`name` price_name
                , b.id price_id
                , b.price_day
                , IFNULL(binds, false) bindings_exist
                , b.price_month
                , b.recalc_time
                , cast(p.disable_day_static as date) disable_day_static
                , cast(p.disable_day as date) disable_day
                , p.agreement customer_id          
                FROM client_prices p 
                JOIN bill_prices b on b.id  = p.price 
                LEFT JOIN (
                SELECT DISTINCT activation,  true  binds FROM eq_bindings GROUP BY activation
                UNION 
                SELECT DISTINCT activation,  true binds FROM trinity_bindings GROUP BY activation
                ) bd on bd.activation = p.id 
                WHERE  p.id  = 8 order by id desc 
        ");
        $psth->execute([$activationId]);
        if($psth->rowCount() == 0) {
            throw new NotFoundException("Activation with id {$activationId} not found", 404, 'customer');
        }
        return $this->wrapForResponse($psth->fetchAll()[0]);
    }
    function getByAgreement($customerId) {
        $psth = dbConnPDO()->prepare("SELECT  p.id
                , cast(p.time_start as date) time_start
                ,cast(p.time_stop as date) time_stop
                , b.`name` price_name
                , b.id price_id
                , b.price_day
                , IFNULL(binds, false) bindings_exist
                , b.price_month
                , b.recalc_time
                , cast(p.disable_day_static as date) disable_day_static
                , cast(p.disable_day as date) disable_day
                , p.agreement customer_id                
                FROM client_prices p 
                JOIN bill_prices b on b.id  = p.price 
                LEFT JOIN (
                SELECT DISTINCT activation,  true  binds FROM eq_bindings GROUP BY activation
                UNION 
                SELECT DISTINCT activation,  true binds FROM trinity_bindings GROUP BY activation
                ) bd on bd.activation = p.id 
                WHERE  p.agreement  = ? order by id desc 
        ");
        $psth->execute([$customerId]);
        return $this->wrapArrayForResponse($psth->fetchAll());
    }
    function getPossibleActivations($customerId) {
        $psth = dbConnPDO()->prepare("
                SELECT id, `name`, price_day, price_month, recalc_time, `show`, speed, provider, days_to_disable 
                FROM bill_prices 
                WHERE `show`=1 
                and provider in (SELECT provider FROM clients WHERE id = ?)  
                ORDER BY name ");
        $psth->execute([$customerId]);
        return $this->wrapArrayForResponse($psth->fetchAll());
    }
    function activate($customerId, $priceId, $employeeId, $sendSMS) {
        return activations::activate($customerId, $priceId, $employeeId, $sendSMS);
    }
    function frost($activationId, $employeeId, $sendSMS = false) {
        return activations::frost($activationId, $employeeId, $sendSMS );
    }
    function defrost($activationId, $employeeId, $sendSMS = false) {
        return activations::defrost($activationId, $employeeId, $sendSMS );
    }
    function deactivate($activationId, $employeeId, $sendSMS = false ) {
        return activations::deactivate($activationId, $employeeId, $sendSMS );
    }
}