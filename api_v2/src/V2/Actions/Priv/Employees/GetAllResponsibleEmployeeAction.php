<?php


namespace Api\V2\Actions\Priv\Employees;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetAllResponsibleEmployeeAction extends Action
{
    protected function action(): Response
    {
       return $this->respondWithData(
         dbConnPDO()->query("SELECT e.id, e.name, e.phone, e.mail email, p.position position_name, p.id position_id
FROM employees e 
JOIN emplo_positions p on p.id = e.position
WHERE `display` = 1 
ORDER BY name, position_name ")->fetchAll()
       );
    }

}