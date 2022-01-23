<?php


namespace Api\V2\Actions\SwitcherCore\Switcher;


use Api\V2\Actions\SwitcherCore\SwitcherCoreAction;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class CallModuleAction extends SwitcherCoreAction
{
    protected function action(): Response
    {
        $calledModule = $this->request->getAttribute('module');
        $core = $this->initCoreByRequestParam();
        $modules = $core->getModulesData();
        $modulesNames = [];
        foreach ($modules as $module) {
            $modulesNames[] = $module['name'];
        }
        if(!in_array($calledModule, $modulesNames)) {
            throw new HttpBadRequestException($this->request, "Module $calledModule not exist for device");
        }
 
        return $this->respondWithData($this->initCoreByRequestParam()->action($calledModule, $this->request->getQueryParams()));
    }

}