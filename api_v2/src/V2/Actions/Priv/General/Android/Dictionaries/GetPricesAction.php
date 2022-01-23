<?php


namespace Api\V2\Actions\Priv\General\Android\Dictionaries;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetPricesAction extends Action
{
    protected function action(): Response
    {
        return  $this->respondWithData(
            dbConnPDO()->query("SELECT id, `name`, price_day, price_month, recalc_time, `show`, speed, provider, days_to_disable, sms_name, work_type FROM bill_prices")->fetchAll()
        );
    }
}