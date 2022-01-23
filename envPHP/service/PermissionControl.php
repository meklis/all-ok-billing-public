<?php


namespace envPHP\service;



class PermissionControl
{
    protected $sql;
    protected $groupId = 0;
    protected $groupPermissions = [];
    public function __construct($groupId)
    {
        $this->sql = dbConnPDO();
        $this->groupId = $groupId;
        $sth = $this->sql->prepare("SELECT id, permissions FROM emplo_positions WHERE id = ?");
        $sth->execute([$groupId]);
        $perms = $sth->fetch();
        if($perms['permissions']) {
            $this->groupPermissions  = json_decode($perms['permissions'], true);
        }
    }
    public function isPermitted($permission) {
        return in_array($permission, $this->groupPermissions);
    }
    public function setGroupPermissions(array $permissions) {
        $this->groupPermissions = $permissions;
        return $this;
    }
    public function getGroupPermissions() {
        return $this->groupPermissions;
    }
    public function getPermissionsTemplate($name_as_key = true) {
        if(!getGlobalConfigVar('PERMISSIONS') || !getGlobalConfigVar('PERMISSIONS')['enabled']) {
            throw new \Exception("Permissions not configured");
        }
        $resp = [];
        foreach (getGlobalConfigVar('PERMISSIONS')['access_list'] as $rules) {
            $key = $name_as_key ? $rules['name'] : $rules['key'];
            foreach ($rules['rules'] as $rule) {
                if(!$rule['display']) continue;
                $resp[$key][] = [
                    'checked' => in_array($rule['key'], $this->groupPermissions) ? true  : false ,
                    'key' => $rule['key'],
                    'name' => $rule['name'],
                ];
            }
        }
        return $resp;
    }
    public function isGroupPermitted($groupKey) {
        foreach ($this->getPermissionsTemplate(false)[$groupKey] as $perm) {
            if($perm['checked']) return true;
        }
        return false;
    }

    public function save() {
        $sth = $this->sql->prepare("UPDATE emplo_positions SET permissions = ? WHERE id = ?");
        $sth->execute([json_encode($this->groupPermissions), $this->groupId]);
    }

    public function setAllowedHouseGroups($allowedHouseGroupIds) {
        $this->sql->prepare("DELETE FROM employee_positions_to_house_groups WHERE position_id = ?")->execute([$this->groupId]);
        $this->sql->beginTransaction();
        $sth = $this->sql->prepare("INSERT INTO employee_positions_to_house_groups (position_id, house_group_id, updated_at) VALUES (?,?,NOW())");
        foreach ($allowedHouseGroupIds as $houseGroupId) {
            $sth->execute([$this->groupId, $houseGroupId]);
        }
        $this->sql->commit();
        return $this->getAllowedHouseGroups();
    }

    public function getAllowedHouseGroups() {
        $allowed = [0];
        $sth = $this->sql->prepare("SELECT house_group_id FROM employee_positions_to_house_groups WHERE position_id = ?");
        $sth->execute([$this->groupId]);
        foreach ($sth->fetchAll(\PDO::FETCH_ASSOC) as $e) {
            $allowed[] = $e['house_group_id'];
        }
        return $allowed;
    }
}
