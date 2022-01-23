<?php


namespace envPHP\Schedule;


use envPHP\StoreObjectInterface;

class ScheduleAddressGroups implements StoreObjectInterface
{
    protected $id;
    protected $createdAt;
    protected $name;
    protected $description;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ScheduleAddressGroups
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
     * @return ScheduleAddressGroups
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
     * @return ScheduleAddressGroups
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return ScheduleAddressGroups
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }



    public function fillById($id)
    {
        $psth = dbConnPDO()->prepare("
            SELECT id, created createdAt, name, description FROM `addr_groups` WHERE id = ? 
        ");
        $psth->execute([$id]);
        foreach ($psth->fetch() as $k=>$v) {
            $this->{$k} = $v;
        }
        return $this;
    }

    public function save()
    {
        if(!$this->id) {
            dbConnPDO()->prepare("
                INSERT INTO addr_groups (created, name, description) 
                VALUES (NOW(), ?, ?)
            ")->execute([$this->name, $this->description]);
            $this->id = dbConnPDO()->lastInsertId();
        } else {
            dbConnPDO()->prepare("
                UPDATE addr_groups set name = ?, description = ? WHERE id = ?
            ")->execute([$this->name, $this->description, $this->id]);
        }
    }

    public static function getAll()
    {
        $data = dbConnPDO()->query("SELECT id FROM addr_groups order by  1")->fetchAll();
        $arr = [];
        foreach ($data as $d) {
            $type = new self();
            $type->fillById($d['id']);
            $arr[] = $type;
        }
        return $arr;
    }

    public static function delete($id)
    {
        dbConnPDO()->prepare("DELETE FROM addr_groups WHERE id = ?")->execute([$id]);
    }

    public function getHousesId() {
        $psth = dbConnPDO()->prepare("SELECT id FROM addr_houses WHERE group_id = ?");
        $psth->execute([$this->id]);
        $resp = [];
        foreach ($psth->fetchAll() as $p) {
            $resp[] = $p['id'];
        }
        return $resp;
    }

    public function getAsArray()
    {
        return [
          'id' => $this->id,
          'name' => $this->name,
          'description'=>$this->description,
        ];
    }

}