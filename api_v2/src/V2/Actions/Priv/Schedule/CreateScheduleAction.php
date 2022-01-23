<?php

namespace Api\V2\Actions\Priv\Schedule;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\Schedule\CalendarType;
use envPHP\Schedule\Schedule;
use envPHP\Schedule\ScheduleAddressGroups;
use envPHP\structs\Employee;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class CreateScheduleAction extends Action
{
    protected $form = [
      'employee_id' => -1,
      'calendar_id' => -1,
      'title' => '',
      'start' => "",
      'end' => '',
      'groups' => null,
        'is_all_day' => null,
    ];
    protected function action(): Response
    {
        $form = $this->getFormData();
        $this->fillDefaultKeys($this->form, $form);
        $schedule = new Schedule();
        if($form['employee_id'] !== -1) {
            $schedule->setEmployee(
                (new Employee())->fillById($form['employee_id'])
            );
        }
        if($form['calendar_id'] !== -1) {
            $schedule->setCalendar(
                (new CalendarType())->fillById($form['calendar_id'])
            );
        }
        if($form['start'] !== '') {
            $schedule->setStart($form['start']);
        }
        if($form['end'] !== '') {
            $schedule->setEnd($form['end']);
        }
        if($form['title'] !== '') {
            $schedule->setTitle($form['title']);
        }
        if($form['is_all_day'] !== null) {
            $schedule->setIsAllDay($form['is_all_day']);
        }
        if(is_array($form['groups'])) {
            $groups = [];
            foreach ($form['groups'] as $grId) {
                $groups[] = (new ScheduleAddressGroups())->fillById($grId);
            }
            $schedule->setGroups($groups);
        }
        $schedule->setCreatedEmployeeId($this->request->getQueryParams()['USER_ID']);
        $schedule->save();
        return $this->respondWithData($schedule->getAsArray());
    }
}