<?php


namespace Api\V2\Actions\Priv\Equipment\Device;


use Api\Infrastructure\Pagination;
use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\classes\std;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class FindDeviceAction extends Action
{
    protected $params = [
      'id' => 0,
      'ip' => '',
      'mac' => '',
      'house_id' => 0,
      'entrance' => 0,
      'records_per_page' => 50,
      'page_no' => 1,
    ];
    /**
     * @var \PDO
     */
    protected $sql;
    protected function action(): Response
    {
        $this->replaceQueryParams($this->params, true);
        $query = $this->getQuery();
        $psth = $this->sql->prepare($query);
        if(!$psth->execute(std::prepareParamsForPDO($this->params, ['page_no', 'records_per_page']))) {
            throw new \Exception("SQL ERR: ". $this->sql->errorInfo()[2]);
        }
        $dbData = $psth->fetchAll(\PDO::FETCH_ASSOC);
        $pagination = new Pagination($dbData, $this->params['records_per_page']);
        return $this->respondWithData($pagination->getPage($this->params['page_no']), [
            'pagination' => [
                'page_no' => (int) $this->params['page_no'],
                'records_per_page' => (int) $this->params['records_per_page'],
                'total_pages' => $pagination->getTotalPages(),
                'total_records' => $pagination->getTotalRecords(),
            ]
        ]);
    }


    protected function getQuery() {
        $where = ' 1 = 1 ';
        if($this->params['id']) {
            $where .= 'and eq.id = :id ';
        }
        if($this->params['ip']) {
            $where .= 'and eq.ip = :ip ';
        }
        if($this->params['mac']) {
            $where .= 'and eq.mac = :mac ';
        }
        if($this->params['house_id']) {
            $where .= 'and eq.house = :house_id ';
        }
        if($this->params['entrance']) {
            $where .= 'and eq.entrance = :entrance ';
        }

        $query = "SELECT 
gr.`id` `group_id`,
gr.`name` `group_name`,
gr.`description` `group_description`,
eq.id id,
eq.ip, 
eq.mac, 
mo.name model, 
concat('г. ', ci.`name`,', ',st.name , ', д.', ho.name, ', под. ', eq.entrance ) addr,
if(eq.ping > 0, 1, 0) status ,
eq.last_ping 
FROM `equipment` eq
JOIN equipment_models mo on mo.id = eq.model
JOIN equipment_access ac on ac.id = eq.access
JOIN equipment_group gr on gr.id = eq.`group`
JOIN addr_houses ho on ho.id = eq.house
JOIN addr_streets st on st.id = ho.street
JOIN addr_cities ci on ci.id = st.city
WHERE $where 
ORDER BY group_name, status , addr";
        return $query;
    }
    function __construct(LoggerInterface $logger)
    {
        $this->sql = dbConnPDO();
        parent::__construct($logger);
    }

}