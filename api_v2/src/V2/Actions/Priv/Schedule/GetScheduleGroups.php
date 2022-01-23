<?php

namespace Api\V2\Actions\Priv\Schedule;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\Schedule\CalendarType;
use envPHP\Schedule\ScheduleAddressGroups;
use envPHP\structs\Employee;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetScheduleGroups extends Action
{
    protected function action(): Response
    {
        $objs = ScheduleAddressGroups::getAll();
        $arr = [];
        foreach ($objs as $e) {
            $arr[] = $e->getAsArray();
        }
        return $this->respondWithData($arr);
    }
}