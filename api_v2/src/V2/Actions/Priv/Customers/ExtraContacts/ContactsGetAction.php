<?php


namespace Api\V2\Actions\Priv\Customers\ExtraContacts;


use Api\V2\Actions\Action;
use envPHP\structs\ClientContact;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class ContactsGetAction extends ContactsAbstractAction
{
    protected function action(): Response
    {
        $id = $this->request->getAttribute('id');
        return $this->respondWithData(ClientContact::getById($id, true));
    }
}