<?php


namespace envPHP\Schedule;


use envPHP\StoreObjectInterface;
use envPHP\structs\Employee;

class Schedule implements StoreObjectInterface
{

    protected $id = -1;
    protected $createdAt;
    protected $updatedAt;
    /**
     * @var CalendarType
     */
    protected $calendar;
    /**
     * @var Employee
     */
    protected $employee;
    protected $start;
    protected $end;
    protected $title;
    protected $isAllDay;
    /**
     * @var Employee
     */
    protected $createdEmployeeId;

    /**
     * @var ScheduleAddressGroups[]
     */
    protected $groups;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Schedule
     */
    public function setId(int $id): Schedule
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $start
     * @param $end
     * @param Employee $employee
     * @return self[]
     */
    public static function getByPeriod($start, $end, ?Employee $employee = null)
    {
        $psth = null;
        if ($employee) {
            $psth = dbConnPDO()->prepare("SELECT id FROM schedule_list 
        WHERE ((start BETWEEN ? and ? ) or (end BETWEEN ? and ?) or (start < ? and end > ? ))  and employee_id = ?");
            $psth->execute([$start, $end, $start, $end, $start, $end, $employee->getId()]);
        } else {
            $psth = dbConnPDO()->prepare("SELECT id FROM schedule_list 
        WHERE (start BETWEEN ? and ? ) or (end BETWEEN ? and ?) or (start < ? and end > ? )");
            $psth->execute([$start, $end, $start, $end, $start, $end]);
        }
        $schedules = [];
        foreach ($psth->fetchAll() as $e) {
            $schedules[] = (new self())->fillById($e['id']);
        }
        return $schedules;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     * @return Schedule
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return Schedule
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return CalendarType
     */
    public function getCalendar(): CalendarType
    {
        return $this->calendar;
    }

    /**
     * @param CalendarType $calendar
     * @return Schedule
     */
    public function setCalendar(CalendarType $calendar): Schedule
    {
        $this->calendar = $calendar;
        return $this;
    }

    /**
     * @return Employee
     */
    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    /**
     * @param Employee $employee
     * @return Schedule
     */
    public function setEmployee(Employee $employee): Schedule
    {
        $this->employee = $employee;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param mixed $start
     * @return Schedule
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param mixed $end
     * @return Schedule
     */
    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Schedule
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsAllDay()
    {
        return $this->isAllDay;
    }

    /**
     * @param mixed $isAllDay
     * @return Schedule
     */
    public function setIsAllDay($isAllDay)
    {
        $this->isAllDay = $isAllDay;
        return $this;
    }

    /**
     * @return Employee
     */
    public function getCreatedEmployeeId(): Employee
    {
        return $this->createdEmployeeId;
    }

    /**
     * @param int $createdEmployeeId
     * @return Schedule
     */
    public function setCreatedEmployeeId($createdEmployeeId): Schedule
    {
        $this->createdEmployeeId = $createdEmployeeId;
        return $this;
    }

    /**
     * @return ScheduleAddressGroups[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param ScheduleAddressGroups[] $groups
     * @return Schedule
     */
    public function setGroups(array $groups): Schedule
    {
        $this->groups = $groups;
        return $this;
    }


    public function fillById($id)
    {
        $psth = dbConnPDO()->prepare("SELECT 
                id, 
                created_at createdAt, 
                updated_at updatedAt, 
                calendar_id calendarId, 
                employee_id employeeId,
                start,
                end,
                title,
                is_all_day isAllDay,
                created_employee_id createdEmployeeId
                FROM schedule_list WHERE id = ?");
        $psth->execute([$id]);
        foreach ($psth->fetch() as $k => $v) {
            switch ($k) {
                case 'employeeId':
                    $this->employee = (new Employee())->fillById($v);
                    break;
                case 'calendarId':
                    $this->calendar = (new CalendarType())->fillById($v);
                    break;
                case 'isAllDay':
                    $this->isAllDay = $v ? true : false;
                    break;
                default:
                    $this->{$k} = $v;
            }
        }
        $psth = dbConnPDO()->prepare("SELECT schedule_id, group_id FROM schedule_list_groups WHERE schedule_id = ?");
        $psth->execute([$id]);
        $groups = [];
        foreach ($psth->fetchAll() as $grId) {
            $groups[] = (new ScheduleAddressGroups())->fillById($grId['group_id']);
        }
        $this->groups = $groups;
        return $this;
    }

    public static function getAll()
    {
        $data = dbConnPDO()->query("SELECT id FROM schedule_list order by  1")->fetchAll();
        $arr = [];
        foreach ($data as $d) {
            $type = new self();
            $type->fillById($d['id']);
            $arr[] = $type;
        }
        return $arr;
    }

    public function getAsArray()
    {
        $groups = [];
        foreach ($this->groups as $gr) {
            $groups[] = $gr->getAsArray();
        }
        return [
            'id' => $this->id,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'start' => $this->start,
            'end' => $this->end,
            'is_all_day' => $this->isAllDay,
            'calendar' => $this->calendar->getAsArray(),
            'employee' => $this->employee->getAsArray(),
            'created_employee' => $this->createdEmployeeId,
            'title' => $this->title,
            'groups' => $groups,
        ];
    }

    public static function delete($id)
    {
        dbConnPDO()->prepare("DELETE FROM schedule_list WHERE id = ?")->execute([$id]);
    }


    public function save()
    {
        if (!$this->employee) {
            throw new \Exception("Employee is required");
        }
        if (!$this->calendar) {
            throw new \Exception("Calendar is required");
        }
        if($this->isAllDay) {
            $start = \DateTime::createFromFormat('Y-m-d H:i:s', $this->start);
            $this->start = $start->format("Y-m-d") . " 00:00:00";

            $end = \DateTime::createFromFormat('Y-m-d H:i:s', $this->end);
            $this->end = $end->format("Y-m-d") . " 23:59:59";

        }
        $this->isAllDay = $this->isAllDay ? 1 : 0;

        if (!$this->id || $this->id < 0) {
            dbConnPDO()->prepare("
                INSERT INTO schedule_list (
                           `created_at`, 
                           `updated_at`, 
                           `calendar_id`, 
                           `employee_id`, 
                           `start`, 
                           `end`, 
                           `title`, 
                           `is_all_day`, 
                           `created_employee_id`
                       ) 
                VALUES (
                            NOW(), 
                            NOW(), 
                            ?, # calendar
                            ?, # employee
                            ?, # start
                            ?, # end  
                            ?, # title
                            ?, # is all day 
                            ?  # created employee id
                )
            ")->execute(
                [
                    $this->calendar->getId(),
                    $this->employee->getId(),
                    $this->start,
                    $this->end,
                    $this->title,
                    $this->isAllDay,
                    $this->createdEmployeeId
                ]
            );

            $this->createdAt = date("Y-m-d H:m:s");
            $this->updatedAt = date("Y-m-d H:m:s");
            $this->id = dbConnPDO()->lastInsertId();
        } else {
            dbConnPDO()->prepare("
                UPDATE schedule_list set updated_at = NOW(), 
                                         calendar_id = ?,
                                         employee_id = ?,
                                         start = ?,
                                         end = ? ,
                                         title = ?,
                                         is_all_day = ?
                WHERE id = ?
            ")->execute([$this->calendar->getId(),
                $this->employee->getId(),
                $this->start,
                $this->end,
                $this->title,
                $this->isAllDay ? 1 : 0,
                $this->id]);
        }
        dbConnPDO()->prepare("DELETE FROM schedule_list_groups WHERE schedule_id = ? ")->execute([$this->id]);
        foreach ($this->groups as $group) {
            dbConnPDO()->prepare("INSERT INTO schedule_list_groups (schedule_id, group_id) VALUES (?, ?)")->execute([$this->id, $group->getId()]);
        }
    }

}