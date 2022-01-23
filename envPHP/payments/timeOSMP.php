<?php
namespace envPHP\payments;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 31.08.2017
 * Time: 12:41
 */
class timeOSMP
{
    protected $request = [
        'txn_id'=>0,
        'txn_date'=>'0000-00-00 00:00:00',
        'account'=>0,
        'sum'=>0.00,
        'prv_id'=>0,
        'cancel_txn_id'=>0,
        'command'=>'',
    ];
    protected $callbacks = [];
    function createOperation($request) {
        foreach ($this->request as $k=>$v) {
            if(!isset($request[$k])) $request[$k] = $v;
        }
        if($request['command']) {
            switch ($request['command']) {
                case "check": return $this->check($request['txn_id'],$request['account'],$request['sum'], $request['prv_id']);
                case 'pay': return $this->pay($request['txn_id'],$request['txn_date'],$request['account'],$request['sum'], $request['prv_id']);
                case 'cancel': return $this->cancel($request['txn_id'],$request['txn_date'],$request['cancel_txn_id'],$request['sum']);
                default : return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
    <result>300</result>
    <comment>UNKNOWN COMMAND</comment> 
</response>
";
            }
        } else {
            return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
    <result>300</result>
    <comment>ERROR READING XML</comment> 
</response>
";
        }
    }
    protected function setCallBack($name,  $function) {
        $allowNames = ['CHECK', 'PAYMENT', 'CONFIRM', 'CANCEL'];
        if(!in_array(strtoupper($name),$allowNames)) throw new \Exception("NO SUPPORTED METHOD $name");
        $this->callbacks[strtoupper($name)] = $function;
    }
    function setCheck($function) {
        $this->setCallBack('CHECK',$function);
        return $this;
    }
    function setPayment($function) {
        $this->setCallBack('PAYMENT',$function);
        return $this;
    }
    function setCancel($function) {
        $this->setCallBack('CANCEL',$function);
        return $this;
    }

    protected function pay($transactionOsmp,$transactionDate,$account,$amount, $provider) {
        if(!$func = @$this->callbacks['PAYMENT']) throw new \Exception("Method payment not defined");
        $result = 0;
        $coment = "OK";
        $transactionProvider = 0;
        try {
            $transactionProvider = call_user_func($func,$transactionOsmp, $transactionDate,$account,$amount, $provider);
        } catch (\Exception $e) {
            $result = $e->getCode() == 0?5:$e->getCode();
            $coment = $e->getMessage();
        }
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
    <osmp_txn_id>$transactionOsmp</osmp_txn_id>
    <prv_txn>$transactionProvider</prv_txn>
    <prv_txn_date>".$this->datetime()."</prv_txn_date>
    <sum>$amount</sum>
    <result>$result</result>
    <comment>$coment</comment>
</response>
";
    }
    protected function check($transactionOsmp, $account, $amount, $provider) {
        if(!$func = @$this->callbacks['CHECK']) throw new \Exception("Method check not defined");
        $result = 0;
        $coment = "OK";
        $name = "";
        $balance = "";
        try {
            $data = call_user_func($func,$transactionOsmp,$account,$amount, $provider);
            $name = $data['Name'];
            $balance = $data['Balance'];
        } catch (\Exception $e) {
            $result = $e->getCode() == 0?5:$e->getCode();
            $coment = $e->getMessage();
        }
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
    <osmp_txn_id>$transactionOsmp</osmp_txn_id>  
    <result>$result</result>
    <fields>
        <field1 name='name'>$name</field1>
        <field2 name='balance'>$balance</field2>
    </fields>
    <comment>$coment</comment>
</response> 
";
    }
    protected function cancel($transactionOsmp,$transactionOsmpDate, $cancelTrasaction,$amount) {
        if(!$func = @$this->callbacks['CANCEL']) throw new \Exception("Method cancel not defined");
        $result = 0;
        $coment = "OK";
        $transactionProvider = 0;
        try {
            $transactionProvider = call_user_func($func,$transactionOsmp,$transactionOsmpDate,$cancelTrasaction);
        } catch (\Exception $e) {
            $result = $e->getCode() == 0?5:$e->getCode();
            $coment = $e->getMessage();
        }
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<response>
        <osmp_txn_id>$transactionOsmp</osmp_txn_id>
        <cancel_txn_id>$cancelTrasaction</cancel_txn_id>
        <prv_txn>$transactionProvider</prv_txn>
        <sum>$amount</sum>
        <result>$result</result>
        <comment>$coment</comment>
</response>
";
    }
    protected function datetime(){
        return date('Y-m-d H:i:s', time());}
}