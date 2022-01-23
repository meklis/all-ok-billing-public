<?php


namespace envPHP\service;

use \OmoSystemsApi\Api\Device;
use \OmoSystemsApi\ApiCaller;
use  \OmoSystemsApi\Auth;

class OmoControl
{
    private static $config = [];
    private static $configSetted = false;
    public static function setConfig($config) {
        self::$config = [
            'enabled' => true,
            'access_addr' => '',
            'api_addr' => '',
            'secret' => '',
            'username' => '',
            'password' => '',
            'user_can_share_device' => false,
            'user_as_owner' => false,
            'set_device_description' => false,
        ];
        foreach (self::$config as $key=>$_) {
            if(isset($config[$key])) {
                self::$config[$key] = $config[$key];
            }
        }
        self::$configSetted = true;
    }

    /**
     * @var \OmoSystemsApi\Api\Device
     */
    protected $omoDevApi;
    /**
     * @var OmoLocalControl
     */
    protected $local;
    function __construct() {
        if(!self::$config) {
            throw new \Exception("Configuration not setted. Use static method ::setConfig()");
        }
        if(!self::$config['enabled']) {
            throw new \Exception("OMO systems disabled in configuration");
        }
        $this->local = new OmoLocalControl();
    }

    protected function getDeviceOmoApi() {
        if($this->omoDevApi) {
            return $this->omoDevApi;
        }
        $device = new  Device(
            new  ApiCaller($this->getAuthOmo()->getToken())
        );
        $this->omoDevApi = $device;
        return $device;
    }
    protected function getAuthOmo() {
        Auth::$authServer = self::$config['access_addr'];
        return new  Auth(self::$config['secret'], self::$config['username'], self::$config['password']);
    }
    public function addPhone($phone, $agreementId) {
        $this->local->startTransaction();
        try {
            $userId = $this->local->userAdd($phone);
        } catch (\Exception $e) {
            throw new \Exception("Ошибка внесения нового пользователя, обратитесь к администратору!");
        }
        try {
            $this->local->bindAddAgreement($userId, $agreementId);
        } catch (\Exception $e) {
            throw new \Exception("Ошибка закрепления пользователя за договором!");
        }
        $this->local->commitTransaction();
        return $userId;
    }
    public function deletePhone($phone, $agreementId) {
        $this->local->startTransaction();
        $user = $this->local->userGetByPhone($phone);
        $this->local->bindDeleteAgreement($user['id'], $agreementId);
        $this->local->commitTransaction();
        return $this;
    }
    public function shareDevice($userId, $deviceId) {
        $this->local->startTransaction();
        $this->local->bindAddDevice($userId, $deviceId);
        $device = $this->local->deviceInfo($deviceId);
        $user = $this->local->userInfo($userId);
        $deviceLabel = "";
        if(self::$config['set_device_description']) {
            $deviceLabel = $this->local->deviceGetDescription($deviceId);
        }
        $resp = "OK";
     //   $resp = $this->getDeviceOmoApi()->share($device['device_uid'], $user['phone'], $deviceLabel, self::$config['user_can_share_device'], self::$config['user_as_owner']);
        $this->local->commitTransaction();
        return $resp;
    }
    public function revokeDevice($userId, $deviceId) {
        $this->local->startTransaction();
        $this->local->bindDeleteDevice($userId, $deviceId);
        $device = $this->local->deviceInfo($deviceId);
        $user = $this->local->userInfo($userId);
        $resp = "OK";
        //   $resp = $this->getDeviceOmoApi()->revoke($device['device_uid'], $user['uid'], $user['phone']);
        $this->local->commitTransaction();
        return $resp;
    }
    public function getLocalOmo() {
        return $this->local;
    }
}