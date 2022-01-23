<?php


namespace Api\V2\Actions\Priv\Customers;


use Api\V2\Actions\Action;
use envPHP\classes\std;
use envPHP\service\Customer\GeneralInfo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

class CustomerInfoAction extends Action
{
    protected $cfg;
    protected $params =  [
        'provider' => 0,
        'id' => 0,
        'name' => '',
    ];
    protected $sql;
    protected function action(): Response
    {
        $id = $this->request->getAttribute('id');
        $customerInfo = new GeneralInfo($id);
        return $this->respondWithData($customerInfo->get());
    }
    function __construct(LoggerInterface $logger)
    {
        $this->sql = dbConnPDO();
        parent::__construct($logger);
    }

}