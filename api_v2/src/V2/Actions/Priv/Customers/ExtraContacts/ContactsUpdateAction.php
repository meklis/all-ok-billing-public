<?php


namespace Api\V2\Actions\Priv\Customers\ExtraContacts;


use Api\V2\Actions\Action;
use envPHP\structs\ClientContact;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class ContactsUpdateAction extends ContactsAbstractAction
{
    protected function action(): Response
    {
        $id = $this->request->getAttribute('id');
        $contact = ClientContact::getById($id);
        $form = $this->getFormData();
        if(isset($form['agreement_id'])) $contact->setAgreementId($form['agreement_id']);
        if(isset($form['name'])) $contact->setName($form['name']);
        if(isset($form['type'])) $contact->setType($form['type']);
        if(isset($form['value'])) $contact->setValue($form['value']);
        if(isset($form['main'])) $contact->setMain($form['main']);
        $contact->setEmployeeId($this->request->getQueryParams()['USER_ID']);
        $contact->save();
        return $this->respondWithData(ClientContact::getById($id, true));
    }
}