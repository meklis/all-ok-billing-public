<?php


namespace Api\V2\Actions\Priv\Customers;


use Api\Infrastructure\Pagination;
use Api\V2\Actions\Action;
use envPHP\classes\std;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use SwitcherCore\Modules\Helper;

class CustomerSearchAction extends Action
{
    protected $sql;
    protected $params = [
        'records_per_page' => 50,
        'page_no' => 1,
    ];
    protected function action(): Response
    {
        $this->replaceQueryParams($this->params);
        $data = $this->request->getQueryParams();
        $WHERE = " s.status = 'ENABLED'";
        $params = [];
        if(isset($data['agreement'])) {
            $WHERE .= ' and s.agreement like ?';
            $params[] = "%{$data['agreement']}%";
        }
        if(isset($data['phone'])) {
            $WHERE .= ' and s.phone like ?';
            $params[] = "%{$data['phone']}%";
        }
        if(isset($data['name'])) {
            $WHERE .= ' and s.name like ?';
            $params[] = "%{$data['name']}%";
        }
        if(isset($data['house_id'])) {
            $WHERE .= ' and s.house = ?';
            $params[] = "{$data['name']}";
        }
        if(isset($data['id'])) {
            $WHERE .= ' and s.id = ?';
            $params[] = "{$data['id']}";
        }
        if(isset($data['address']) && $data['address'] != '') {
            list($houseId, $apartment) = explode(';', $data['address']);
            $WHERE .= ' and s.house = ?';
            $params[] = "{$houseId}";
            if($apartment != '-') {
                $WHERE .= ' and s.apartment = ?';
                $params[] = "{$apartment}";
            }
        }
        $psth = $this->sql->prepare("
                SELECT 
                s.id, 
                s.`status` client_status, 
                gr.id group_id,  
                gr.name group_name, 
                s.provider provider_id, 
                s.balance, 
                s.agreement, 
                em.email main_email, 
                ph.phone main_phone, 
                if(s.notice_mail = 0, false, true) notification_email,
                if(s.notice_sms = 0, false, true) notification_sms,
                s.add_time created_at, 
                s.name,    
                s.descr,  
                enable_credit, 
                enable_credit_period,
                a.full_addr address_house,
                s.entrance address_entrance, 
                s.floor address_floor,
                s.apartment address_apartment, 
                s.house address_house_id
                FROM clients s 
                JOIN addr a on a.id = s.house
                LEFT JOIN addr_groups gr on gr.id = a.group_id
                LEFT JOIN (SELECT agreement_id, `value` phone FROM client_contacts WHERE main = 1 and type = 'PHONE') ph on ph.agreement_id = s.id 
                LEFT JOIN (SELECT agreement_id, `value` email FROM client_contacts WHERE main = 1 and type = 'EMAIL') em on em.agreement_id = s.id 
    
               WHERE $WHERE
                ORDER BY s.id desc 
        ");
        $psth->execute($params);
        $finded = $psth->fetchAll();
        $pagination = new Pagination($finded, $this->params['records_per_page']);
        return $this->respondWithData($pagination->getPage($this->params['page_no']), [
            'pagination' => [
                'page_no' => (int) $this->params['page_no'],
                'records_per_page' => (int) $this->params['records_per_page'],
                'total_pages' => $pagination->getTotalPages(),
                'total_records' => $pagination->getTotalRecords(),
            ]
    ]);
    }
    function __construct(LoggerInterface $logger)
    {
        $this->sql = dbConnPDO();
        parent::__construct($logger);
    }

}