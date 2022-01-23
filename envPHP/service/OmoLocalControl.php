<?php


namespace envPHP\service;


class OmoLocalControl
{
    protected $sql;

    function __construct() {
        $this->sql = dbConn();
    }
    public function startTransaction() {
        $this->sql->query("START TRANSACTION");
        return $this;
    }
    public function commitTransaction() {
        $this->sql->query("COMMIT");
        return $this;
    }
    public function rollbackTransaction() {
        $this->sql->query("ROLLBACK");
        return $this;
    }
    //Работа с закреплениями
    public function bindAddDevice($userId, $deviceId) {
        if($id = $this->sql->query("SELECT id FROM omo_device_bindings WHERE user_id = '$userId' and device_id = '$deviceId' ")->fetch_assoc()['id']) {
            return $id;
        }
        if($this->sql->query("INSERT INTO omo_device_bindings (created_at, device_id, user_id) VALUES  (NOW(), '$deviceId', '$userId')")) {
            return $this->sql->insert_id;
        }
        throw new \Exception("SQL ERR: " . $this->sql->error );
    }
    public function bindDeleteDevice($userId, $deviceId) {
        if(!$this->sql->query("DELETE FROM omo_device_bindings WHERE device_id = '{$deviceId}' and user_id = '{$userId}'")) {
            throw new \Exception("SQL ERR: " . $this->sql->error );
        }
        return $this;
    }
    public function bindAddAgreement($userId, $agreementId) {
        if($id = $this->sql->query("SELECT id FROM omo_agreement_bindings WHERE user_id = '$userId' and agreement_id = '$agreementId'")->fetch_assoc()['id']) {
            return $id;
        }
        if(!$this->sql->query("INSERT INTO omo_agreement_bindings (agreement_id, user_id) VALUES ('$agreementId', '$userId')")) {
            throw new \Exception("SQL ERR: " . $this->sql->error);
        }
        return $this->sql->insert_id;
    }

    public function bindDeleteAgreement($userId, $agreementId) {
        if(!$this->sql->query("DELETE FROM omo_agreement_bindings WHERE agreement_id = '{$agreementId}' and user_id = '{$userId}'")) {
            throw new \Exception("SQL ERR: " . $this->sql->error);
        }
        return $this;
    }

    //Работа с пользователями
    public function userSetUid($userPhone, $userUid) {
        if(!$this->sql->query("UPDATE omo_users SET uid = '{$userUid}' WHERE phone = '{$userPhone}'")) {
            throw new \Exception("SQL ERR: " . $this->sql->error);
        }
        return $this;
    }
    public function userSetPhone($userUid, $userPhone) {
        if(!$this->sql->query("UPDATE omo_users SET phone = '{$userPhone}' WHERE uid = '{$userUid}'")) {
            throw new \Exception("SQL ERR: " . $this->sql->error);
        }
        return $this;
    }
    public function userAdd($phone, $uid = "") {
            $WHERE = "";
            if($uid) {
                $WHERE .= " or uid = '{$uid}'";
            }
            if($id = $this->sql->query("SELECT id FROM omo_users WHERE phone = '$phone' $WHERE")->fetch_assoc()['id']) {
                return $id;
            }
            if( $this->sql->query("INSERT INTO omo_users (created_at, phone, uid) VALUES (NOW(), '$phone', '$uid')")) {
                return $this->sql->insert_id;
            }
       throw new \Exception("SQL ERR: ".  $this->sql->error);
    }
    public function userGetByUid($userId) {
        $res = dbConn()->query("SELECT id, phone, uid FROM omo_users WHERE uid = '$userId'")->fetch_assoc();
        if(!$res['id']) {
            throw new \Exception("Not found user or uid not setted in local storage by id '{$userId}'");
        }
        return $res;
    }
    public function userInfo($userId) {
        $res = dbConn()->query("SELECT id, created_at, phone, uid FROM omo_users WHERE id = '$userId'")->fetch_assoc();
        if(!$res['id']) {
            throw new \Exception("Not found user or uid not setted in local storage by id '{$userId}'");
        }
        return $res;
    }
    public function userGetByPhone($phone) {
        $res = dbConn()->query("SELECT id, phone, uid FROM omo_users WHERE phone = '$phone'")->fetch_assoc();
        if(!$res['id']) {
            throw new \Exception("Not found user or uid not setted in local storage by phone '{$phone}'");
        }
        return $res;
    }
    public function userFindByDevice($deviceId) {
        $response = [];
        $resp = $this->sql->query("SELECT user_id id FROM omo_device_bindings WHERE device_id = '$deviceId'");
        while ($d = $resp->fetch_assoc()) {
            $response[] = $d['id'];
        }
        return $response;
    }

