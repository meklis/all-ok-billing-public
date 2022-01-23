<?php

namespace Api\V2\Actions\Priv\Schedule;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\Schedule\CalendarType;
use envPHP\Schedule\Schedule;
use envPHP\structs\Employee;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetSchedulesByPeriodAction extends Action
{
    protected $form = [
      'start' => '',
      'end' => '',
      'employee_id' => -1,
    ];
    protected function action(): Response
    {
        $form = $this->request->getQueryParams();
        $this->fillDefaultKeys($this->form, $form);
        if(!$form['start']) {
            $form['start'] = date("Y-m-d H:i:00");
        }
        if(!$form['end']) {
            $form['end'] = date("Y-m-d H:i:00");
        }
        $employee = null;
        if($form['employee_id'] !== -1) {
            $employee = (new Employee())->fillById($form['employee_id']);
        }
        $schedules = [];
        foreach (Schedule::getByPeriod($form['start'], $form['end'], $employee) as $schedule) {
            $schedules[] = $schedule->getAsArray();
        }
        return $this->respondWithData($schedules);
    }
}