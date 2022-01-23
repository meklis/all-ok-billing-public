<?php


namespace Api\V2\Actions\Priv\Customers\ExtraContacts;


use Api\V2\Actions\Action;
use envPHP\service\Customer\ExtraContacts;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;

abstract  class ContactsAbstractAction extends Action
{
    protected $contacts;
    function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

}