<?php


namespace Api\V2\Actions\Priv\Customers\Question;


use Api\Infrastructure\Pagination;
use Api\V2\Actions\Action;
use envPHP\classes\std;
use envPHP\service\Customer\Questions\Questions;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class AddQuestionReportAction extends Action
{
    protected $form = [
        'status' => 'IN_PROCESS',
        'comment' => '',
        'amount' => 0.0,
        'photos' => [],
        'certificate_of_completion' => [],
    ];
    protected function action(): Response
    {
        $question = new Questions();
        $data = $this->getFormData();
        $this->fillDefaultKeys($this->form, $data);
        $data['question_id'] = $this->request->getAttribute('id');
        return  $this->respondWithData($question->addReport(
            $this->request->getQueryParams()['USER_ID'],
            $data['question_id'],
            $data['status'],
            $data['comment'],
            $data['amount'],
            $data['photos'],
            $data['certificate_of_completion']
        ));
    }

    function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }
}