<?php


namespace Api\V2\Actions\Priv\Equipment\Binding;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\service\bindings;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetBindingAction extends Action
{
    protected function action(): Response
    {
        $id = $this->request->getAttribute('id', 0);
        return  $this->respondWithData(bindings::get($id), [
            'user_id' => $this->request->getQueryParams()['USER_ID'],
        ]);
    }

}