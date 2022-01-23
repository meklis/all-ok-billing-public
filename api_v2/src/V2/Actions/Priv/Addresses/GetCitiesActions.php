<?php


namespace Api\V2\Actions\Priv\Addresses;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetCitiesActions extends Action
{
    protected function action(): Response
    {
       return $this->respondWithData(
           dbConnPDO()->query("SELECT * FROM addr_cities order by name")->fetchAll()
       );
    }

}