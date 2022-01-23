<?php


namespace Api\V2\Actions\Priv\Addresses;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetHousesAction extends Action
{

    protected function action(): Response
    {
        $args = [];
        $where = ' 1=1 ';
        $params = $this->request->getQueryParams();
        if(isset($params['street_id']) && $params['street_id']) {
            $args[] = $params['street_id'];
            $where .= " and street = ?";
        }
        if(isset($params['city_id']) && $params['city_id']) {
            $args[] = $params['city_id'];
            $where .= " and street in (SELECT id FROM addr_streets WHERE city = ?)";
        }
        if(isset($params['group_id']) && $params['group_id']) {
            $args[] = $params['group_id'];
            $where .= " and group_id = ?";
        }
        $sth = dbConnPDO()->prepare("SELECT * FROM addr_houses WHERE $where order by name");
        $sth->execute($args);
       return $this->respondWithData(
           $sth->fetchAll()
       );
    }

}
