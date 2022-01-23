<?php


namespace Api\V2\Actions\Priv\Addresses;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class GetHouseInfoAction extends Action
{

    protected function action(): Response
    {
        $houseId = $this->request->getAttribute('id');
        $sth = dbConnPDO()->prepare("SELECT c.id city_id
            , c.name city_name
            , st.id street_id
            , st.`name` street_name
            , gr.id group_id
            , gr.`name` group_name
            , h.id id
            , h.`name` name
            , h.entrances
            , h.floors
            , h.apartments
            , h.descr
            FROM addr_houses h 
            JOIN addr_groups gr on gr.id = h.group_id
            JOIN addr_streets st on st.id = h.street
            JOIN addr_cities c on c.id = st.city
            JOIN (SELECT house house_id, count(*) c FROM clients GROUP BY house ) ch on ch.house_id = h.id 
            JOIN (SELECT house house_id, count(*) c FROM equipment GROUP BY house ) eh on eh.house_id = h.id  
            WHERE h.id = ?");
        $sth->execute([$houseId]);
        if($sth->rowCount() === 0) {
            throw new HttpNotFoundException($this->request, "House with id=$houseId not found");
        }
       return $this->respondWithData(
           $sth->fetchAll()[0]
       );
    }

}