<?php

namespace envPHP\payments;


class easypay {
     protected $easySoftPem = "";
     protected $providerPrivateKey = "";
    protected $callbacks = [];
    public $checkSign = false;
    function __construct($easySoftPemPath ="",$providerPrivateKeyPath ="")
    {
        if($easySoftPemPath || $providerPrivateKeyPath) {
            $fp = @fopen($easySoftPemPath, "r");
            $this->easySoftPemPath = @fread($fp, 8192);
            @fclose($fp);
            if (!$this->easySoftPemPath) throw  new \Exception("Error reading easySoftPem cert in path: $easySoftPemPath");

            $fp = @fopen($providerPrivateKeyPath, "r");
            $this->providerPrivateKey = @fread($fp, 8192);
            @fclose($fp);
            if (!$this->providerPrivateKey) throw  new \Exception("Error reading providerPrivateKeyPath cert in path: $providerPrivateKeyPath");
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
    function setConfirm($function) {
        $this->setCallBack('CONFIRM',$function);
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
        return  null;
    }
    protected function datetime($orderDate = ""){
        if($orderDate) return str_replace(" ","T",$orderDate);
        return date('Y-m-d\TH:i:s', time());}

    function createOperation($inXmlset){ //
        $xmlArr=$this->XmlToArray($inXmlset);
        if($this->checkSign) {
            $sign = $this->getValueByTag($xmlArr, 'sign');
            if (!$this->checkSign($inXmlset, $sign)) {
                return $this->sendResponse('-1', 'Sign Error', "");
            }
        }
        foreach ($xmlArr as $k=>$i) {
            switch ($i['tag']) {
                case 'CHECK':
                    return $this->Check($this->getValueByTag($xmlArr, 'SERVICEID'), $this->getValueByTag($xmlArr, 'ACCOUNT'));
                case 'PAYMENT':
                    return $this->Payment($this->getValueByTag($xmlArr, 'SERVICEID'), $this->getValueByTag($xmlArr, 'ACCOUNT'), $this->getValueByTag($xmlArr, 'AMOUNT'), $this->getValueByTag($xmlArr, 'ORDERID'));
                case 'CONFIRM':
                    return $this->Confirm($this->getValueByTag($xmlArr, 'SERVICEID'), $this->getValueByTag($xmlArr, 'PAYMENTID'));
                case 'CANCEL':
                    return $this->Cancel($this->getValueByTag($xmlArr, 'SERVICEID'), $this->getValueByTag($xmlArr, 'PAYMENTID'));
            }
        }
    }
    protected function addSign($xml){
        if(!$this->checkSign) return $xml;
        $pr_key = openssl_get_privatekey($this->providerPrivateKey);
        openssl_sign($xml, $sign, $pr_key);
        $hexsign = bin2hex($sign);
        return str_replace("<Sign></Sign>", "<Sign>".strtoupper($hexsign)."</Sign>", $xml);
    }
    protected function checkSign($xml,$sign){
        if(!$this->checkSign) return true;
        $pub_key = openssl_get_publickey($this->easySoftPemPath);
        $xml = str_replace($sign, '', $xml);
        $bin_sign = pack("H*", $sign);
        return openssl_verify($xml, $bin_sign, $pub_key);
    }
    protected function sendResponse($status_code, $status_detail, $params = ""){
        $response = "<Response>
    <StatusCode>$status_code</StatusCode>
    <StatusDetail>$status_detail</StatusDetail>
    <DateTime>".$this->datetime()."</DateTime>
    <Sign></Sign>
    $params</Response>";
        return $this->addSign($response);
    }
    protected function Payment($service_id,$account,$amount,$order_id){
        if(!$func = @$this->callbacks['PAYMENT']) throw new \Exception("Method payment not defined");
        $param = "";
        try {
            $payment_id = call_user_func($func,$service_id, $account,$amount,$order_id);
            $status_code = 0;
            $status_detail='OK';
            $param = "<PaymentId>$payment_id</PaymentId>\n";
        } catch (\Exception $e) {
            $status_code = $e->getCode() == 0?-1:$e->getCode();
            $status_detail = $e->getMessage();
        }
       return $this->sendResponse($status_code,$status_detail,$param);
    }
    protected function Confirm($service_id,$payment_id){
        if(!$func = @$this->callbacks['CONFIRM']) throw new \Exception("Method confirm not defined");
        $param = "";
        try {
            $orderDate = call_user_func($func,$service_id, $payment_id);
            $status_code = 0;
            $status_detail='OK';
            $param = "<OrderDate>".$this->datetime($orderDate)."</OrderDate>\n";
         } catch (\Exception $e) {
            $status_code = $e->getCode() == 0?-1:$e->getCode();
            $status_detail = $e->getMessage();
         }
       return $this->sendResponse($status_code,$status_detail,$param);
    }
    protected function Check($service_id,$account)
    {
        if(!$func = @$this->callbacks['CHECK']) throw new \Exception("Method check not defined");
        $account_info = "";
        try {
            $data = call_user_func($func, $service_id, $account);
            $status_code = 0;
            $status_detail = 'OK';
            if ($data['Name'] || $data['Balance']) {
                $account_info = "<AccountInfo>\n";
                if($data['Name']) $account_info .= "<Name>{$data['Name']}</Name>\n";
                if($data['Balance']) $account_info .= "<Balance>{$data['Balance']}</Balance>\n";
                $account_info .= "</AccountInfo>";
            }
        } catch (\Exception $e) {
            $status_code = $e->getCode() == 0?-1:$e->getCode();
            $status_detail = $e->getMessage();
        }
         return $this->sendResponse($status_code,$status_detail,$account_info);
    }
    protected function Cancel($service_id,$payment_id) {
        if(!$func = @$this->callbacks['CANCEL']) throw new \Exception("Method CANCEL not defined");
        $info = "";
        try {
            $data = call_user_func($func, $service_id, $payment_id);
            $status_code = 0;
            $status_detail = 'OK';
            $info = "<CancelDate>".$this->datetime($data)."</CancelDate>";
        } catch (\Exception $e) {
            $status_code = $e->getCode() == 0?-1:$e->getCode();
            $status_detail = $e->getMessage();
        }
        return $this->sendResponse($status_code,$status_detail,$info);
    }
}


