<?php


namespace Api\V2\Actions\SwitcherCore\Switcher;


use Api\V2\Actions\SwitcherCore\SwitcherCoreAction;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetDeviceInfoAction extends SwitcherCoreAction
{
    protected function action(): Response
    {
        return $this->respondWithData($this->initCoreByRequestParam()->getDeviceMetaData());
    }

}