<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 10.05.2019
 * Time: 16:42
 */

namespace envPHP\structs;



class LimitDeactivate
{
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var \DateTime
     */
    protected $created;
    /**
     * @var integer
     */
    protected $days;
    /**
     * @var Employee
     */
    protected $employee;

    function fillByClient(Client $client) {
        $data = dbConn()->query("SELECT id, created, created_employee, client, days FROM `client_disable_days` WHERE client =  {$client->getId()} ORDER BY id desc LIMIT 1;")->fetch_assoc();
        if($data['id']) {
           $this->employee = (new Employee())->fillById($data['created_employee']);
           $this->client = $client;
           $this->days = $data['days'];
           $this->created = $data['created'];
        } else {
            $days = dbConn()->query("SELECT b.days_to_disable, NOW() data
                FROM clients c 
                JOIN client_prices p on p.agreement = c.id 
                JOIN bill_prices b on b.id = p.price 
                WHERE p.time_stop is null and c.id = {$client->getId()} and b.days_to_disable is not null
                ORDER BY p.id desc 
                LIMIT 1");
            if($days->num_rows == 0) {
                throw new \Exception("Client with id  {$client->getId()} doesn't have custom limit and active prices");
            }
            $this->days = $days->fetch_assoc()['days_to_disable'];
            $this->employee = (new Employee())->fillById(getGlobalConfigVar('BASE')['system_user_id']);
            $this->client = $client;
            $this->created = date("Y-m-d H:i:s");
        }
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return LimitDeactivate
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     * @return LimitDeactivate
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return int
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param int $days
     * @return LimitDeactivate
     */
    public function setDays($days)
    {
        $this->days = $days;
        return $this;
    }

    /**
     * @return Employee
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * @param Employee $employee
     * @return LimitDeactivate
     */
    public function setEmployee($employee)
    {
        $this->employee = $employee;
        return $this;
    }

}