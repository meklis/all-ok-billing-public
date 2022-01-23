<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 10.05.2019
 * Time: 16:46
 */

namespace envPHP\structs;


class Employee
{
  protected $id;
  protected $name;
  protected $login;
  protected $phone;
  protected $mail;
  protected $position;
  protected $position_id;
  protected $rank;
  protected $permissions = [];

    /**
     * @return Employee[]
     */
  public static function getAll() {
      $psth = dbConnPDO()->prepare("SELECT e.id, e.name, e.phone, e.skype, e.mail email, e.position position_id, e.login, e.telegram_id, p.position position_name,  p.permissions
            FROM employees e 
            LEFT JOIN emplo_positions p on p.id = e.position
            WHERE e.display = 1 and p.`show` = 1 ORDER BY  2 
      ");
      $psth->execute();
      $employees = [];
      foreach ($psth->fetchAll() as $e) {
          $emplo = new self();
          $emplo->id = $e['id'];
          $emplo->name = $e['name'];
          $emplo->login = $e['login'];
          $emplo->phone = $e['phone'];
          $emplo->mail = $e['mail'];
          $emplo->position = $e['position_name'];
          $emplo->position_id = $e['position_id'];
          $emplo->permissions = json_decode($e['permissions'], true);
          $employees[] = $emplo;
      }
      return $employees;
  }
  public function getAsArray() {
      return [
          'id' => $this->id,
          'name' => $this->name,
          'login' => $this->login,
          'phone' => $this->phone,
          'mail' => $this->mail,
          'position' => $this->position,
      ];
  }

    /**
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     * @return Employee
     */
    public function setPermissions(array $permissions): Employee
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPositionId()
    {
        return $this->position_id;
    }

    /**
     * @param mixed $position_id
     * @return Employee
     */
    public function setPositionId($position_id)
    {
        $this->position_id = $position_id;
        return $this;
    }

  function fillById($employee_id)
  {
      $data = dbConn()->query("SELECT 
                e.id, 
                e.login,
                e.mail,
                e.`name`,
                e.phone,
                p.position,
                p.id position_id,
                p.rank,
                p.permissions
                FROM employees e 
                JOIN emplo_positions p on p.id = e.position
                WHERE e.id = $employee_id
      ")->fetch_assoc();
      if(!$data['id']) {
          throw new \Exception("User not found. ID={$employee_id}");
      }
      foreach ($data as $key=>$val) {
          $this->{$key} = $val;
          if($key === 'permissions') {
              $this->{$key} = json_decode($val, true);
          }
      }

      return $this;
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
     * @return Employee
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return Employee
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     * @return Employee
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     * @return Employee
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param mixed $mail
     * @return Employee
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     * @return Employee
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @param mixed $rank
     * @return Employee
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
        return $this;
    }



}