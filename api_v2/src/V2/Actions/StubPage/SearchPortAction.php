<?php


namespace Api\V2\Actions\StubPage;


use Api\V2\Actions\Action;
use envPHP\EventSystem\EventRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Ramsey\Uuid\Uuid;

class SearchPortAction extends Action
{
    protected function action(): Response
    {
        $search = (new \Meklis\BillingNetworkHelpers\SearchIP(
            new \envPHP\NetworkCore\SearchIp\DbStore()
        ))->setProxyConfigurationPath(getGlobalConfigVar('SWITCHER_CORE')['proxy_path_config']);
        $response = [];
        $params = $this->request->getQueryParams();
        $uuid = Uuid::uuid4()->toString();
        try {
            $found_result = $search->setIp($params['REMOTE_ADDR'])->search();
            $agreeInfo = $this->getAgreementBySwitchPort($found_result['device'], $found_result['port']);
            if(!$agreeInfo['id']) {
                throw new \Exception("User found on {$found_result['device']}:{$found_result['port']}, but agreement not found by bindings");
            }
            $response['uuid'] = $uuid;
            $response['mac_addr'] = $found_result['mac'];
            $response['ip_addr'] = $found_result['ip'];
       //     $response['real_addr'] = $agreeInfo['addr'];
       //     $response['real_addr_apartment'] = $agreeInfo['apartment'];
       //     $response['name'] = $agreeInfo['name'];
            $response['agreement'] = $agreeInfo['agreement'];
            $response['old_mac_addr'] = $agreeInfo['mac'];
            if($agreeInfo['bind_status'] == 'frosted') {
                $response['status'] = $agreeInfo['bind_status'];
            } elseif ($agreeInfo['mac'] !== $response['mac_addr']) {
                $response['status'] = 'unregistered';
            }

            $sth = dbConnPDO()->prepare("INSERT INTO stub_page_results (created_at, uuid, remote_addr,  search_result, agreement_id, old_mac_addr, new_mac_addr, binding_id) VALUES 
                                                                           (NOW(), :uuid, :remote_addr, :result, :agreement, :old_mac_addr, :new_mac_addr, :binding_id)");
            $sth->execute([
                ':remote_addr'=>$params['REMOTE_ADDR'],
                ':result' => json_encode($found_result),
                ':uuid' => $uuid,
                ':agreement' => $agreeInfo['id'],
                ':old_mac_addr' => $agreeInfo['mac'],
                ':new_mac_addr' => $found_result['mac'],
                ':binding_id' => $agreeInfo['binding_id'],
            ]);

        } catch (\Exception $e) {
            $sth = dbConnPDO()->prepare("INSERT INTO stub_page_results (created_at, uuid, remote_addr, error, search_result) VALUES 
                                                                           (NOW(), :uuid, :remote_addr, :err, :result)");
            $sth->execute([
                ':remote_addr'=>$params['REMOTE_ADDR'],
                ':err' => $e->getMessage(),
                ':uuid' => $uuid,
                ':result' => json_encode($response)
            ]);
            \envPHP\EventSystem\EventRepository::getSelf()->notify('stub_page:error_not_found', $response);
            throw new \Exception("USER_NOT_FOUND");
        }
        return $this->respondWithData($response);
    }
    protected function getAgreementBySwitchPort($switch, $port) {
        $res = dbConnPDO()->query("SELECT c.id, c.agreement, c.name, c.apartment, a.full_addr addr, b.mac, b.id binding_id, 'active' bind_status
            FROM clients c 
            JOIN client_prices pr on pr.agreement = c.id 
            JOIN eq_bindings b on b.activation = pr.id 
            JOIN equipment e on e.id = b.switch
            JOIN addr a on a.id = c.house
            WHERE e.ip = '$switch' and b.port = '$port' and pr.time_stop is null 
            ORDER BY b.id desc 
            LIMIT 1 ")->fetch(\PDO::FETCH_ASSOC);
        if(!$res['id']) {
            $res = dbConnPDO()->query("SELECT c.id, c.agreement, c.name, c.apartment, a.full_addr addr, b.mac, b.id binding_id, 'frosted' bind_status
            FROM clients c 
            JOIN client_prices pr on pr.agreement = c.id 
            JOIN eq_bindings b on b.activation = pr.id 
            JOIN equipment e on e.id = b.switch
            JOIN addr a on a.id = c.house
            WHERE e.ip = '$switch' and b.port = '$port'  
            ORDER BY b.id desc 
            LIMIT 1 ")->fetch(\PDO::FETCH_ASSOC);
        }
        return $res;
    }
}

