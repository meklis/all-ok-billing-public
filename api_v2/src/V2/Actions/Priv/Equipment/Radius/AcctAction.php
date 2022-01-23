<?php


namespace Api\V2\Actions\Priv\Equipment\Radius;

use Api\V2\Actions\Action;
use envPHP\service\bindingsDB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;


class AcctAction extends Action
{
    protected $conf;

    function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->conf = getGlobalConfigVar('RADIUS');
        if (!$this->conf) {
            throw new \Exception("API for radius not configured");
        }
        if (!@$this->conf['enabled']) {
            throw new HttpForbiddenException($this->request, "API for radius disabled");
        }
    }

    protected function action(): Response
    {
        if (!$this->conf['acct_logging']) {
            return $this->respondWithData(false);
        }
        $req = $this->getFormData();
        \envPHP\EventSystem\EventRepository::getSelf()->notify('radius:acct', $req);
      //  $this->response = $this->response->withHeader('Connection', 'close');
        return $this->respondWithData($req);
    }
}