<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 10.05.2019
 * Time: 16:55
 */

namespace envPHP\structs;


use envPHP\service\Customer\ExtraContacts;

class Client
{
    protected $id;
    protected $agreement;
    protected $name;
    protected $entrance;
    protected $floor;
    protected $apartment;
    protected $house;
    protected $balance;
    protected $add_time;
    protected $descr;
    protected $password;

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @var ClientContact[]
     */
    protected $contacts;
    /**
     * @return  ClientContact[]
     */
    function getContacts() {
        if($this->contacts !== null) return $this->contacts;
        $this->contacts = ClientContact::getAllContacts($this->id);
        return $this->contacts;

    }
    function fillById(int $client_id) {
        $data = dbConn()->query("SELECT c.id, agreement, c.name, entrance, floor, apartment, house, balance, add_time, descr, cc.value phone, cc2.value email, password
        FROM clients c 
        LEFT JOIN client_contacts cc on c.id = cc.agreement_id and cc.type='PHONE' and cc.main = 1
        LEFT join client_contacts cc2 on c.id = cc2.agreement_id and cc2.type='EMAIL' and cc2.main = 1
        WHERE c.id = {$client_id}
        ;
      ")->fetch_assoc();
        if(!$data['id']) {
            throw new \Exception("Client not found. ID={$client_id}");
        }
        foreach ($data as $key=>$val) {
            $this->{$key} = $val;
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
     * @return Client
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAgreement()
    {
        return $this->agreement;
    }

    /**
     * @param mixed $agreement
     * @return Client
     */
    public function setAgreement($agreement)
    {
        $this->agreement = $agreement;
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
     * @return Client
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntrance()
    {
        return $this->entrance;
    }

    /**
     * @param mixed $entrance
     * @return Client
     */
    public function setEntrance($entrance)
    {
        $this->entrance = $entrance;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @param mixed $floor
     * @return Client
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApartment()
    {
        return $this->apartment;
    }

    /**
     * @param mixed $apartment
     * @return Client
     */
    public function setApartment($apartment)
    {
        $this->apartment = $apartment;
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
     * @return Client
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return Client
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param mixed $house
     * @return Client
     */
    public function setHouse($house)
    {
        $this->house = $house;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     * @return Client
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddTime()
    {
        return $this->add_time;
    }

    /**
     * @param mixed $add_time
     * @return Client
     */
    public function setAddTime($add_time)
    {
        $this->add_time = $add_time;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescr()
    {
        return $this->descr;
    }

    /**
     * @param mixed $descr
     * @return Client
     */
    public function setDescr($descr)
    {
        $this->descr = $descr;
        return $this;
    }
}