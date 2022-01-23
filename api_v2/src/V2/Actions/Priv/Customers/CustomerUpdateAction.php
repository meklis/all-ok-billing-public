<?php


namespace Api\V2\Actions\Priv\Customers;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\service\Customer\GeneralInfo;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class CustomerUpdateAction extends Action
{
    protected $props = [];
    protected function action(): Response
    {
        $customerId = $this->request->getAttribute('id');
        $params = $this->getFormData();
        $customerInfo = new GeneralInfo($customerId);
        return $this->respondWithData($customerInfo->update($params)->get());
    }

}