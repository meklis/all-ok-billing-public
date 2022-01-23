<?php
namespace envPHP\classes;
/**
 * Created by PhpStorm.
 * User: Максим
 * Date: 05.08.2017
 * Time: 0:01
 */
interface Idlink
{
    function __construct($ip, $community,$login,$password);
    function setUntagVidOnPort($port, $vlanId);
    function getVlans();
    function setDescription($port,$description);
    function getPortsNum();
}