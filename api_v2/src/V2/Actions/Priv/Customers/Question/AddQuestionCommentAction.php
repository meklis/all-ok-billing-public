<?php


namespace Api\V2\Actions\Priv\Customers\Question;


use Api\Infrastructure\Pagination;
use Api\V2\Actions\Action;
use envPHP\classes\std;
use envPHP\service\Customer\Questions\Questions;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class AddQuestionCommentAction extends Action
{
    protected $form = [
        'destination_time' => '01.01.1970 00:00:00',
        'comment' => '',
        'entrance' => 0,
        'floor' => 0,
        'responsible_employee_id' => 0,
    ];
    protected function action(): Response
    {
        $question = new Questions();
        $data = $this->getFormData();
        $this->fillDefaultKeys($this->form, $data);
        $data['question_id'] = $this->request->getAttribute('id');
        return  $this->respondWithData($question->edit(
            $data['question_id'],
            $this->request->getQueryParams()['USER_ID'],
            $data['destination_time'],
            $data['comment'],
            $data['entrance'],
            $data['floor'],
            $data['responsible_employee_id']
        ));
    }

    function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }
}