<?php


namespace envPHP\service\Customer\Questions;


use envPHP\classes\Num2TextUa;
use envPHP\pdf\CompletionPdfPrinter;
use envPHP\service\Helpers;
use envPHP\service\ServiceDataWrapper;
use Mpdf\Mpdf;

class Questions extends ServiceDataWrapper
{
    protected $cfgCompletion = [];
    protected $cfgRules = [];
    protected $cfgPictures = [];

    function __construct()
    {
        $this->cfgRules = getGlobalConfigVar('QUESTION_RULES');
        $this->cfgCompletion = getGlobalConfigVar('CERT_OF_COMPLETION');
        $this->cfgPictures = getGlobalConfigVar('PICTURES');
        parent::__construct();
    }

    protected function isAgreementExist($agreementId)
    {
        $psth = $this->sql->prepare("SELECT id FROM clients WHERE id = ?");
        $psth->execute([$agreementId]);
        return $psth->rowCount() !== 0;
    }

    protected function isEmployeeExist($employeeId)
    {
        $psth = $this->sql->prepare("SELECT id FROM employees WHERE id = ?");
        $psth->execute([$employeeId]);
        return $psth->rowCount() !== 0;
    }

    protected function isReasonExist($reasonId)
    {
        $psth = $this->sql->prepare("SELECT id FROM question_reason WHERE id = ?");
        $psth->execute([$reasonId]);
        return $psth->rowCount() !== 0;
    }

