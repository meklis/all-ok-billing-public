<?php


namespace envPHP\ClientPersonalArea;


use envPHP\ClientPersonalArea\Exceptions\CreditNotAllowed;
use envPHP\ClientPersonalArea\Exceptions\DefrostActivationsNotFound;
use envPHP\ClientPersonalArea\Exceptions\DefrostUserHasNegativeBalance;
use envPHP\ClientPersonalArea\Exceptions\FrostActivationsNotFound;
use envPHP\ClientPersonalArea\Exceptions\FrostNotAllowed;
use envPHP\ClientPersonalArea\Exceptions\IncorrectInputData;
use envPHP\ClientPersonalArea\Exceptions\ReachedLimitMaxDevice;
use envPHP\ClientPersonalArea\Exceptions\TrinityIncorrectCode;
use envPHP\libs\LiqPay;
use envPHP\service\activations;
use envPHP\service\creditPeriod;
use envPHP\service\Question;
use envPHP\service\TrinityControl;
use envPHP\structs\Client;

class ClientActions extends AbstractClientPersonalArea
{
    protected $client_id;
    protected $client;
    protected  $employeePersonalArea;
    function __construct(ClientInfo $client)
    {
        $this->client_id = $client->getClientId();
        $this->employeePersonalArea =   getGlobalConfigVar('BASE')['lc_user_id'];
        $this->client = $client;
    }

