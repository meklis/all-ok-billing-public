<?php
namespace envPHP\classes;
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 05.08.2017
 * Time: 0:03
 */
interface Imikrotik
{
    function __construct($ip,$community,$login,$password);
    function addStaticLease($mac,$ip,$interface,$agreement);
    function addArpIp($mac,$ip,$interface,$agreement);
    function delStaticLease($mac, $ip);
    function setQueueSpeed($ip, $speed);
    function removeQueueSpeed($ip);
    function delArpIp($ip, $interface);
}