    public function create($agreementId, $createdEmployee, $phone, $reasonId, $destinationTime, $comment = "", $entrance = 0, $floor = 0, $responsibleEmployee = 0)
    {
        if (!$this->isAgreementExist($agreementId)) {
            throw new \InvalidArgumentException("Agreement with id $agreementId doesn't exist");
        }
        if (!$this->isEmployeeExist($createdEmployee)) {
            throw new \InvalidArgumentException("Employee with id $createdEmployee doesn't exist");
        }
        if (!$this->isReasonExist($reasonId)) {
            throw new \InvalidArgumentException("Reason with id $reasonId doesn't exist");
        }
        if ($responsibleEmployee !== 0 && !$this->isEmployeeExist($responsibleEmployee)) {
            throw new \InvalidArgumentException("Responsible employee id $responsibleEmployee doesn't exist");
        }
        $phone = Helpers::beautyPhoneNumber($phone);

        $psth = $this->sql->prepare("INSERT INTO questions (agreement, created, phone, reason) 
              VALUES (?, NOW(), ?, ?)");
        $psth->execute([$agreementId, $phone, $reasonId]);
        $insertedId = $this->sql->lastInsertId();

        $psth = $this->sql->prepare("INSERT INTO question_comments (created_at, question, dest_time, `comment`, employee, responsible_employee, entrance, floor) VALUES 
                                                                            (NOW(), ?, STR_TO_DATE(?,'%d.%m.%Y %H:%i:%s'), ?, ?, ?, ?, ?)");
        $psth->execute([$insertedId, $destinationTime, $comment, $createdEmployee, $responsibleEmployee, $entrance, $floor]);
        return $insertedId;
    }

    public function addReport($employeeId, $questionId, $status, $comment, $amount = 0, $photos = [], $certificateOfCompletion = [])
    {
        $response = [];
        try {
            $this->_addReportActivationControl($employeeId, $questionId, $status);
        } catch (\Exception $e) {
            $response[] = [
                'status' => 'fail',
                'message' => 'Возникли проблемы с обработкой автоматического контроля услуг',
                'debug' => $e->getMessage(),
            ];
        }
        $amount = $this->_addReportCalculateAmount($amount, $certificateOfCompletion);
        $this->sql->prepare("INSERT INTO question_responses (created_at, question, `comment`, `status`, employee, amount)
                               VALUES (NOW(), ?,?,?,?,?)")->execute([$questionId, $comment, $status, $employeeId, $amount]);
        $responseId = $this->sql->lastInsertId();

        if($certificateOfCompletion) {
            try {
                $this->_addReportGenerateCertificateOfCompletion($questionId, $responseId, $certificateOfCompletion);
            } catch (\Exception $e) {
                $response[] = [
                    'status' => 'fail',
                    'message' => 'Не удалось сгенерировать акт выполненных работ',
                    'debug' => $e->getMessage(),
                ];
            }
        }

        if($photos) {
            try {
                 $response = array_merge($response, $this->_addReportUploadImages($photos,$responseId));
            } catch (\Exception $e) {
                $response[] = [
                    'status' => 'fail',
                    'message' => 'Не удалось сохранить фотографии',
                    'debug' => $e->getMessage(),
                ];
            }
        }
        return [
          'report_id' => $responseId,
          'statuses' =>  $response,
        ];
    }
    protected function _addReportUploadImages($photos, $report_id) {
        $response = [];
        foreach ($photos as $photo) {
            $extension = strtolower(Helpers::getExtensionFromBase64($photo['data']));
            if(!in_array($extension, ['png', 'jpg', 'jpeg', 'git'])) {
                $response[] = [
                    'status' => 'fail',
                    'message' => "Не удалось загрузить файл - {$photo['name']}",
                    'debug' => 'Allow only images for upload',
                ];
                continue;
            }
            $name = md5($photo['data']) . '.' . $extension;
            if(!Helpers::saveBase64File($photo['data'], $this->cfgPictures['system_path'] . '/' . $name)) {
                $response[] = [
                    'status' => 'fail',
                    'message' => "Не удалось загрузить файл - {$photo['name']}",
                    'debug' => 'Error save photo to file system',
                ];
            }
            $this->sql->prepare("INSERT INTO `question_responses_pictures` (response_id, name) VALUES (?,?);")->execute([$report_id, $name]);

        }
        return $response;
    }
    protected function _addReportGenerateCertificateOfCompletion($questionId, $reportId, $completion) {
        if(!$this->cfgCompletion || !$this->cfgCompletion['enabled']) {
            return false;
        }
        //Получение инфы о абоненте
        $psth = $this->sql->prepare("SELECT c.name, c.agreement, CONCAT(a.full_addr, ', кв. ', c.apartment) addr 
                FROM questions q  
                JOIN clients c on c.id = q.agreement
                JOIN addr a on a.id = c.house
                WHERE q.id = ?");
        $psth->execute([$questionId]);
        if($psth->rowCount() == 0) {
            throw new \Exception("Not found agreement info by question with id $reportId");
        }
        $abonent_info = $psth->fetchAll()[0];

        $template = file_get_contents($this->cfgCompletion['template_path'] . "/cert_of_completion/template.html");
        if(!$template) {
            throw new \Exception("Error get template for certification of complete");
        }
        $completion_printer = new CompletionPdfPrinter(new Mpdf([
            'tempDir' => '/tmp/php/pdf',
            'format' => [210, 297],
        ]));

        $summary_amount = 0;
        foreach ($completion as $c) {
            if($c['price'] === '') continue;
            $completion_printer->addCompletionRow($c['name'], $abonent_info['agreement'], $c['count'], $c['price']);
            $summary_amount += ($c['count'] * $c['price']);
        }
        $completion_printer->setVariables([
            'ACT_NUMBER' => $questionId,
            'ACT_DATE' => date("d.m.Y") . "р",
            'ABON_NAME' => $abonent_info['name'],
            'ABON_ADDR' => $abonent_info['addr'],
            'SUMMARY_AMOUNT_AS_TEXT' => Num2TextUa::getTextByNum($summary_amount),
            'SUMMARY_AMOUNT' => $summary_amount,
        ]);
        $completion_printer->setTemplate($template)->prepareTemplate()->save($this->cfgCompletion['path'] . "/" . $reportId .".pdf");
         $this->sql->prepare("UPDATE question_responses SET cert_of_completion = ? WHERE id = ?")->execute([json_encode($completion), $reportId]);
        return  $this;
    }

    protected function _addReportActivationControl($employeeId, $questionId, $status)
    {
        if ($status == 'DONE' && $this->cfgRules && $this->cfgRules['enabled']) {
            $rules = new \envPHP\service\QuestionRules($questionId);
            $rules->proccess($employeeId);
        }
        return true;
    }

    protected function _addReportCalculateAmount($amount, $cert_of_completion)
    {
        if ($this->cfgCompletion && $this->cfgCompletion['enabled'] && is_array($cert_of_completion) && count($cert_of_completion) !== 0) {
            $amount = 0;
            foreach ($cert_of_completion as $c) {
                if ($c['price'] === '') continue;
                $amount += ($c['count'] * $c['price']);
            }
        }
        return $amount;
    }

    public function getReports($questionId)
    {
        $reportsPsth = $this->sql->prepare("SELECT r.id
            , r.question question_id 
            , r.created_at
            , r.comment
            , r.status
            , e.id employee_id 
            , e.name employee_name
            , IFNULL(r.amount,0) amount 
            , IFNULL(r.cert_of_completion, '[]') cert_of_completion
            , IFNULL(r.cert_subscribed, 0) cert_of_completion_subscribed
            FROM question_responses r 
            JOIN employees e on e.id = r.employee  
            WHERE r.question = ?  
            ORDER BY id desc ");
        $reportsPsth->execute([$questionId]);
        $reports = [];
        $reportsRAW = $reportsPsth->fetchAll();
        $pictPSTH = $this->sql->prepare("SELECT id, created_at, name, response_id, CONCAT('" . getGlobalConfigVar('PICTURES')['http_path'] . "', '/', name) url
                FROM question_responses_pictures WHERE response_id = ?");
        foreach ($reportsRAW as $report) {
            $path_of_cert = null;
            $certOfCompletion = json_decode($report['cert_of_completion'], true);
            if (is_array($certOfCompletion) && count($certOfCompletion) != 0) {
                $path_of_cert = "/load_pdf?file_path=question_certs/{$report['id']}.pdf";
            }
            if ($report['cert_of_completion'] == 1) {
                $path_of_cert = "/load_pdf?file_path=question_certs_subscribed/{$report['id']}.pdf";
            }
            $pictPSTH->execute([$report['id']]);
            $reports[] = [
                'id' => $report['id'],
                'question_id' => $report['question_id'],
                'created_at' => $report['created_at'],
                'comment' => $report['comment'],
                'status' => $report['status'],
                'employee_id' => $report['employee_id'],
                'employee_name' => $report['employee_name'],
                'amount' => $report['amount'],
                'cert_of_completion' => json_decode($report['cert_of_completion'], true),
                'cert_path' => $path_of_cert,
                'cert_of_completion_subscribed' => $report['cert_of_completion'] == 1,
                'pictures' => $pictPSTH->fetchAll(),
            ];
        }
        return $reports;
    }

    public function getQuestionInfo($questionId)
    {
        $psth = $this->sql->prepare("SELECT 
                q.id
                ,q.created created_at 
                , e.id created_employee_id
                ,e.`name` created_employee_name
                ,s.id agreement_id 
                ,s.agreement agreement
                ,q.`comment`
                ,q.phone
                ,q.reason_id reason_id
                ,q.reason reason_name
                ,CONCAT('г.',c.name,', ', st.name, ', д.', h.`name`, ', под.',s.entrance, ', эт.',s.floor, ', кв.', s.apartment) addr
                ,q.dest_time
                ,q.report_status `status`
                ,q.report_comment
                ,re.id responsible_employee_id
                ,re.name responsible_employee_name
                ,e2.id reported_employee_id
                ,e2.name resported_employee_name 
                ,q.amount
                FROM questions_full q 
                JOIN clients s on q.agreement = s.id
                JOIN addr_houses h on h.id = s.house
                JOIN addr_streets st on st.id = h.street
                JOIN addr_cities c on c.id = st.city
                LEFT JOIN employees e on e.id = q.created_employee
                LEFT JOIN employees e2 on e2.id = q.reported_employee
                LEFT JOIN employees re on re.id = q.responsible_employee 
                WHERE q.id = ?");
        $psth->execute([$questionId]);
        if ($psth->rowCount() !== 0) {
            $response = $psth->fetchAll()[0];
            $response['updates'] = $this->getComments($questionId);
            $response['reports'] = $this->getReports($questionId);
            return $this->wrapForResponse($response);
        } else {
            throw new \InvalidArgumentException("Question with id $questionId doesn't exist");
        }
    }

    public function getComments($questionId)
    {
        $psth = $this->sql->prepare("SELECT 
        c.id,
        q.id question_id,
        c.created_at,
        c.dest_time,
        e.name employee,
        c.`comment` comment,
        r.name reason_name,
        r.id reason_id,
        a.agreement,
        q.phone,
        c.responsible_employee responsible_id,
        er.name responsible_name,
        c.entrance,
        c.floor
        FROM questions q 
        JOIN clients a on a.id = q.agreement 
        JOIN question_comments c on c.question = q.id 
        JOIN employees e on e.id = c.employee
        JOIN question_reason r on r.id = q.reason
        LEFT JOIN employees er on er.id = c.responsible_employee
        WHERE q.id = ?
        ORDER BY id desc ");
        $psth->execute([$questionId]);
        return $this->wrapArrayForResponse($psth->fetchAll());
    }

    public function getPossibleReasons()
    {
        return $this->sql->query("SELECT id, name FROM question_reason WHERE display = 'YES' order by 2")->fetchAll();
    }

    public function edit($questionId, $createdEmployee, $destinationTime, $comment, $entrance, $floor, $responsible)
    {
        if (!$this->isEmployeeExist($createdEmployee)) {
            throw new \InvalidArgumentException("Employee with id $createdEmployee doesn't exist");
        }
        if ($responsible !== 0 && !$this->isEmployeeExist($responsible)) {
            throw new \InvalidArgumentException("Responsible employee id $responsible doesn't exist");
        }
        $psth = $this->sql->prepare("INSERT INTO question_comments (created_at, question, dest_time, `comment`, employee, responsible_employee, entrance, floor) VALUES 
                                                                            (NOW(), ?, STR_TO_DATE(?,'%d.%m.%Y %H:%i:%s'), ?, ?, ?, ?, ?)");
        $psth->execute([$questionId, $destinationTime, $comment, $createdEmployee, $responsible, $entrance, $floor]);
        return $this->sql->lastInsertId();
    }

}