<?php


namespace envPHP\EventSystem;


abstract class Event implements \SplObserver
{
    abstract function getEventType();
}