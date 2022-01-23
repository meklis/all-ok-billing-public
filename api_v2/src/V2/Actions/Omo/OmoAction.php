<?php
declare(strict_types=1);

namespace Api\V2\Actions\Omo;

use Api\V2\Actions\Action;
use envPHP\service\OmoControl;
use envPHP\service\OmoLocalControl;
use Psr\Log\LoggerInterface;

abstract class OmoAction extends Action
{
    /**
     * @var OmoControl
     */
    protected $control;

    /**
     * UserAction constructor.
     * @param LoggerInterface $logger
     * @param User $user
     */
    public function __construct(LoggerInterface $logger, OmoControl $omo)
    {
        parent::__construct($logger);
        $this->control = $omo;
    }
}
