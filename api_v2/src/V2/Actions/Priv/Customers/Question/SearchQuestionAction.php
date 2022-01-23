<?php


namespace Api\V2\Actions\Priv\Customers\Question;


use Api\Infrastructure\Pagination;
use Api\V2\Actions\Action;
use envPHP\classes\std;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class SearchQuestionAction extends Action
{
    protected $params =  [
        'id' => 0,
        'agreement' => 0,
        'agreement_id' => 0,
        'date_start' => '',
        'date_stop' => '',
        'type_date' => 'CREATED',
        'city' => 0,
        'street' => 0,
        'house_id' => 0,
        'reason' => 0,
        'responsible' => 0,
        'page_no' => 1,
        'status' => '',
        'records_per_page' => 50
    ];
    protected $sql;
    protected function action(): Response
    {
        $this->replaceQueryParams($this->params);
        $psth = $this->prepareStatment();

        $dbData = $psth->fetchAll(\PDO::FETCH_ASSOC);
        $resp = [];
        if($this->params['status']) {
            $dbData = array_filter($dbData, function ($e)  {
               return $e['status'] === $this->params['status'];
            });
        }
        foreach ($dbData as $k=>$d) {
                if($d['status'] === 'NEW') {
                    $resp[] = $d;
                }
        }
        foreach ($dbData as $k=>$d) {
                if($d['status'] === 'DONE') {
                    $resp[] = $d;
                }
        }
        foreach ($dbData as $k=>$d) {
                if($d['status'] === 'IN_PROCESS') {
                    $resp[] = $d;
                }
        }
        foreach ($dbData as $k=>$d) {
                if($d['status'] === 'CANCEL') {
                    $resp[] = $d;
                }
        }
        $pagination = new Pagination($resp, $this->params['records_per_page']);
        return $this->respondWithData($pagination->getPage($this->params['page_no']), [
            'pagination' => [
                'page_no' => (int) $this->params['page_no'],
                'records_per_page' => (int) $this->params['records_per_page'],
                'total_pages' => $pagination->getTotalPages(),
                'total_records' => $pagination->getTotalRecords(),
            ]
        ]);
    }

    /**
     * @return  \PDOStatement
     */
    protected function prepareStatment() {
        $params = [];
        $sqlStr = '';
        if ($this->params['house_id'] != 0) {
            $sqlStr .= " and s.house  = ?";
            $params[] = $this->params['house_id'];
        };
        if ($this->params['city'] != 0) {
            $sqlStr .= " and c.id  = ?";
            $params[] = $this->params['city'];
        };
        if ($this->params['street'] != 0) {
            $sqlStr .= " and st.id  = ?";
            $params[] = $this->params['street'];
        };
        if($this->params['date_start'] && $this->params['date_stop']) {
            if ($this->params['type_date'] != 'CREATED') {
                $sqlStr .= " and cast(q.dest_time as date) BETWEEN ? and ? ";
            } else {
                $sqlStr .= " and cast(q.created as date) BETWEEN ? and ?";
            }
            $params[] = $this->params['date_start'];
            $params[] = $this->params['date_stop'];
        }
        if ($this->params['agreement']) {
            $sqlStr .= " and s.agreement = ?";
            $params[] = $this->params['agreement'];
        }
        if ($this->params['agreement_id']) {
            $sqlStr .= " and s.id = ?";
            $params[] = $this->params['agreement_id'];
        }
        if ($this->params['reason']) {
            $sqlStr .= " and q.reason = ?";
            $params[] = $this->params['reason'];
        }
        if ($this->params['responsible']) {
            $sqlStr .= " and q.responsible_employee = ?";
            $params[] = $this->params['responsible'];
        }
        $query =  "SELECT 
            q.id
            ,q.created
            ,e.`name` created_employee
            ,s.agreement
            ,s.id agreement_id
            ,q.`comment` 
            ,q.phone
            ,q.reason
            ,CONCAT('г.',c.name,', ', st.name, ', д.', h.`name`, ', под.',s.entrance, ', кв.', s.apartment) addr
            , s.house house_id
            ,q.dest_time
            ,ifnull(q.report_status, 'NEW') status 
            ,q.report_comment
            , re.name responsible_employee
            ,e2.name reported_employee 
            
            FROM questions_full q 
            JOIN clients s on q.agreement = s.id
            JOIN addr_houses h on h.id = s.house
            JOIN addr_streets st on st.id = h.street
            JOIN addr_cities c on c.id = st.city
            LEFT JOIN employees e on e.id = q.created_employee
            LEFT JOIN employees e2 on e2.id = q.reported_employee
            LEFT JOIN employees re on re.id = q.responsible_employee
            WHERE 1=1 $sqlStr
            ORDER BY id desc";
        $psth = $this->sql->prepare($query);
        $psth->execute($params);
        return $psth;
    }

    function __construct(LoggerInterface $logger)
    {
        $this->sql = dbConnPDO();
        parent::__construct($logger);
    }
}