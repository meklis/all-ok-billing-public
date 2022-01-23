<?php


namespace Api\V2\Actions\Priv\Customers\Activations;


use Api\Infrastructure\Pagination;
use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\service\Customer\Activation;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class GetActivationPricesActions extends AbstractActivationAction
{
    protected $form = [
        'customer_id' => 0,
    ];
    function action(): Response
    {
        $this->replaceQueryParams($this->form);
        return $this->respondWithData(
            $this->services->getPossibleActivations($this->form['customer_id'])
        );
    }
}