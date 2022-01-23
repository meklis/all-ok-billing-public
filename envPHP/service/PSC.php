<?php


namespace envPHP\service;


class PSC
{
    /**
     * @var PSC
     */
    protected static $slf;
    protected $permissionControl;

    protected function __construct($groupId)
    {
        $this->permissionControl = new PermissionControl($groupId);
    }

    public static function init($groupId)
    {
        self::$slf = new self($groupId);
    }

    public static function isPermitted($permission)
    {
        if (!self::$slf) {
            throw new \Exception("PermissionControl not initialized. You must call ::init first");
        }
        return self::$slf->permissionControl->isPermitted($permission);
    }

    public static function isGrpPermitted($permission)
    {
        if (!self::$slf) {
            throw new \Exception("PermissionControl not initialized. You must call ::init first");
        }
        return self::$slf->permissionControl->isGroupPermitted($permission);
    }
    public static function getAllowedHouseGroups() {
        if (!self::$slf) {
            throw new \Exception("PermissionControl not initialized. You must call ::init first");
        }
        return self::$slf->permissionControl->getAllowedHouseGroups();
    }

}