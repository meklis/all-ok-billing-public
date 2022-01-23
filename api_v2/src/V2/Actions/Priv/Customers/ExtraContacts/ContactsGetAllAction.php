<?php


namespace Api\V2\Actions\Priv\Customers\ExtraContacts;


use Api\V2\Actions\Action;
use envPHP\structs\ClientContact;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class ContactsGetAllAction extends ContactsAbstractAction
{
    protected function action(): Response
    {
        if(!isset($this->request->getQueryParams()['agreement_id'])) {
            throw new HttpBadRequestException($this->request, "agreement_id is required");
        }
        $agreementId = $this->request->getQueryParams()['agreement_id'];
        return $this->respondWithData( ClientContact::getAllContacts($agreementId, true));
    }
}