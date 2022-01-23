<?php


namespace envPHP\Schedule;


use envPHP\StoreObjectInterface;

class CalendarType implements StoreObjectInterface
{
    public function fillById($id)
    {
        $psth = dbConnPDO()->prepare("SELECT id, created_at createdAt, name, work_type workType, colors FROM schedule_calendar_types WHERE id = ?");
        $psth->execute([$id]);
        foreach ($psth->fetch() as $k=>$v) {
            $this->{$k} = $v;
            if($k === 'colors') {
                $this->{$k} = json_decode($v, true);
            }
        }
        return $this;
    }

    public static function getAll()
    {
        $data = dbConnPDO()->query("SELECT id FROM schedule_calendar_types order by  1")->fetchAll();
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
        return [
            'id' => $this->id,
            'created_at' => $this->createdAt,
            'name' => $this->name,
            'work_type' => $this->workType,
            'colors' => $this->colors,
        ];
    }

    public static function delete($id)
    {
       dbConnPDO()->prepare("DELETE FROM schedule_calendar_types WHERE id = ?")->execute([$id]);
    }

    protected $id = -1;
    protected $createdAt;
    protected $name;
    protected $workType;
    protected $colors;

    public function save() {
        if(!$this->id || $this->id < 0) {
            dbConnPDO()->prepare("
                INSERT INTO schedule_calendar_types (created_at, name, work_type, colors) 
                VALUES (NOW(), ?, ?, ?)
            ")->execute([$this->name, $this->workType, json_encode($this->colors)]);
            $this->id = dbConnPDO()->lastInsertId();
        } else {
            dbConnPDO()->prepare("
                UPDATE schedule_calendar_types set name = ?, work_type = ?, colors = ? WHERE id = ?
            ")->execute([$this->name, $this->workType, json_encode($this->colors), $this->id]);

        }
    }
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return CalendarType
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * @return CalendarType
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return CalendarType
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWorkType()
    {
        return $this->workType;
    }

    /**
     * @param mixed $workType
     * @return CalendarType
     */
    public function setWorkType($workType)
    {
        $this->workType = $workType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * @param mixed $colors
     * @return CalendarType
     */
    public function setColors($colors)
    {
        $this->colors = $colors;
        return $this;
    }


}