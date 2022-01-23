<?php


namespace envPHP\ClientPersonalArea;


use envPHP\service\Helpers;
use envPHP\structs\Client;
use Ramsey\Uuid\Uuid;
use SwitcherCore\Modules\Helper;

class Auth extends AbstractClientPersonalArea
{
    protected $sql;
    function __construct()
    {
        $this->sql = $this->getConnection();
    }
    public function auth($login, $password) {
        $sth = $this->sql->prepare("SELECT id FROM clients WHERE agreement = :login and BINARY `password` = :password ");
        $sth->execute([
            ':login' => $login,
            ':password' => $password,
        ]);
        if($sth->rowCount() == 0) {
            return -1;
        }
        $data = $sth->fetch();
        if(!$data['id']) {
            throw new \Exception("Unknown user id");
        }
        return $data['id'];
    }
    public function generateAuthKey($client_id) {
        $uuid = Uuid::uuid4();
        $token = $uuid->toString();
        $sth = $this->sql->prepare("INSERT INTO client_tokens (token, client_id, expired_at) VALUES (:token, :client_id, NOW() + INTERVAL 1 DAY)");
        $sth->execute([
            ':token' => $token,
            ':client_id' => $client_id,
        ]);
        return $token;
    }
    public function validateAuthKey($auth_key) {
        $sth = $this->sql->prepare("SELECT client_id FROM client_tokens WHERE token = :token");
        $sth->execute([
            ':token' => $auth_key,
        ]);
        if($sth->rowCount() == 0) {
            return -1;
        }
        $data = $sth->fetch();
        if(!$data['client_id']) {
            throw new \Exception("Unknown user id");
        }
        return $data['client_id'];
    }
    public function changePasswd($client_id, $new_pwd) {
        $sth = $this->sql->prepare("UPDATE clients SET password = :pwd WHERE id = :client_id");
        $sth->execute([
            ':pwd' => $new_pwd,
            ':client_id' => $client_id,
        ]);
        return true;
    }

    /**
     * @param $phone
     * @return Client[]
     * @throws \Exception
     */
    public function getClientsByPhone($phone) {
        $clients = [];
        $phone = Helpers::beautyPhoneNumber($phone);
        $psth = $this->sql->prepare("SELECT distinct agreement_id FROM client_contacts WHERE type = 'PHONE' and value = ?");
        $psth->execute([$phone]);
        foreach ($psth->fetchAll() as $agree) {
            $clients[] = (new Client())->fillById($agree['agreement_id']);
        }
        return $clients;
    }
    public function getAuthByPhoneCode($phone) {
        $code = rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
        $phone = Helpers::beautyPhoneNumber($phone);

        //Check clients exists
        if(!count($this->getClientsByPhone($phone))) {
            throw new \Exception("Users not found in system by phone");
        }

        $this->sql->prepare("
            INSERT INTO client_auth_by_phone 
                (phone, code, created_at, expired_at) 
            VALUES  (?, ?, NOW(), NOW() + INTERVAL 5 MINUTE )
        ")->execute([$phone, $code]);
        return $code;
    }
    public function isAuthByPhoneCodeValid($phone, int $code) {
        $phone = Helpers::beautyPhoneNumber($phone);
        $psth = $this->sql->prepare("SELECT id, code 
                FROM client_auth_by_phone 
                WHERE expired_at > NOW() 
                    and phone = ? order by 1 desc limit 1");
        $psth->execute([$phone]);
        return   $psth->fetch()['code'] == $code;
    }
}