    /**
     * Создает заявку с типом ЛК
     *
     * @param $phone_number
     * @param $message
     * @return string
     * @throws \Exception
     */
    function createQuestion($phone_number, $message) {
        $stmt = $this->getConnection()->prepare("INSERT INTO questions (agreement, created, phone, reason) 
              VALUES (:id,NOW(),:phone, '9')");
        $stmt->execute([
           ':id' => $this->client_id,
           ':phone' =>$phone_number,
        ]);
        $lastId = $this->getConnection()->lastInsertId();
        if(!$lastId) {
            throw new \Exception("Error create question");
        }
        $sth = $this->getConnection()->prepare("INSERT INTO question_comments (created_at, question, dest_time, `comment`, employee)
VALUES (NOW(), :question_id ,NOW(), :message, '1')");
        $sth->execute([
            ':question_id' => $lastId,
            ':message' => $message,
        ]);
        return $lastId;
    }

    /**
     * Приостанавливает услуги по договору.
     * Если передана конкретная активация в поле $activationId - производит приостановление услуги только по ней
     *
     * @param int $activationId
     * @return array
     * @throws FrostActivationsNotFound
     * @throws FrostNotAllowed
     */
    public function frost($activationId = 0) {
        //Получим список активных активаций с привязками
        $data = dbConn()->query("SELECT pr.id, pr.time_start , if(pr.time_start < NOW() - INTERVAL 1441 MINUTE, 1, 0) allow_frost
			FROM client_prices pr 
			JOIN (SELECT DISTINCT activation FROM eq_bindings) b on b.activation = pr.id
			WHERE time_stop is null and pr.agreement = '{$this->client_id}'");
        if($data->num_rows == 0) {
            throw new FrostActivationsNotFound("Not found activations for frost");
        }
        $frostList = [];
        while ($d = $data->fetch_assoc()) {
            $frostList[] = $d['id'];
            if($d['allow_frost'] <= 0) {
                throw new FrostNotAllowed("Frost not allowed at this time, try later");
            }
        }
        $responses = [];
        $error = null;
        foreach ($frostList as $id) {
            if($activationId && $activationId != $id) continue;
            try {
                activations::frost($id,  $this->employeePersonalArea, false);
                $responses[] =[
                    'id' => $id,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                $error = $e;
                break;
            }
        }
        if($error) {
            foreach ($responses as $resp) {
                if($resp['status'] === 'success') {
                    activations::defrost($resp['id'], $this->employeePersonalArea, false);
                }
            }
            throw new \Exception($error);
        }
        return $responses;
    }


    protected function hasPositiveBalance() {
        //Проверим, позволяет ли баланс провести разморозку
        $creditAmount = $this->client->getCreditAmount();
        $userBalance = $this->client->getGeneralInfo()['balance'];

        if(($userBalance + $creditAmount) <= 0) {
                return false;
        }
        return true;
    }


    /**
     *  Восстанавливает работу услуг
     *
     *
     * @return array
     * @throws DefrostActivationsNotFound
     * @throws DefrostUserHasNegativeBalance
     */
    public function defrost($activationId = 0)  {
        //Проверим, позволяет ли баланс провести разморозку
        if(!$this->hasPositiveBalance()) {
            throw new DefrostUserHasNegativeBalance("User has negative balance with credit summary");
        }

        //Получим список активных приостановленных активаций с привязками
        $data = dbConn()->query("SELECT pr.id, pr.time_start 
									FROM client_prices pr 
									JOIN (SELECT DISTINCT activation FROM eq_bindings) b on b.activation = pr.id
									WHERE time_stop is not null and pr.agreement = '{$this->client_id}'");
        if($data->num_rows == 0) {
            throw new DefrostActivationsNotFound("Not found frosted activations");
        }
        $responses = [];
        $error = null;
        while ($d = $data->fetch_assoc()) {
            if($activationId && $d['id'] != $activationId) continue;
            try {
                $responses[] = activations::defrost($d['id'], $this->employeePersonalArea, false);
            } catch (\Exception $e) {
                $error = $e;
                break;
            }
        }
        if($error) {
            foreach ($responses as $r) {
                activations::frost($r,$this->employeePersonalArea, false );
            }
            throw new \Exception($error);
        }
        return $responses;
    }

    /**
     * Включает кредитный период
     *
     * @return bool
     * @throws CreditNotAllowed
     */
    function enableCreditPeriod() {
        try {
            creditPeriod::enableCreditWithDefrost($this->client_id, $this->employeePersonalArea);
        } catch (\Exception $e) {
            if($e->getCode()) {
                throw new CreditNotAllowed($e->getMessage());
            } else {
                throw new \Exception($e);
            }
        }
        return true;
    }

    /**
     * Проверяет возможность оплаты через liqpay для данного договора
     *
     * @return bool
     * @throws \Exception
     */
    function isPossibleLiqPay() {
        $provider = $this->client->getGeneralInfo()['provider_id'];
        if(isset(getGlobalConfigVar('LIQPAY_ACCESS')[$provider])) {
            return true;
        }
        return false;
    }

    /**
     * Создает платеж для LiqPay
     *
     * @param $amount
     * @return string Форма с кнопкой liqpay для оплаты
     * @throws \Exception
     */
    function createLiqPayOrder($amount) {
        //INSERT INTO paymants_orders(money,agreement,comment,order_id) VALUES ($m, $uid, 'Оплата через LiqPay', CONCAT(agreement, '-', UNIX_TIMESTAMP()));
        $stmt = $this->getConnection()->prepare("INSERT INTO paymants_orders 
                       (money,agreement,comment,order_id) 
                VALUES (:amount, :client_id, 'Оплата через LiqPay', CONCAT(:cid, '-', UNIX_TIMESTAMP()))");
        $stmt->execute([
            ':cid' => $this->client_id,
            ':client_id' => $this->client_id,
            ':amount' =>$amount,
        ]);
        $provider = $this->client->getGeneralInfo()['provider_id'];
        $orderId = $this->getConnection()->query("SELECT order_id FROM paymants_orders WHERE agreement = '{$this->client_id}' order by id desc limit 1")->fetch()['order_id'];
        $liqpay = new LiqPay(getGlobalConfigVar('LIQPAY_ACCESS')[$provider]['id'], getGlobalConfigVar('LIQPAY_ACCESS')[$provider]['private_key']);
        $html = $liqpay->cnb_form(array(
            'version' => 3,
            'action' => 'pay',
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => 'Оплата послуг інтернет, рахунок: ',
            'server_url' =>  getGlobalConfigVar('BASE')['service_addr'] . '/api/liqpay',
            'order_id' => $orderId,
            'customer' => $this->client_id,
            'result_url' => getGlobalConfigVar('BASE')['my_addr'] . '/index.php?page=pay'
        ));
        return $html;
    }

    /**
     * Создает активацию по договору
     *
     * @param $priceId
     * @return string
     * @throws DefrostUserHasNegativeBalance
     */
    public function createActivation($priceId) {
        //Проверим, позволяет ли баланс провести разморозку
        if(!$this->hasPositiveBalance()) {
            throw new DefrostUserHasNegativeBalance("User has negative balance with credit summary");
        }
        return activations::activate($this->client_id, $priceId, $this->employeePersonalArea, false);
    }

    /**
     * Добавляет новое устройство тринити (создает привязку и регистрирует устройство)
     *
     *
     * @param $activationId
     * @param string $code
     * @param string $mac
     * @param string $uuid
     * @return bool|int|mixed
     * @throws ReachedLimitMaxDevice
     * @throws TrinityIncorrectCode
     * @throws IncorrectInputData
     */
    public function addTrinityBinding($activationId, $code = '', $mac = '', $uuid = '') {
        if(!$this->client->isTrinityRegAllowed($activationId)) {
            throw new ReachedLimitMaxDevice("Reached max device for registration per activation");
        }
        // The string, mac device subscriber, 12 characters in uppercase
        $mac = str_replace(array(
            "-",
            ":"
        ), "", strtoupper($mac));

        if($mac && !preg_match('/^([0-9a-fA-F]){12}$/', $mac)) {
            throw new IncorrectInputData("MAC address is incorrect");
        }
        if($code && !preg_match('/^([0-9]){3,12}$/', $code)) {
            throw new IncorrectInputData("Code is incorrect");
        }

        if($code) {
            //Активация по коду
            try {
                $id = TrinityControl::regByCode($activationId, $code, $this->employeePersonalArea);
            } catch (\Exception $e) {
                throw new TrinityIncorrectCode($e->getMessage());
            }
        } else {
            $id = TrinityControl::reg($activationId, $this->employeePersonalArea, $mac, $uuid);
        }
        return $id;
    }

    /**
     * Удаляет привязку устройства тринити
     *
     * @param $bindingId
     * @throws \Exception
     */
    public function deleteTrinityBinding($bindingId) {
        TrinityControl::deregBindById($bindingId);
    }

    /**
     * Деактивирует прайс.
     * Если на прайсе были привязки - они удаляются
     *
     * @param $activationId
     * @throws \Exception
     */
    public function deactivate($activationId) {
        activations::deactivate($activationId, $this->employeePersonalArea);
    }
}