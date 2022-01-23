<?php

namespace Api\V2\Actions\Priv\Schedule;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\Schedule\CalendarType;
use envPHP\Schedule\Schedule;
use envPHP\structs\Employee;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetScheduleAction extends Action
{
    protected function action(): Response
    {
        $schedule = (new Schedule())->fillById($this->request->getAttribute('id'));
        return $this->respondWithData($schedule->getAsArray());
    }
}