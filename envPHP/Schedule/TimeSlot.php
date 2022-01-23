<?php


namespace envPHP\Schedule;


use envPHP\classes\std;

class TimeSlot
{
    /**
     * @var bool Глобальная конфигурация, блок TOS
     */
    protected $conf;
    /**
     * @var ScheduleAddressGroups
     */


    protected $slotSize = 0;

    public function __construct()
    {
        $this->conf = getGlobalConfigVar('TOS');
        if ($this->conf['slot_time'] <= 0) {
            throw new \InvalidArgumentException("Slot time has minus value");
        }
        $this->slotSize = $this->conf['slot_time'] * 60;

    }

    public function roundToSlotSize($time, $ceil = false)
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

    /**
     * Добавляет к слотам слой заявок
     *
     * @param $slots
     * @return $this
     */
    public function addQuestionsLayout(&$slots)
    {

        $questions = $this->getQuestions($slots['start'], $slots['end']);
        foreach ($questions as $question) {
            $currentTime = $question['start'];
            $endTimeStamp = $question['end'];
            while ($endTimeStamp >= $currentTime) {
                if (isset($slots['slots'][$currentTime]['questions'])) {
                    $slots['slots'][$currentTime]['questions'][] = $question;
                }
                $currentTime += $this->slotSize;
            }
        }
        return $this;
    }

    /**
     * Добавляет к слотам слой дежурств
     *
     * @param &array $slots слоты
     * @param array $work_types Массивом передать типы календарей, которые нужно учитывать
     * @return $this
     */
    public function addScheduleLayout(&$slots, $work_types = ['WORK'])
    {
        $schedules = $this->getSchedules($slots['start'], $slots['end'], $slots['group']);
        foreach ($schedules as $schedule) {
            $currentTime = $schedule['start'];
            $endTimeStamp = $schedule['end'];
            if (!in_array($schedule['work_type'], $work_types)) {
                continue;
            }
            while ($endTimeStamp >= $currentTime) {

                if (isset($slots['slots'][$currentTime]['employees'])) {
                    $slots['slots'][$currentTime]['employees'][] = $schedule;
                }
                $currentTime += $this->slotSize;
            }
        }
        return $this;
    }

    /**
     * Возвращает отформатированные слоты на основе размера слота и периода времени.
     * Далее, эти слоты можно использовать для разметки или наполнять данным с помощью методов add****Layout
     *
     * @param int $start
     * @param int $end
     * @param ScheduleAddressGroups|null $group
     * @return array
     */
    public function getSlots(int $start, int $end, ?ScheduleAddressGroups $group = null)
    {
        return [
            'slots' => $this->prepareSlots($start, $end),
            'start' => $start,
            'end' => $end,
            'group' => $group,
        ];
    }

    protected function prepareSlots(int $start, int $end)
    {
        $slots = [];
        $currentTime = $this->roundToSlotSize($start);
        $endTimeStamp = $this->roundToSlotSize($end, true);
        while ($endTimeStamp > $currentTime) {
            $hour = (int) date("H", $currentTime);
            if($hour < $this->conf['hours_schedule']['end'] && $hour >= $this->conf['hours_schedule']['start']) {
                $slots[$currentTime] = [
                    'start' => $currentTime,
                    'end' => $currentTime + $this->slotSize,
                    'status' => 'unknown',
                    'employees' => [],
                    'questions' => [],
                ];
            }
            $currentTime += $this->slotSize;
        }
        return $slots;
    }

