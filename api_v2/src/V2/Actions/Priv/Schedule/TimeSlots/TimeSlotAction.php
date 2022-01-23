<?php


namespace Api\V2\Actions\Priv\Schedule\TimeSlots;


use Api\V2\Actions\Action;
use envPHP\Schedule\TimeSlot;
use Psr\Log\LoggerInterface;

abstract class TimeSlotAction extends Action
{
    /**
     * @var TimeSlot
     */
    protected $timeSlot;
    function __construct(LoggerInterface $logger, TimeSlot $timeSlot)
    {
        $this->timeSlot = $timeSlot;
        parent::__construct($logger);
    }
}