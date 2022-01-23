<?php


namespace envPHP\NetworkCore\SearchIp;

use Meklis\BillingNetworkHelpers\Interfaces\StoreInterface;

class DbStore implements StoreInterface
{
    /**
     * Возвращает лист IP адресов устройств,
     * на которых нужно произвести поиск по MAC-адресам
     *
     * @param $user_ip
     * @return mixed
     * @throws \Exception
     */
    function getSwitchesListByIp($user_ip)
    {
        $listQ = dbConn()->query("SELECT eq.ip  
FROM eq_neth n 
JOIN eq_vlan_neth vn on vn.neth = n.id
JOIN eq_vlans vl on vl.id = vn.vlan 
JOIN eq_vlan_equipment ve on ve.vlan = vl.id 
JOIN equipment eq on eq.id = ve.equipment
WHERE INET_ATON('{$user_ip}') BETWEEN INET_ATON(n.startIp) and INET_ATON(n.stopIp)
and eq.group in (". join(",", getGlobalConfigVar('BILLING')['devices']['access_levels']) .")");
        if($listQ->num_rows == 0) {
            throw new \Exception("Not found access-level devices for ip $user_ip");
        }
        $ips = [];

        while($ip = $listQ->fetch_assoc()) {
            $ips[] = $ip['ip'];
        }
        return $ips;
    }

    /**
     * Возвращает асоциативный массив с ключами ip, login, password, community, telnetPort, apiPort
     * @param $device_ip
     * @return mixed
     * @throws \Exception
     * @example request getSwitchesListByIp('10.1.1.11');
     * @example response ['ip'=> '10.1.1.11', 'community' => 'public', 'login'=>'login', 'password'=>'password', 'port_telnet'=>23, 'port_api'=>55055]
     * Запрашиваются IP возвращаемые методами getSwitchesListByIp(), getRouterListByIp().
     */
    function getDeviceAccess($device_ip)
    {
        $data = dbConn()->query("SELECT ip, login, `password`, community, 21 port_telnet,  55055 port_api FROM equipment e JOIN equipment_access a on a.id = e.access WHERE ip = '{$device_ip}'")->fetch_assoc();
        if(!$data['ip']) {
            throw new \Exception("Not found accesses for ip {$device_ip}");
        }
        //print_r($data);
        return $data;
    }

    /**
     * Возвращает лист IP адресов роутеров, на которых нужно искать ARP
     *
     * @param $user_ip
     * @throws \Exception
     * @return string[]
     */
    function getRouterListByIp($user_ip)
    {

        $listQ = dbConn()->query("SELECT eq.ip  
FROM eq_neth n 
JOIN eq_vlan_neth vn on vn.neth = n.id
JOIN eq_vlans vl on vl.id = vn.vlan 
JOIN eq_vlan_equipment ve on ve.vlan = vl.id 
JOIN equipment eq on eq.id = ve.equipment
WHERE INET_ATON('{$user_ip}') BETWEEN INET_ATON(n.startIp) and INET_ATON(n.stopIp)
and eq.group in (". join(getGlobalConfigVar('BILLING')['devices']['core_levels']) .")");
        if($listQ->num_rows == 0) {
            throw new \Exception("Not found core-level devices for ip $user_ip");
        }
        $ips = [];
        while($d = $listQ->fetch_assoc()) {
            $ips[] = $d['ip'];
        }
        return $ips;
    }

}