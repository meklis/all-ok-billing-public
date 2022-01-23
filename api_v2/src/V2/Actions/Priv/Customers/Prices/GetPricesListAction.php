<?php


namespace Api\V2\Actions\Priv\Customers\Prices;


use Api\V2\Actions\Action;
use envPHP\classes\std;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GetPricesListAction extends Action
{
    protected $cfg;
    protected $params =  [
        'provider' => 0,
        'id' => 0,
        'name' => '',
    ];
    protected $sql;
    protected function action(): Response
    {
        $this->replaceQueryParams($this->params);
        $filter = "1=1 ";
        if($this->params['provider']) {
            $filter .= " and provider = :provider";
        }
        if($this->params['id']) {
            $filter .= " and id = :id";
        }
        if($this->params['name']) {
            $filter .= " and name like :name ";
        }
        $query = "SELECT id, name, price_day, provider, price_month, `show`, work_type FROM bill_prices WHERE $filter ORDER BY name";
        $psth = $this->sql->prepare($query);
        if(!$psth->execute(std::prepareParamsForPDO($this->params))) {
            throw new \Exception("SQL ERR: ". $this->sql->errorInfo()[2]);
        }
        return $this->respondWithData( $psth->fetchAll(\PDO::FETCH_ASSOC));
    }
    function __construct(LoggerInterface $logger)
    {
        $this->sql = dbConnPDO();
        parent::__construct($logger);
    }

}