<?php
namespace envPHP\service;
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 29.01.2019
 * Time: 16:47
 */

class creditPeriodStatus
{
    public $message;
    public $code;

    function setMessage($message) {
        $this->message = $message;
        return $this;
    }
    function setCode($code) {
        $this->code = $code;
        return $this;
    }
    const OK = 0;
    const ERR_NOT_DEFINED_OPEN_CREDIT = 1;
    const ERR_DEFINED_DUPLICATES_OPEN_CREDITS = 2;
    const ERR_NEGATIVE_BALANCE_DEFINED = 3;
    const ERR_HAS_ACTIVE_CREDIT = 4;
}