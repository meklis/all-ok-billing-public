<?php


namespace Api\V2\Actions\Priv\Equipment\Binding;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\service\bindings;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class AddBindingAction extends Action
{
    protected $form = [
      'ip' => '',
      'activation' => 0,
      'mac' => '',
      'switch' => '',
      'port' => '',
    ];
    protected function action(): Response
    {
        $data = $this->getFormData();
        $this->fillDefaultKeys($this->form, $data);
        return  $this->respondWithData(bindings::add($data['activation'], $data['ip'], $data['mac'],$data['switch'],$data['port'],$this->request->getQueryParams()['USER_ID'], $data['real_ip']), [
            'user_id' => $this->request->getQueryParams()['USER_ID'],
        ]);
    }

}