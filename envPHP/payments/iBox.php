<?php
namespace envPHP\payments;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 31.08.2017
 * Time: 12:41
 */
class iBox
{
    protected $request = [
        'txn_id'=>0, //внутрішній номер платежу в ІС
        'txn_date'=>'',
        'account'=>0, //ідентифікатор клієнта в  інформаційній системі Оператора
        'sum'=>0.00, //сума до зарахування на особовий рахунок клієнта (Сума платежу на користь Оператора)
        'prv_id'=>1, //внутрішній ідентифікатор Оператора в ІС завдовжки до 4 знаків.
        'command'=>'', //запит на перевірку стану клієнта
        'pay_type'=>1, //ідентифікатор Послуги, яка надається Оператором
        'trm_id'=>-1,//id ППП (до 20 знаків)
        'agt_id'=>-1, //id агента (до 20 знаків)
        'sum_from'=>0.00, //сума платежу з дод. комісією
    ];
    protected $callbacks = [];
    function createOperation($request) {
        foreach ($this->request as $k=>$v) {
            if(!isset($request[$k])) $request[$k] = $v;
        }
        if($request['command']) {
            switch ($request['command']) {
                case "check": return $this->check($request['txn_id'],$request['account'],$request['sum'], $request['pay_type'], $request['prv_id'], $request['trm_id'], $request['agt_id']);
                case 'pay': return $this->pay($request['txn_id'],$request['account'],$request['sum'],$request['pay_type'], $request['prv_id'], $request['trm_id'], $request['agt_id'], $request['txn_date']);
                case 'getstatus': return $this->status($request['txn_id']);
                case 'cancel': return $this->cancel($request['txn_id']);
                default : return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
<txn_id>{$request['txn_id']}</txn_id>
<result>300</result>
<comment>UNDEFINED OPERATION</comment>
</response>
";
            }
        } else {
            return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
    <result>300</result>
    <comment>ERROR READING REQUEST</comment> 
</response>
";
        }
    }
    protected function setCallBack($name,  $function) {
        $allowNames = ['CHECK', 'PAYMENT', 'CANCEL', 'GET_STATUS'];
        if(!in_array(strtoupper($name),$allowNames)) throw new \Exception("NO SUPPORTED METHOD $name");
        $this->callbacks[strtoupper($name)] = $function;
    }

    /**
     * @param $function
     * @return $this
     * @throws Exception
     */
    function setCheck($function) {
        $this->setCallBack('CHECK',$function);
        return $this;
    }

    /**
     * @param $function
     * @return $this
     * @throws Exception
     */
    function setPayment($function) {
        $this->setCallBack('PAYMENT',$function);
        return $this;
    }
    function setGetStatus($function) {
        $this->setCallBack('GET_STATUS',$function);
        return $this;
    }
    function setCancel($function) {
        $this->setCallBack('CANCEL',$function);
        return $this;
    }
    //$request['txn_id'],$request['account'],$request['sum'], $request['pay_type'], $request['prv_id'], $request['trm_id'], $request['agt_id']

    protected function pay($transactionId,$account,$amount, $payType, $providerId, $termId, $agentId, $transactionDate) {
        if(!$func = @$this->callbacks['PAYMENT']) throw new \Exception("Method payment not defined");
        $result = 0;
        $coment = "OK";
        $transactionData = [];
        try {
            $transactionData = call_user_func($func,$transactionId,$account,$amount, $payType, $providerId, $termId, $agentId,$transactionDate);
        } catch (\Exception $e) {
            $transactionData = [
              'id' => -1,
              'time' => self::datetime(),
              'amount' => $amount,
            ];
            $result = $e->getCode() == 0?300:$e->getCode();
            $coment = $e->getMessage();
        }
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
    <txn_id>$transactionId</txn_id>
    <prv_txn>{$transactionData['id']}</prv_txn>
    <prv_txn_date>{$transactionData['time']}</prv_txn_date>
    <sum>{$transactionData['amount']}</sum>
    <result>$result</result>
    <comment>$coment</comment>
</response>
";
    }
    protected function check($transactionId,$account,$amount, $payType, $providerId, $termId, $agentId) {

        if(!$func = @$this->callbacks['CHECK']) throw new \Exception("Method check not defined");
        $result = 0;
        $coment = "OK";
        $name = "";
        $balance = 0;
        try {
            $data = call_user_func($func,$transactionId,$account,$amount, $payType, $providerId, $termId, $agentId);
            $name = $data['Name'];
            $balance = $data['Balance'];
        } catch (\Exception $e) {
            $result = $e->getCode() == 0?5:$e->getCode();
            $coment = $e->getMessage();
        }
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
    <txn_id>$transactionId</txn_id>  
    <result>$result</result>
    <fields>
        <field1 name=\"name\">$name</field1>
        <field2 name=\"balance\">$balance</field2>
    </fields>
    <comment>$coment</comment>
</response> 
";
    }
    protected function status($transactionId) {
        if(!$func = @$this->callbacks['GET_STATUS']) throw new \Exception("Method check not defined");
        $result = 0;
        $coment = "OK";
        $name = "";
        $balance = 0;
        try {
            $data = call_user_func($func,$transactionId);
            $name = $data['Name'];
            $balance = $data['Balance'];
        } catch (\Exception $e) {
            $result = $e->getCode() == 0?5:$e->getCode();
            $coment = $e->getMessage();
        }
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
    <txn_id>$transactionId</txn_id>  
    <result>$result</result>
    <fields>
        <field1 name=\"name\">$name</field1>
        <field2 name=\"balance\">$balance</field2>
    </fields>
    <comment>$coment</comment>
</response> 
";
    }
    protected function cancel($transactionId) {
    if(!$func = @$this->callbacks['CANCEL']) throw new \Exception("Method check not defined");
    $result = 0;
    $coment = "OK";
    $name = "";
    $balance = 0;
    try {
        $data = call_user_func($func,$transactionId);
        $name = $data['Name'];
        $balance = $data['Balance'];
    } catch (\Exception $e) {
        $result = $e->getCode() == 0?5:$e->getCode();
        $coment = $e->getMessage();
    }
    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
    <txn_id>$transactionId</txn_id>  
    <result>$result</result>
    <fields>
        <field1 name=\"name\">$name</field1>
        <field2 name=\"balance\">$balance</field2>
    </fields>
    <comment>$coment</comment>
</response> 
    ";
    }
    static function datetime(){
        return date('Y-m-d H:i:s', time());}
}