<?php


namespace Api\V2\Actions\SwitcherCore;


use Api\V2\Actions\Action;
use envPHP\classes\std;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use SwitcherCore\Modules\Helper;
use SwitcherCore\Switcher\CoreConnector;
use SwitcherCore\Switcher\Device;

abstract class SwitcherCoreAction extends Action
{
    /**
     * @var CoreConnector
     */
    protected $coreConnector;
    /**
     * @var \PDO
     */
    protected $pdo;
    function __construct(LoggerInterface $logger)
    {
        try {
            $this->coreConnector = (new \SwitcherCore\Switcher\CoreConnector(Helper::getBuildInConfig()));

        } catch (\Exception $e) {
            $this->logger->error("ERROR initialize switcher-core: {$e->getMessage()}");
            throw new \Exception($e);
        }
        $this->pdo = dbConnPDO();
        parent::__construct($logger);
    }
    protected function getDeviceAccessByIp($ip) {
        $sth = $this->pdo->prepare("SELECT 
ip, a.login, a.`password`, a.community
FROM equipment e 
JOIN equipment_models m on m.id = e.model
JOIN equipment_access a on a.id = e.access
WHERE ip = :ip");
        if(!$sth->execute([':ip' => $ip])) {
            $this->logger->error("ERROR execute query for get device access: ".$sth->queryString);
            throw new \Exception("ERROR execute query for get device access");
        }
        if($sth->rowCount() === 0) {
            throw new HttpNotFoundException($this->request, "$ip not found in store");
        }
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }
    protected function getDeviceStoreInfo($ip) {
        $sth = $this->pdo->prepare("SELECT 
e.ip, e.mac, e.ping, e.description, e.entrance, e.firmware, e.floor, e.hardware, e.sn, e.uplink_port, m.`name` model, ad.full_addr addr 
FROM equipment e 
JOIN equipment_models m on m.id = e.model
JOIN equipment_access a on a.id = e.access
JOIN addr ad on ad.id = e.house
WHERE ip = :ip");
        if(!$sth->execute([':ip' => $ip])) {
            $this->logger->error("ERROR execute query for get device info: ".$sth->queryString);
            throw new \Exception("ERROR execute query for get device info");
        }
        if($sth->rowCount() === 0) {
            throw new HttpNotFoundException($this->request, "$ip not found in store");
        }
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }
    protected function initCoreByRequestParam() {
        $dev = null;
        $req = $this->request->getQueryParams();

        $dev_ip = $this->getDevIpFromHeader();
        if($dev_ip) {
            $dev = $this->getDeviceAccessByIp($dev_ip);
        } elseif (isset($req['ip'])) {
            $dev = $this->getDeviceAccessByIp($req['ip']);
        } else {
            throw new HttpBadRequestException($this->request, "Parameter 'ip' is required ");
        }

        $config = getGlobalConfigVar('SWITCHER_CORE');
        return $this->coreConnector->init(
            Device::init($dev['ip'], $dev['community'], $dev['login'], $dev['password'])
                ->set('telnetPort', $config['port_telnet'])
                ->set('mikrotikApiPort',$config['port_api'] )
                ->set('telnetTimeout', 10)
        );
    }
    protected function getDevIpFromHeader() {
        $headers = $this->request->getHeader('X-Device-Ip');
        if(count($headers) > 0) {
            return $headers[0];
        }
        return "";
    }
}