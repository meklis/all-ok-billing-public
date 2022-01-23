<?php


namespace Api\V2\Actions\Priv\Customers\ExtraContacts;


use Api\V2\Actions\Action;
use envPHP\structs\ClientContact;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class ContactsAddAction extends ContactsAbstractAction
{
    protected function action(): Response
    {
        $form = [
            'type' => 'PHONE',
            'value' => '',
            'agreement_id' => '',
            'employee_id' => '',
            'main' => false,
            'name' => '',
        ];
        $data = $this->getFormData();
        foreach ($data as $d=>$v) {
            $form[$d] = $v;
        }
        $form['employee_id'] = $this->request->getQueryParams()['USER_ID'];
        $contact = new ClientContact($form['type'], $form['agreement_id'], $form['value'], $form['name'], $form['employee_id'], $form['main']);
        $id = $contact->save()->getId();
        $resp = ClientContact::getById($id, true);
        $resp['main'] = $resp['main'] == 1 ? true : false;
        return $this->respondWithData($resp);
    }
}