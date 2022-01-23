<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 10.05.2019
 * Time: 16:42
 */

namespace envPHP\service;


use envPHP\structs\Client;
use envPHP\structs\Employee;
use envPHP\structs\LimitDeactivate;

class LimitControlDeactivateDays
{
    /**
     * @param Client $agreement
     * @param $days
     * @param Employee $employee
     *
     * @return bool | \Exception
     */
   public static function set(Client $agreement, $days, Employee $employee) {
        dbConn()->query("INSERT INTO `client_disable_days` (created, created_employee, client, days)
            VALUES (NOW(), {$employee->getId()}, {$agreement->getId()}, '$days')");
       BillingDisableDay::recalcDisableDay($agreement->getId());
        if(dbConn()->error) {
            throw new \Exception(dbConn()->error);
        }
        return dbConn()->insert_id;
   }

    /**
     * @param Client $agreement
     *
     * @return LimitDeactivate
     */
   public static function get(Client $agreement) {
        return (new LimitDeactivate())->fillByClient($agreement);
   }
}