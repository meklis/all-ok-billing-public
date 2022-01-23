<?php


namespace envPHP\ClientPersonalArea;


use envPHP\ClientPersonalArea\Exceptions\AgreementsNotFound;
use envPHP\ClientPersonalArea\Exceptions\UuidExpired;
use envPHP\service\shedule;
use Ramsey\Uuid\Uuid;

class PasswordReminder extends  AbstractClientPersonalArea
{
    protected $phone;
    protected $uuid;
    function __construct()
    {
        parent::__construct();
    }
    function formatPhone($phone) {
        $phone = str_replace(['+', ' ', '-', '(',')', '.'], '',trim($phone));
        return '+' . $phone;
    }
    function getCode($uuid) {
        $stm = $this->getConnection()->prepare("SELECT code FROM client_password_reminder WHERE uuid =  :uid and expired_at > NOW() ");
        $stm->execute([
            ':uid' => $uuid,
        ]);
        $data = $stm->fetch();
        if(!$data['code']) {
            throw new UuidExpired("Incorrect code");
        }
        return $data['code'];
    }
    function setCodeConfirmed($uuid) {
        $this->getConnection()->prepare("UPDATE client_password_reminder SET code_confirmed = 1 WHERE uuid = :id")->execute([':id' => $uuid]);
        return $this;
    }
    function findAgreements($uuid) {
        $stm = $this->getConnection()->prepare("SELECT DISTINCT  c.id, c.agreement FROM clients c JOIN client_contacts cc on c.id = cc.agreement_id and type='PHONE' WHERE cc.value in (SELECT phone FROM client_password_reminder WHERE uuid =  :uid and expired_at > NOW() ) ");
        $stm->execute([
            ':uid' => $uuid,
        ]);
        return $stm->fetchAll();
    }
    function isPhoneExist($phone) {
        $stm = $this->getConnection()->prepare("SELECT DISTINCT c.id FROM clients c JOIN client_contacts cc on c.id = cc.agreement_id and type='PHONE' WHERE cc.value = :phone ");
        $stm->execute([
            ':phone' => $phone,
        ]);
        if($stm->rowCount() != 0) {
            return true;
        } else {
            return false;
        }
    }
    function generateUid() {
        $uuid = Uuid::uuid4();
        return $uuid->toString();
    }
    function generateCode() {
        $code = '';
        for($i=0;$i<4;$i++) {
            $code .= rand(0,9);
        }
        return  $code;
    }
    protected function creteReminder($phone, $uuid, $code) {
        $stm = $this->
            getConnection()->
            prepare("INSERT INTO client_password_reminder (code, uuid, phone, created_at, expired_at) 
                        VALUES (:code, :uid, :phone, NOW(), NOW() + INTERVAL 15 MINUTE )");
        $stm->execute([
            ':phone' => $phone,
            ':uid' => $uuid,
            ':code' => $code,
        ]);
    }
    function sendConfirmation($phone, $uuid, $code) {
        if(!$this->isPhoneExist($phone)) {
            throw new AgreementsNotFound("Phone  {$phone} not registered in system");
        }
        $this->creteReminder($phone, $uuid, $code);
        shedule::add(26, 'notification/sendSMS', [
           'phone' => $phone,
           'message' => sprintf($this->config['recover_password']['text'], $code),
        ]);
        return $this;
    }
    function isCodeConfirmed($uuid) {
        $stm = $this->
        getConnection()->
        prepare("SELECT id FROM client_password_reminder WHERE uuid = :uuid and code_confirmed != 0");
        $stm->execute([
            ':uuid' => $uuid,
        ]);
        if($stm->rowCount() === 0) {
            return false;
        } else {
            return true;
        }
    }



}