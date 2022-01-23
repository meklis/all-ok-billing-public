<?php


namespace Api\V2\Actions\Priv\Addresses;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetStreetsAction extends Action
{

    protected function action(): Response
    {
        $args = [];
        $where = ' 1=1 ';
        $params = $this->request->getQueryParams();
        if(isset($params['city_id'])) {
            $args[] = $params['city_id'];
            $where .= " and city = ?";
        }
        $sth = dbConnPDO()->prepare("SELECT * FROM addr_streets WHERE $where order by name");
        $sth->execute($args);
       return $this->respondWithData(
           $sth->fetchAll()
       );
    }

}