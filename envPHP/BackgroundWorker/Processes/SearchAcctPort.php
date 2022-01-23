<?php


namespace envPHP\BackgroundWorker\Processes;


use Curl\MultiCurl;
use envPHP\BackgroundWorker\AbstractProcess;
use envPHP\service\Exceptions\NotFoundException;
use envPHP\MultiSwitcher\MultiSwitcher;
use envPHP\MultiSwitcher\SearchPort;

class SearchAcctPort extends AbstractProcess
{
    protected $curl;

    public function run()
    {
        $data = SearchPort::byMac($this->args['mac'], $this->args['vlan_id'])->search();
        dbConnPDO()->prepare("UPDATE radius_acct SET switch = ?, port = ? WHERE id = ?")
        ->execute([$data['switch'], $data['port'], $this->args['id']]);
    }

    protected function prepareArgs($args)
    {
        if(!isset($args['id'])) throw new \Exception("Id is required");
        if(!isset($args['vlan_id']))  throw new \Exception("VlanId is required");
        if(!isset($args['mac']))  throw new \Exception("MAC is required");
        return $args;
    }

}