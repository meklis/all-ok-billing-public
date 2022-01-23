<?php
declare(strict_types=1);

namespace Api\V2\Actions\User;

use Api\V2\Actions\Action;
use envPHP\service\User;
use Psr\Log\LoggerInterface;

abstract class UserAction extends Action
{
    /**
     * @var User
     */
    protected $user;

    /**
     * UserAction constructor.
     * @param LoggerInterface $logger
     * @param User $user
     */
    public function __construct(LoggerInterface $logger, User $user)
    {
        parent::__construct($logger);
        $this->user = $user;
    }
}
