<?php


namespace Api\V2\Actions\Pub;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\Schedule\SlotCalculation;
use envPHP\service\Question;
use envPHP\service\TrinityControl;
use envPHP\structs\Employee;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class PublicCreateQuestion extends Action
{
    protected function action(): Response
    {
        $data = $this->getFormData();
        $client =  (new \envPHP\structs\Client())->fillById($data['agreement_id']);
        if(!$data['dest_time'] && $data['reason_id']) {
            $psth = dbConnPDO()->prepare("SELECT reaction_time FROM question_reason WHERE id=?");
            $psth->execute([$data['reason_id']]);
        }
        return $this->respondWithData(Question::create(
            $client,
            $data['reason_id'],
            (new Employee())->fillById($data['employee_id']),
            $data['phone'],
            $data['comment'],
            $data['dest_time']
        ));
    }

}