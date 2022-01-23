<?php


namespace envPHP\Events;


use envPHP\EventSystem\Event;
use SplSubject;

class DatabaseLogger extends Event
{

    public function update(SplSubject $subject, $event = '*', $data = null)
    {
        dbConnPDO()
            ->prepare("INSERT INTO system_events (`time`, `event_type`, `data`) VALUES (NOW(), ?, ?)")
            ->execute([$event, json_encode($data)]);
    }
    public function getEventType() {
        return "*";
    }

}