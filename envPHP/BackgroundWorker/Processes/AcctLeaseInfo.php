<?php


namespace envPHP\BackgroundWorker\Processes;


use Curl\MultiCurl;
use envPHP\BackgroundWorker\AbstractProcess;
use envPHP\NetworkCore\SearchIp\DbStore;
use envPHP\service\Exceptions\NotFoundException;
use envPHP\MultiSwitcher\MultiSwitcher;

class AcctLeaseInfo extends AbstractProcess
{
    protected $curl;

    public function run()
    {
        $msw = new MultiSwitcher();
        $this->log()->info("Get lease info by parameters", $this->args);
        foreach ($this->getRoutersByVlan($this->args['vlan_id']) as $router) {
            $msw->add('lease_info', $router, [
                'ip' => $this->args['ip'],
            ]);
        }
        $data = $msw->process()->getByModule('lease_info');
        $this->log()->info("Found leases - ", $data);
        foreach ($data as $router => $response) {
            if (!isset($response['data']) || !is_array($response['data']) || count($response['data']) == 0) {
                continue;
            }
            dbConnPDO()->prepare("UPDATE radius_acct SET hostname = ? WHERE id = ?")
                ->execute([$response['data'][0]['host_name'], $this->args['id']]);
        }
    }

    protected function prepareArgs($args)
    {
        if (!isset($args['ip'])) throw new \Exception("IP is required");
        if (!isset($args['vlan_id'])) throw new \Exception("vlan_id is required");
        if (!isset($args['id'])) throw new \Exception("Id is required");
        return $args;
    }

    protected function getRoutersByVlan($vlan_id)
    {
        $listQ = dbConnPDO()->query("SELECT ip 
FROM equipment eq
JOIN eq_vlan_equipment ve on ve.equipment = eq.id 
JOIN eq_vlans v on ve.vlan = v.id 
WHERE v.vlan = '$vlan_id'
and eq.`group` in(" . join(getGlobalConfigVar('BILLING')['devices']['core_levels']) . ")");
        $ips = [];
        foreach ($listQ->fetchAll() as $d) {
            $ips[] = $d['ip'];
        }
        return $ips;
    }
}