    /**
     * Возвращает аггрегированные данные в слотах на основе ранее наложенных слоев
     *
     * @param $slots
     * @return array
     */
    public function aggregateSlotLayouts($slots)
    {
        $loading = [];
        foreach ($slots['slots'] as $key => $slot) {
            $loading[$key] = [
                'start' => $slot['start'],
                'end' => $slot['end'],
                'employees' => [],
                'questions' => [],
            ];
            if (count($slot['employees']) > 0) {
                foreach ($slot['employees'] as $employee) {
                    $groupIds = [];
                    foreach ($employee['groups'] as $group) {
                        $groupIds[] = $group['id'];
                    }

                    $emploLoad = [
                        'schedule_id' => $employee['id'],
                        'id' => $employee['employee']['id'],
                        'name' => $employee['employee']['name'],
                        'start' => $employee['start'],
                        'end' => $employee['end'],
                        'work_type' => $employee['work_type'],
                        'questions' => [],
                    ];
                    if (is_array($slot['questions']) && count($slot['questions']) > 0) {
                        foreach ($slot['questions'] as $question) {
                            //Проверим, относится ли как то эта заявка к мастеру
                            if(count($groupIds) !== 0 && !in_array($question['agreement']['group_id'], $groupIds))  {
                                continue;
                            }

                            //Если нет отвественного - вешаем занятость на всех мастеров
                            if (!isset($question['responsible']['responsible_id'])) {
                                $emploLoad['questions'][] = $question;


                            //Если есть ответственный мастер - кидаем на него
                            } else {
                                if ($question['responsible']['responsible_id'] === $employee['employee']['id']) {
                                    $emploLoad['questions'][] = $question;
                                }
                            }
                        }
                    }
                    $loading[$key]['employees'][] = $emploLoad;
                }
            } else {
                $loading[$key]['questions'] = $slot['questions'];
            }
        }
        foreach ($loading as $key => $slot) {
            $loading[$key]['status'] = 'NO_WORK';
            if (count($slot['questions']) > 0) {
                $loading[$key]['status'] = 'NO_WORKERS';
            } else {
                if (count($slot['employees']) > 0) {
                    $slotStatus = 'LOAD';
                    foreach ($slot['employees'] as $k => $employee) {
                        if (count($employee['questions']) > 1) {
                            $loading[$key]['employees'][$k]['status'] = 'LOAD';
                        } elseif (count($employee['questions']) === 1) {
                            $loading[$key]['employees'][$k]['status'] = 'LOAD';
                        } elseif (count($employee['questions']) === 0) {
                            $loading[$key]['employees'][$k]['status'] = 'FREE';
                            $slotStatus = 'FREE';
                        }
                    }
                    $loading[$key]['status'] = $slotStatus;
                } else {
                    $loading[$key]['status'] = 'NO_WORK';
                }
            }
        }


        return $loading;
    }

    protected function getSchedules(int $start, int $end, ?ScheduleAddressGroups $group = null)
    {
        $schedules = Schedule::getByPeriod(
            date("Y-m-d H:i:s", $start),
            date("Y-m-d H:i:s", $end)
        );
        $response = [];
        foreach ($schedules as $schedule) {
            $allow = false;
            if ($group !== null) {
                $groups = $schedule->getGroups();
                if (!$groups) {
                    $allow = true;
                } else {
                    foreach ($groups as $gr) {
                        if ($group->getId() === $gr->getId()) {
                            $allow = true;
                        }
                    }
                }
            }
            if ($allow) {
                $groups = [];
                foreach ($schedule->getGroups() as $gr) {
                    $groups[] = $gr->getAsArray();
                }
                $response[] = [
                    'id' => $schedule->getId(),
                    'start' => $this->roundToSlotSize(\DateTime::createFromFormat('Y-m-d H:i:s', $schedule->getStart())),
                    'end' => $this->roundToSlotSize(\DateTime::createFromFormat('Y-m-d H:i:s', $schedule->getEnd())),
                    'employee' => $schedule->getEmployee()->getAsArray(),
                    'title' => $schedule->getTitle(),
                    'work_type' => $schedule->getCalendar()->getWorkType(),
                    'groups' => $groups,
                ];
            }
        }
        return $response;
    }

