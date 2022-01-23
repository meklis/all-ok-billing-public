<?php


namespace Api\V2\Actions\Priv\Addresses;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetGroupsAction extends Action
{

    protected function action(): Response
    {
        $args = [];
        $where = ' 1=1 ';
        $sth = dbConnPDO()->prepare("SELECT * FROM addr_groups WHERE $where order by name");
        $sth->execute($args);
       return $this->respondWithData(
           $sth->fetchAll()
       );
    }

}