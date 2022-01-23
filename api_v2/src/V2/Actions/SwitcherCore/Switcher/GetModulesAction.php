<?php


namespace Api\V2\Actions\SwitcherCore\Switcher;


use Api\V2\Actions\SwitcherCore\SwitcherCoreAction;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetModulesAction extends SwitcherCoreAction
{
    protected function action(): Response
    {
        $modules = [];
        foreach ($this->initCoreByRequestParam()->getModulesData() as $module) {
            $modules[] = [
                'name' => $module['name'],
                'arguments' => array_values($module['arguments']),
            ];
        }
        return $this->respondWithData($modules);
    }

}