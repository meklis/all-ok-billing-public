<?php


namespace Api\V2\Actions\Priv\Customers\Question;


use Api\Infrastructure\Pagination;
use Api\V2\Actions\Action;
use envPHP\classes\std;
use envPHP\service\Customer\Questions\Questions;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetQuestionDetailAction extends Action
{
    protected function action(): Response
    {
        $question = new Questions();
        return  $this->respondWithData($question->getQuestionInfo($this->request->getAttribute('id')));
    }

    function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }
}