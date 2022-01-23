<?php


namespace Api\V2\Actions\Priv\Customers\Question;


use Api\Infrastructure\Pagination;
use Api\V2\Actions\Action;
use envPHP\classes\std;
use envPHP\service\Customer\Questions\Questions;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetQuestionReasonsAction extends Action
{
    protected function action(): Response
    {
        $question = new Questions();
        return  $this->respondWithData($question->getPossibleReasons());
    }

    function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }
}