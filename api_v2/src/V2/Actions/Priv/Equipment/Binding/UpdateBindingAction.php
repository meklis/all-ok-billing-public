<?php


namespace Api\V2\Actions\Priv\Equipment\Binding;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\service\bindings;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class UpdateBindingAction extends Action
{
    protected $defaults = [
        'ip' => '',
        'activation' => 0,
        'mac' => '',
        'switch' => '',
        'port' => '',
        'allow_static' => false,
    ];
    protected function action(): Response
    {
        $form = $this->getFormData();
        $this->fillDefaultKeys($this->defaults, $form);
        $id = $this->request->getAttribute('id', 0);
        return  $this->respondWithData(bindings::edit($id, $form['ip'], $form['mac'],$form['switch'],$form['port'],$this->request->getQueryParams()['USER_ID'], $form['allow_static']), [
            'user_id' => $this->request->getQueryParams()['USER_ID'],
        ]);
    }
}