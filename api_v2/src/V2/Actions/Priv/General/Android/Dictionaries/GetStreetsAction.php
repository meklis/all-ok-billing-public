<?php


namespace Api\V2\Actions\Priv\General\Android\Dictionaries;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetStreetsAction extends Action
{
    protected function action(): Response
    {
        return  $this->respondWithData(
            dbConnPDO()->query("SELECT id, name, city FROM addr_streets WHERE `show` = 1 ")->fetchAll()
        );
    }
}