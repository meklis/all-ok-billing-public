<?php

namespace Api\V2\Actions\Priv\Schedule;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\structs\Employee;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetScheduleEmployees extends Action
{
    protected function action(): Response
    {
        $emploObjs = Employee::getAll();
        $employees = [];
        foreach ($emploObjs as $e) {
            $employees[] = $e->getAsArray();
        }
        return $this->respondWithData($employees);
    }
}