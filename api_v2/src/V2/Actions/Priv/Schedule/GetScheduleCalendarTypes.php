<?php

namespace Api\V2\Actions\Priv\Schedule;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\Schedule\CalendarType;
use envPHP\structs\Employee;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetScheduleCalendarTypes extends Action
{
    protected function action(): Response
    {
        $objs = CalendarType::getAll();
        $arr = [];
        foreach ($objs as $e) {
            $arr[] = $e->getAsArray();
        }
        return $this->respondWithData($arr);
    }
}