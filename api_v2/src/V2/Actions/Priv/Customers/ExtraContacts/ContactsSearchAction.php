<?php


namespace Api\V2\Actions\Priv\Customers\ExtraContacts;


use Api\V2\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class ContactsSearchAction extends ContactsAbstractAction
{
    protected function action(): Response
    {
        return $this->respondWithData( $this->contacts->search($this->request->getQueryParams()));
    }
}