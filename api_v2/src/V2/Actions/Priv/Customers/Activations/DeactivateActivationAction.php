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

class DeactivateActivationAction extends AbstractActivationAction
{
    protected $form = [
        'activation_id' => 0,
    ];
    function action(): Response
    {
        $data = $this->fillArray($this->getFormData(), $this->form);
        return $this->respondWithData(
            $this->services->deactivate(
                $data['activation_id'],
                $this->request->getQueryParams()['USER_ID'],
                false
            )
        );
    }
}