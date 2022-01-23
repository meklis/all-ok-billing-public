<?php
namespace envPHP\payments;


/**
 * Class TwoClick
 * @package payments
 * @version 2.30
 * @description ПРОТОКОЛ пакетного обмена данными c поставщиком услуг и системой 2click
 * @author Max Boyar(max.boyar.a@gmail.com)
 * @date 2018-02-16
 */

class TwoClick {
    protected $callbacks = [];
    protected $secret;
    protected $checkSign = false;
    protected $requestSign = "";
    function __construct($secret = "")
    {
        if($secret) {
            $this->secret = $secret;
            $this->checkSign = true;
        }
    }
    protected function setCallBack($name,  $function) {
        $allowNames = ['CHECK', 'PAYMENT', 'STATUS'];
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
    function setStatus($function) {
        $this->setCallBack('STATUS',$function);
        return $this;
    }
    protected function XmlToArray($inXmlset){
        $resource    =    xml_parser_create();
        xml_parse_into_struct($resource, $inXmlset, $outArray);
        xml_parser_free($resource);
        return $outArray;
    }
    protected function getValueByTag($xmlArray,$needle){
        foreach ($xmlArray as $i){
            if($i['tag']==strtoupper($needle))
                return @$i['value'];
        }
        throw  new \InvalidArgumentException("Tag $needle not found in XML");
    }
    protected function datetime($time = ""){
        $unix = time();
        if($time) {
            $unix = strtotime($time);
        }
        return date("d.m.Y H:i:s", $unix);
    }
    function createOperation($inXmlset){ //
        $xmlArr=$this->XmlToArray($inXmlset);
        $this->requestSign = $this->getValueByTag($xmlArr, 'sign');
        try {
            $ACT = $this->getValueByTag($xmlArr, 'ACT');
                switch ($ACT) {
                    case '1':
                        return $this->Check($this->getValueByTag($xmlArr, 'service_id'),
                            $this->getValueByTag($xmlArr, 'pay_account'),
                            $this->getValueByTag($xmlArr, 'pay_id'),
                            $this->getValueByTag($xmlArr, 'trade_point')
                        );
                    case '4':
                        return $this->Payment($this->getValueByTag($xmlArr, 'service_id'),
                            $this->getValueByTag($xmlArr, 'pay_account'),
                            $this->getValueByTag($xmlArr, 'pay_amount'),
                            $this->getValueByTag($xmlArr, 'receipt_num'),
                            $this->getValueByTag($xmlArr, 'pay_id'),
                            $this->getValueByTag($xmlArr, 'trade_point')
                        );
                    case '7':
                        return $this->Status($this->getValueByTag($xmlArr, 'service_id'),
                            $this->getValueByTag($xmlArr, 'pay_id')
                        );
                }
        } catch (\Exception $e) {
            $this->sendResponse(-100);
        }
        $this->sendResponse(-101);
    }
    protected function checkSign() {
        $sign = "";
        foreach (func_get_args() as $arg) {
            $sign .= trim($arg) . "_";
        }
        $sign = trim($sign, "_");
        if($this->requestSign == strtoupper(sha1($sign))) {
            return true;
        } else {
            return false;
        }
    }
    protected function sendResponse($status_code,  $params = []){
        $resp = "<pay-response>
    <status_code>$status_code</status_code> 
    <time_stamp>".$this->datetime()."</time_stamp>\n";
        foreach($params as $key=>$param) {
            $resp .= "  <{$key}>$param</{$key}>";
        }
    return $resp."\n</pay-response>";
    }
    protected function Payment($service_id,$account,$amount,$receiptNum, $payId, $terminal){
        if(!$func = @$this->callbacks['PAYMENT']) throw new \Exception("Method payment not defined");
        $param = [];
        try {
            if ($this->checkSign && !$this->checkSign(4,$account,$service_id,strtoupper($payId),sprintf("%.2f",$amount),$this->secret)) {
                return $this->sendResponse('-101');
            }
            call_user_func($func,$service_id,$account,$amount,$receiptNum, $payId, $terminal);
            $status_code = 22;
            $param['pay_id'] = trim($payId);
            $param['service_id'] = trim($service_id);
            $param['amount'] = trim($amount);
        } catch (\Exception $e) {
            $status_code = $e->getCode() == 0?-90:$e->getCode();
        }
       return $this->sendResponse($status_code ,$param);
    }
    protected function Check($service_id,$account,$payId,$terminal)
    {
        if(!$func = @$this->callbacks['CHECK']) throw new \Exception("Method check not defined");
        $params = [];
        try {
            if ($this->checkSign && !$this->checkSign(1,$account,$service_id,strtoupper($payId),$this->secret)) {
                return $this->sendResponse('-101');
            }
            $data = call_user_func($func, $service_id,$account,$payId,$terminal);
            $status_code = 21;
            $params['balance'] = $data['Balance'];
            $params['name'] = $data['Name'];
            $params['account'] = trim($account);
            $params['service_id'] = trim($service_id);
        } catch (\Exception $e) {
            $status_code = $e->getCode() == 0?-90:$e->getCode();
        }
         return $this->sendResponse($status_code ,$params);
    }
    protected function Status($service_id,$payId) {
        if(!$func = @$this->callbacks['STATUS']) throw new \Exception("Method STATUS not defined");
        $params = [];

        try {
            $data = call_user_func($func, $service_id, $payId);
            if ($this->checkSign && !$this->checkSign(7,"",$service_id,strtoupper($payId),$this->secret)) {
                return $this->sendResponse('-101');
            }
            $amount = trim($data['amount']);
            $status_code = 11;
            $params['transaction'] = "
            <pay_id>{$payId}</pay_id>
            <service_id>{$service_id}</service_id>
            <amount>{$amount}</amount>
            <status>111</status>
            <time_stamp>{$this->datetime($data['time'])}</time_stamp>
            ";
        } catch (\Exception $e) {
            $status_code = $e->getCode() == 0?-90:$e->getCode();
        }
        return $this->sendResponse($status_code ,$params);
    }
}


