<?php


namespace Api\V2\Actions\Priv\General\Android\Dictionaries;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetProvidersAction extends Action
{
    protected function action(): Response
    {
        return  $this->respondWithData(
            dbConnPDO()->query("SELECT id, name FROM providers")->fetchAll()
        );
    }
}