    //Работа с устройствами
    public function deviceAdd($hubId, $deviceId, $userId, $type) {
        if($id = $this->sql->query("SELECT id FROM omo_devices WHERE device_uid = '{$deviceId}'")->fetch_assoc()['id']) {
            return $id;
        }
        if(!$this->sql->query("INSERT INTO omo_devices (created_at, hub_uid, device_uid, user_uid, `type`, `comment`)
VALUES (NOW(), '{$hubId}', '{$deviceId}', '{$userId}', '{$type}', '')")) {
            throw new \Exception("SQL ERR: ". $this->sql->error );
        };
        return $this->sql->insert_id;
    }
    //Работа с устройствами
    public function deviceGetByUid( $deviceId) {
        if($id = $this->sql->query("SELECT id FROM omo_devices WHERE device_uid = '{$deviceId}'")->fetch_assoc()['id']) {
            return $id;
        }
        throw new \Exception("Not found device by id: $deviceId");
    }
    public function deviceUpdate($id, $house, $entrance, $floor, $apartment, $comment) {
        if($this->sql->query("SELECT id FROM omo_devices WHERE id = '$id'")->num_rows == 0) {
            throw new \Exception("Device with ID $id not found");
        };
        if(!$this->sql->query("UPDATE omo_devices 
           SET 
               house = '{$house}', 
               entrance = '{$entrance}',
               floor = {$floor},
               apartment = {$apartment},
               comment = {$comment},
               status = 'BINDED'
            WHERE id = '$id'
        ")) {
            throw new \Exception("SQL ERR: ". $this->sql->error );
        };
        return $this;
    }
    public function deviceDelete($id) {
        if(!$this->sql->query("UPDATE omo_devices SET status='DELETED' WHERE id = '$id'")) {
            throw new \Exception("SQL ERR: ". $this->sql->error );
        }
        return $this;
    }
    public function deviceInfo($deviceId) {
        $res = dbConn()->query("SELECT d.id, 
            d.created_at, 
            d.hub_uid, 
            d.device_uid, 
            d.type, 
            d.`status`, 
            if(a.id is null, '', a.full_addr) addr,
            d.entrance,
            d.floor,
            d.apartment,
            d.`comment`,
            d.delete_reason
            FROM `omo_devices` d  
            LEFT JOIN addr a on a.id = d.house
            WHERE d.id = '$deviceId'
            ORDER BY created_at desc ")->fetch_assoc();
        if(!$res['device_uid']) {
            throw new \Exception("Not found or not binded device by id '{$deviceId}'");
        }
        $res['description'] = "{$res['addr']}, под. {$res['entrance']}";
        return $res;
    }
    public function deviceFindByHouseEntrance($houseId, $entrance) {
        $res = dbConn()->query("SELECT d.id, 
            d.created_at, 
            d.hub_uid, 
            d.device_uid, 
            d.type, 
            d.`status`, 
            if(a.id is null, '', a.full_addr) addr,
            d.entrance,
            d.floor,
            d.apartment,
            d.`comment`,
            d.delete_reason
            FROM `omo_devices` d  
            LEFT JOIN addr a on a.id = d.house
            WHERE a.id = '$houseId' and d.entrance = '$entrance'
            ORDER BY created_at desc ")->fetch_all(MYSQLI_ASSOC);
        return $res;
    }
    public function deviceGetList() {
        return $this->sql->query("
            SELECT d.id, 
            d.created_at, 
            d.hub_uid, 
            d.device_uid, 
            d.type, 
            d.`status`, 
            if(a.id is null, '', a.full_addr) addr,
            d.entrance,
            d.floor,
            d.apartment,
            d.`comment`,
            d.delete_reason
            FROM `omo_devices` d  
            LEFT JOIN addr a on a.id = d.house  
            ORDER BY created_at desc 
            ")->fetch_all(MYSQLI_ASSOC);
    }

    public function deviceGetDescription($deviceId) {
        $res = $this->sql->query("SELECT d.device_uid, d.hub_uid, d.entrance, a.full_addr, d.type
                            FROM omo_devices d 
                            JOIN addr a on a.id = d.house 
                            WHERE d.id = '$deviceId' and d.`status` = 'BINDED'")->fetch_assoc();
        if(!$res['device_uid']) {
            throw new \Exception("Not found or not binded device by id '{$deviceId}'");
        }
        $res['description'] = "{$res['full_addr']}, под. {$res['entrance']}";
        return $res;
    }

    public function userGetFiltered($filter = []) {
        $WHERE = "";
        if(isset($filter['agreement_id']) && $filter['agreement_id']) {
            $WHERE .= " and a.agreement_id = '{$filter['agreement_id']}' ";
        }
        if(isset($filter['device_id']) && $filter['device_id']) {
            $WHERE .= " and d.device_id = '{$filter['device_id']}' ";
        }
        $data = $this->sql->query("SELECT DISTINCT u.id, u.created_at, u.uid, u.phone 
            FROM omo_users u 
            LEFT JOIN (SELECT user_id, agreement_id FROM omo_agreement_bindings) a on a.user_id = u.id 
            LEFT JOIN (SELECT user_id, device_id FROM omo_device_bindings) d on d.user_id = u.id
            WHERE u.id != 0 
            $WHERE
            order by  u.created_at desc 
            ")->fetch_all(MYSQLI_ASSOC);
        return $data;
    }
}