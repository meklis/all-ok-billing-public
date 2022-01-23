<?php


namespace envPHP\Schedule;


class SlotCalculation
{

    protected $employeePeriod = [];
    protected $slots;
    protected $slotSize = 0;
    protected $conf;

    /**
     * SlotCalculation constructor.
     * @param array $aggreggatedSlots Список слотов полченных на основе TimeSlot::aggregateSlotLayouts
     */
    function __construct($aggreggatedSlots)
    {

        $this->conf = getGlobalConfigVar('TOS');
        if ($this->conf['slot_time'] <= 0) {
            throw new \InvalidArgumentException("Slot time has minus value");
        }
        $this->slotSize = $this->conf['slot_time'] * 60;
        $this->slots = $aggreggatedSlots;
        $this->employeePeriod = $this->getSlotStatuses();
    }

    private function roundToSlotSize($time, $ceil = false)
    {
        $slotTime = null;
        if ($time instanceof \DateTime) {
            $slotTime = $time->getTimestamp();
        } elseif (is_numeric($time)) {
            $slotTime = $time;
        } else {
            throw new \InvalidArgumentException("Unknown type of argument time");
        }
        if ($ceil) {
            $slotTime = ceil($slotTime / $this->slotSize) * $this->slotSize;
        } else {
            $slotTime = floor($slotTime / $this->slotSize) * $this->slotSize;
        }
        return $slotTime;
    }

    private function prepareSlots(int $start, int $end)
    {
        $slots = [];
        $currentTime = $this->roundToSlotSize($start);
        $endTimeStamp = $this->roundToSlotSize($end, true);
        while ($endTimeStamp > $currentTime) {
            $slots[] = $currentTime;
            $currentTime += $this->slotSize;
        }
        return $slots;
    }

    function getEmployeeSlots()
    {
        $resp = [];
        foreach ($this->employeePeriod as $employee) {
            $slots = [];
            foreach ($employee['slots'] as $time => $status) {
                $slots[] = [
                    'time' => $time,
                    'status' => $status,
                ];
            }
            $e = $employee;
            $e['slots'] = $slots;
            $resp[] = $e;
        }
        return $resp;
    }

    function getSlotStatuses()
    {
        $shift = [];
        $employees = [];
        foreach ($this->slots as $k => $slot) {
            foreach ($slot['employees'] as $employee) {
                $employees[$employee['id']] = [
                    'id' => $employee['id'],
                    'name' => $employee['name'],
                    'schedule' => [
                        'id' => $employee['schedule_id'],
                        'work_type' => $employee['work_type'],
                        'start' => $employee['start'],
                        'end' => $employee['end'],
                    ]
                ];
                $shift[$employee['id']][$k] = $employee['status'];
            }
        }
        $response = [];
        foreach ($shift as $emploId => $slotStatuses) {
            $resp = $employees[$emploId];
            $resp['slots'] = $slotStatuses;
            $response[] = $resp;
        }
        return $response;
    }

    function getRangesSlots($slotStatuses = 'FREE')
    {
        $slots = [];
        foreach ($this->employeePeriod as $employee) {
            $start = null;
            $countSlots = count($employee['slots']);
            $lastStatus = '';
            foreach ($employee['slots'] as $time => $status) {
                $countSlots--;
                if ($status === $slotStatuses && $start === null) {
                    $start = $time;
                } elseif (($status !== $slotStatuses || $countSlots <= 0) && $start !== null) {
                    $slots[] = [
                        'start' => $start,
                        'end' => $time,
                        'status' => $lastStatus,
                        'employee' => [
                            'id' => $employee['id'],
                            'name' => $employee['name'],
                            'schedule' => [
                                'id' => $employee['schedule']['id'],
                                'work_type' => $employee['schedule']['work_type'],
                                'start' => $employee['schedule']['start'],
                                'end' => $employee['schedule']['end'],
                            ]
                        ]
                    ];

                    $start = null;
                }
                $lastStatus = $status;
            }
        }
        return $slots;
    }

    function getQuestionSlots($questionMinutesSize)
    {
        $response = [];
        $questionMinutesSize *= 60;
        foreach ($this->getRangesSlots('LOAD') as $slot) {
            foreach ($this->prepareSlots($slot['start'], $slot['end']) as $time) {
                $response[$time] = [
                    'employee_id' => $slot['employee']['id'],
                    'time' => $time,
                    'status' => 'BUSY',
                ];
            }
        }
        foreach ($this->getRangesSlots('FREE') as $slot) {
            foreach ($this->prepareSlots($slot['start'], $slot['end']) as $time) {
                $response[$time] = [
                    'employee_id' => $slot['employee']['id'],
                    'time' => $time,
                    'status' => 'DOES_NOT_FIT',
                ];
            };
        }
        foreach ($this->getRangesSlots('FREE') as $slot) {
            foreach ($this->prepareSlots($slot['start'], $slot['end']) as $time) {

                if (
                    $slot['end'] - $slot['start'] >= $questionMinutesSize &&
                    ($slot['end']+$this->slotSize) - $time >= $questionMinutesSize
                ) {
                    $response[$time] = [
                        'employee_id' => $slot['employee']['id'],
                        'time' => $time,
                        'status' => 'FREE',
                    ];
                };
            }
        }
        ksort($response);
        return array_values($response);
    }

}