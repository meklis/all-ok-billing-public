<?php


namespace envPHP\service;


use envPHP\EventSystem\EventRepository;
use Ramsey\Uuid\Uuid;

class User
{
    protected $sql;
    public function __construct()
    {
        $this->sql = dbConnPDO();
    }

    public function generateToken($userId) {
        if(!$userId) {
            throw new \InvalidArgumentException("Incorrect user ID");
        }
       $uuid = Uuid::uuid4();
       $this->sql->prepare("
        INSERT INTO emplo_tokens (token, employee, created_at, expired_at) 
        VALUES (?, ?, NOW(), NOW() + INTERVAL 14 DAY)
       ")->execute([$uuid->toString(), $userId]);
       return $uuid->toString();
    }
    public function auth($login, $password) {
        $psth = $this->sql->prepare("
            SELECT * FROM employees WHERE login = ? and BINARY password = ?
        ");
        $psth->execute([$login, $password]);
        if($psth->rowCount() > 0) {
            $id = $psth->fetch()['id'];
            \envPHP\EventSystem\EventRepository::getSelf()->notify('employee:logged', [
                'id' => $id
            ]);
            return $id;
        }
        return false;
    }

    /**
     * @param $tokenUUID
     * @return int -2 - user with token not found, -1 - token expired, int > 0 - employee ID
     */
    public function validateToken($tokenUUID) {
        $psth = $this->sql->prepare("
                SELECT employee, if(expired_at > NOW(), 1 , 0) expired  
                FROM emplo_tokens WHERE token = ?");
        $psth->execute([$tokenUUID]);
        if($psth->rowCount() <= 0) {
            return -2;
        }
        $info = $psth->fetch();
        if($info['expired']) {

            return $info['employee'];
        } else {
            return -1;
        }
    }
    public function getUserByLogin($login) {
        $psth = $this->sql->prepare("SELECT id FROM employees WHERE login = ?");
        $psth->execute([$login]);
        return $this->getUser($psth->fetch()['id']);
    }
    public function getUser($userId) {
        $psth = $this->sql->prepare("SELECT e.id, e.name, e.phone, e.skype, e.mail email, e.login, if(IFNULL(e.display, 0) = 0, false, true) display, e.telegram_id, p.position, p.id group_id, p.permissions
                FROM employees e 
                JOIN emplo_positions p on p.id = e.position
                WHERE e.id = ?
                ;");
        $psth->execute([$userId]);
        $perms = $psth->fetch();
        $perms['permissions'] = json_decode($perms['permissions']);
        return $perms;
    }
    public function setUserStatus($userId, $status) {
        $currentStatus = $this->getUserStatus($userId);
        if($status === $currentStatus['status']) {
            return $this;
        }
        if($status !== 'OFFLINE' && $currentStatus['status'] === 'OFFLINE') {
            $this->sql->prepare("INSERT INTO employee_work_statuses (employee_id, `start`, `status`) VALUES (?, NOW(), ?)")
                ->execute([$userId, $status]);
        } elseif ($status === 'OFFLINE' && $currentStatus['status'] !== 'OFFLINE') {
            $this->sql->prepare("UPDATE employee_work_statuses SET stop = NOW() WHERE id = ?")
                ->execute([$currentStatus['last_status_id']]);
        } elseif ($status !== 'OFFLINE' && $currentStatus['status'] !== 'OFFLINE') {
            $this->sql->prepare("UPDATE employee_work_statuses SET stop = NOW() WHERE id = ?")
                ->execute([$currentStatus['last_status_id']]);
            $this->sql->prepare("INSERT INTO employee_work_statuses (employee_id, `start`, `status`) VALUES (?, NOW(), ?)")
                ->execute([$userId, $status]);
        }
        EventRepository::getSelf()->notify('employee:status_updated', [
            'id' => $userId,
            'old_status' => $currentStatus['status'],
            'new_status' => $status,
        ]);
        return $this;
    }
    public function getUserStatus($userId) {
        $sth = $this->sql->prepare("SELECT id, `status`, from_time, last_status_id FROM v_employee_statuses WHERE id = ?");
        $sth->execute([$userId]);
        if($sth->rowCount() !== 0) {
            return $sth->fetchAll()[0];
        }
        throw new \Exception("user with $userId not found");
    }
}