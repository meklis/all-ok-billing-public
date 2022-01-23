<?php


namespace Api\V2\Actions\Priv\Equipment\Pinger;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetHostListAction extends Action
{
    protected function action(): Response
    {
        $sql = dbConnPDO();
        $data = $sql->query("SELECT ip, ping status, last_ping FROM equipment ORDER BY last_ping");
        $data->execute();
        $resp = [];
        $count = $data->rowCount();
        foreach ($data->fetchAll() as $el) {
            $resp[] = [
              'ip' => $el['ip'],
              'status' => (int)$el['status'],
            ];
        }
        return $this->respondWithData($resp, [
            'count' => (int) $count,
        ]);
    }

}