<?php


namespace Api\V2\Actions\Priv\Schedule\TimeSlots;


use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetSlotsAction extends TimeSlotAction
{
    protected $form = [
        'start' => '',
        'end' => '',
        'group_id' => 0,
        'employee_id' => 0,
        'status' => 'FREE',
        'work_type' => 'WORK',
    ];
    protected function action(): Response
    {
        $form = $this->request->getQueryParams();
        $this->fillDefaultKeys($this->form, $form);
        if(!$form['start']) {
            $form['start'] = date("Y-m-d") . " 00:00:00";
        }
        if(!$form['end']) {
            $form['end'] = date("Y-m-d") . " 23:59:59";
        }
        if(!$form['group_id']) {
            throw new HttpBadRequestException($this->request, "Field group_id is required");
        }
        $form['work_type'] = explode(',', trim($form['work_type'], ','));
        $form['status'] = explode(',', trim($form['status'], ','));

        $slots = $this->timeSlot->getSlots(
            DateTime::createFromFormat("Y-m-d H:i:s", $form['start'])->getTimestamp(),
            DateTime::createFromFormat("Y-m-d H:i:s", $form['end'])->getTimestamp(),
            (new \envPHP\Schedule\ScheduleAddressGroups())->fillById($form['group_id'])
        );
        $aggregated = $this->timeSlot
            ->addScheduleLayout($slots, $form['work_type'])
            ->addQuestionsLayout($slots)
            ->getRangesSlots($slots);

        return $this->respondWithData(
            array_filter(
                $aggregated,
                function ($e) use ($form) {
                    if(!$form['employee_id']) return true;
                    return $e['employee']['id'] === $form['employee_id'];
                }
            ), $form);
    }

}