    protected function getQuestions($start, $end)
    {
        $WHERE = " WHERE 
                ((dest_time between ? and ?) or (dest_time + INTERVAL r.reaction_time MINUTE between ? and ?))
        ";
        $reasons = join(",", $this->conf['question_reasons']);
        $psth = dbConnPDO()->prepare("SELECT 
                e.id responsible_id,
                e.name responsible_name,
                f.id, 
                f.agreement agreement_id, 
                c.agreement agreement_code, 
                f.reason_id, 
                f.reason reason_name, 
                f.dest_time start_time, 
                f.dest_time + INTERVAL r.reaction_time MINUTE end_time, 
                c.house house_id, 
                h.group_id
                FROM questions_full f 
                JOIN clients c on c.id = f.agreement 
                JOIN addr_houses h on h.id = c.house 
                JOIN v_reaction_times r on r.reason_id = f.reason_id and r.house_id = h.id 
	            LEFT JOIN employees e on e.id = f.responsible_employee
                $WHERE 
                and (report_status is null or report_status in  ('IN_PROCESS', 'DONE'))
                and f.reason_id in ({$reasons})
                ");
        $psth->execute([
            date("Y-m-d H:i:s", $this->roundToSlotSize($start)),
            date("Y-m-d H:i:s", $this->roundToSlotSize($end, true)),
            date("Y-m-d H:i:s", $this->roundToSlotSize($start)),
            date("Y-m-d H:i:s", $this->roundToSlotSize($end, true)),
        ]);
        $prepare = function ($elem) {
            $responsible = null;
            if ($elem['responsible_id']) {
                $responsible = [
                    'id' => $elem['responsible_id'],
                    'name' => $elem['responsible_name'],
                ];
            }
            return [
                'agreement' => [
                    'id' => $elem['agreement_id'],
                    'code' => $elem['agreement_code'],
                    'house_id' => $elem['house_id'],
                    'group_id' => $elem['group_id'],
                ],
                'start' => $this->roundToSlotSize(\DateTime::createFromFormat('Y-m-d H:i:s', $elem['start_time'])->getTimestamp()),
                'end' => $this->roundToSlotSize(\DateTime::createFromFormat('Y-m-d H:i:s', $elem['end_time'])->getTimestamp(), true),
                'question' => [
                    'id' => $elem['id'],
                    'reason_id' => $elem['reason_id'],
                    'reason_name' => $elem['reason_name'],
                ],
                'responsible' => $responsible,
            ];
        };
        $response = [];
        foreach ($psth->fetchAll() as $p) {
            $response[] = $prepare($p);
        }
        return $response;
    }

    function getEmployeeSlots($slots)
    {
        $resp = [];
        foreach ($this->getSlotStatuses($slots) as $employee) {
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

    function getSlotStatuses($slots)
    {
        $shift = [];
        $schedules = [];
        $response = [];
        foreach ($this->aggregateSlotLayouts($slots) as $k => $slot) {
            foreach ($slot['employees'] as $employee) {
                $schedules[$employee['schedule_id']] = [
                    'id' => $employee['id'],
                    'name' => $employee['name'],
                    'schedule' => [
                        'id' => $employee['schedule_id'],
                        'work_type' => $employee['work_type'],
                        'start' => $employee['start'],
                        'end' => $employee['end'],
                        '_dates' => [
                            'start' => date("Y-m-d H:i:s", $employee['start']),
                            'end' =>  date("Y-m-d H:i:s", $employee['end']),
                        ]
                    ]
                ];
                $shift[$employee['schedule_id']][$k] = $employee['status'];
            }
        }
        foreach ($shift as $k => $slotStatuses) {
            $resp = $schedules[$k];
            $resp['slots'] = $slotStatuses;
            $response[] = $resp;
        }
        return $response;
    }

    function getRangesSlots($slots, $displayStatus = 'FREE')
    {
        $response = [];
        $start = null;
        $lastStatus = '';
        foreach ($this->getSlotStatuses($slots) as $schedule) {
            $countSlots = count($schedule['slots']);
            foreach ($schedule['slots'] as $time => $slotStatus) {
                $countSlots--;
                if (($time > $schedule['schedule']['end'] || $time < $schedule['schedule']['start']) && $start === null) {
                    continue;
                }
                if (($time > $schedule['schedule']['end'] || $time < $schedule['schedule']['start']) && $start !== null) {
                    $slotStatus = 'NO_WORK';
                }
                if ($displayStatus === $slotStatus && $start === null) {
                    $start = $time;
                }

                if (($slotStatus !== $displayStatus || $countSlots <= 0) && $start !== null) {
                    $response[] = [
                        'start' => $start,
                        'end' => $time,
                        '_dates' => [
                            'start' => date("Y-m-d H:i:s", $start),
                            'end' => date("Y-m-d H:i:s", $time),
                        ],
                        'status' => $lastStatus,
                        'employee' => [
                            'id' => $schedule['id'],
                            'name' => $schedule['name'],
                            'schedule' => [
                                'id' => $schedule['schedule']['id'],
                                'work_type' => $schedule['schedule']['work_type'],
                                'start' => $schedule['schedule']['start'],
                                'end' => $schedule['schedule']['end'],
                            ]
                        ]
                    ];

                    $start = null;
                    $lastStatus = $slotStatus;
                }
            }
        }
        return $response;
    }

    function getQuestionSlots($slots, $questionMinutesSize)
    {
        $response = [];
        $questionMinutesSize *= 60;
        foreach ($this->getRangesSlots($slots, 'LOAD') as $slot) {
            foreach ($this->prepareSlots($slot['start'], $slot['end']) as $time => $_) {
                $response[$time] = [
                    'employee_id' => $slot['employee']['id'],
                    'time' => $time,
                    'status' => 'BUSY',
                ];
            }
        }
        foreach ($this->getRangesSlots($slots, 'FREE') as $slot) {
            foreach ($this->prepareSlots($slot['start'], $slot['end']) as $time => $_) {
                $response[$time] = [
                    'employee_id' => $slot['employee']['id'],
                    'time' => $time,
                    'status' => 'NOT-FIT',
                ];
            }
        }
        foreach ($this->getRangesSlots($slots, 'FREE') as $slot) {
            foreach ($this->prepareSlots($slot['start'], $slot['end']) as $time => $_) {
                if (
                    $slot['end'] - $slot['start'] >= $questionMinutesSize &&
                    ($slot['end'] + $this->slotSize) - $time >= $questionMinutesSize
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