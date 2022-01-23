<?php


namespace Api\V2\Actions\SwitcherCore\Switcher;


use Api\V2\Actions\SwitcherCore\SwitcherCoreAction;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetDeviceStoreInfoAction extends SwitcherCoreAction
{
    protected function action(): Response
    {
        $req = $this->request->getQueryParams();
        $dev_ip = $this->getDevIpFromHeader();
        if(!$dev_ip) {
            $dev_ip = $req['ip'];
        }
        if($dev_ip) {
            return $this->respondWithData(
                $this->getDeviceStoreInfo($dev_ip)
            );
        } else {
            throw new HttpBadRequestException($this->request, "Parameter 'ip' is required ");
        }
    }

}