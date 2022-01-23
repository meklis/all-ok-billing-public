<?php



namespace envPHP\Events;


use envPHP\BackgroundWorker\BackgroundProcess;
use envPHP\BackgroundWorker\Processes\AcctLeaseInfo;
use envPHP\BackgroundWorker\Processes\SearchAcctPort;
use envPHP\classes\Logger;
use envPHP\EventSystem\Event;
use SplSubject;

class RadiusAcct extends Event
{

    protected function bgProcesses($data) {

        //Check last update time
        $psth = dbConnPDO()->prepare("SELECT id, updated_at
                    FROM radius_acct 
                    WHERE mac = ? 
                        and ip = ? 
                        and vlan_id = ?
                        and (updated_at is null or updated_at < NOW() - INTERVAL 1 HOUR )");
        $psth->execute([
            $data['device_mac'],
            $data['ip_address'],
            $this->getVlanId($data['dhcp_server_name'])
        ]);
        if($psth->rowCount() !== 0) {
            $db = $psth->fetchAll()[0];
            dbConnPDO()->prepare("UPDATE radius_acct SET updated_at = NOW() WHERE id = ?")->execute([$db['id']]);
        }

    }
    public function update(SplSubject $subject, $event = '*', $data = null)
    {
        if($data['status_type'] === 'Start') {
            dbConnPDO()->prepare("
                INSERT INTO radius_acct ( mac, ip, dhcp_server, vlan_id, start ) 
                VALUES (?, ?, ?, ?, NOW())  ON DUPLICATE KEY UPDATE start = NOW(), stop = null 
            ")->execute([$data['device_mac'], $data['ip_address'], $data['dhcp_server_name'], $this->getVlanId($data['dhcp_server_name'])]);

            $this->bgProcesses($data);
        } else {
            dbConnPDO()->prepare("UPDATE radius_acct SET stop = NOW() WHERE mac = ? and ip = ? and dhcp_server = ?"
             )->execute([$data['device_mac'], $data['ip_address'], $data['dhcp_server_name']]);
        }
    }
    public function getEventType() {
        return "radius:acct";
    }
    protected function getVlanId($serverName) {
        if(preg_match('/^.*?([0-9]{1,4})$/', $serverName, $matches)) {
            return $matches[1];
        }
        return 0;
    }

}