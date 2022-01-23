<?

namespace envPHP\payments;
class bank24 {

    protected $callbacks = [];
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
    function sendResponse($response){
         return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><commandResponse>$response</commandResponse>";
    }
    function XmlToArray($inXmlset){
        $resource = xml_parser_create();
        xml_parse_into_struct($resource, $inXmlset, $outArray);
        xml_parser_free($resource);
        return $outArray;
    }
    function getValueByTag($xmlArray,$needle) {
        foreach ($xmlArray as $i){
            if($i['tag']==strtoupper($needle))
                return $i['value'];
        }
        return false;
    }
    function parseXML($inXmlset)
    {
        $xmlArr=$this->XmlToArray($inXmlset);
        foreach ($xmlArr as $i)
        {
            $command = $this->getValueByTag($xmlArr,'command');
            switch ($command)
            {
                case 'check' ://Валидация
                    return $this->Check($this->getValueByTag($xmlArr,'login'),
                        $this->getValueByTag($xmlArr,'password'), $this->getValueByTag($xmlArr,'payElementID'),
                        $this->getValueByTag($xmlArr,'transactionID'), $this->getValueByTag($xmlArr,'account'));
                case 'pay'://Оплата
                    return $this->Payment($this->getValueByTag($xmlArr,'login'),
                        $this->getValueByTag($xmlArr,'password'), $this->getValueByTag($xmlArr,'transactionID'),
                        $this->getValueByTag($xmlArr,'payTimestamp'), $this->getValueByTag($xmlArr,'payID'),
                        $this->getValueByTag($xmlArr,'payElementID'), $this->getValueByTag($xmlArr,'account'),
                        $this->getValueByTag($xmlArr,'amount'), $this->getValueByTag($xmlArr,'terminalId'));
                case 'cancel'://Отмена платежа
                    return $this->Cancel($this->getValueByTag($xmlArr,'login'),
                        $this->getValueByTag($xmlArr,'password'), $this->getValueByTag($xmlArr,'transactionID'),
                        $this->getValueByTag($xmlArr,'cancelPayID'), $this->getValueByTag($xmlArr,'payElementID'),
                        $this->getValueByTag($xmlArr,'account'), $this->getValueByTag($xmlArr,'amount'));

            }
        }
    }
    function Check($login, $password, $payElementID, $transactionID, $account){


        if(!$func = @$this->callbacks['CHECK']) throw new \Exception("Method check not defined");
        $fields = "";
        try {
            $data = call_user_func($func,$login, $password,$transactionID,$account,$payElementID);
            $status_code = 0;
            $status_detail='';
            $fields = "<fields>";
            if($data['Name']) $fields .= "<field1 name=\"FIO\">{$data['Name']}</field1>";
            if($data['Balance']) $fields .= "<field2 name=\"balance\">{$data['Balance']}</field2>";
            $fields .= "</fields>";
        } catch (\Exception $e) {
            $status_code = $e->getCode() == 0?5:$e->getCode();
            $status_detail = $e->getMessage();
            $data['id'] = 0;
        }
        return $this->sendResponse("<extTransactionID>{$data['id']}</extTransactionID>
 <account>$account</account>
 <result>$status_code</result>
$fields
 <comment>$status_detail</comment>
         ");
    }
    function Payment($login, $password, $transactionID, $payTimestamp, $payID,$payElementID, $account, $amount, $terminalId)
    {
        if(!$func = @$this->callbacks['PAYMENT']) throw new \Exception("Method payment not defined");
        $id = 0;
        try {
            $id = call_user_func($func,$login, $password,$transactionID,$payTimestamp,$payID,$payElementID,$account,$amount,$terminalId);
            $status_code = 0;
            $status_detail='';
        } catch (\Exception $e) {
            $status_code = $e->getCode() == 0?300:$e->getCode();
            $status_detail = $e->getMessage();
        }
        return $this->sendResponse("<extTransactionID>$id</extTransactionID>
 <account>$account</account>
 <result>$status_code</result>
 <comment>$status_detail</comment>");
    }
    function Cancel($login, $password, $transactionID, $cancelPayID, $payElementID,$account, $amount)
    {
        if(!$func = @$this->callbacks['CANCEL']) throw new \Exception("Method payment not defined");
        $extTransactionID = 0;
        try {
            $extTransactionID = call_user_func($func,$login, $password,$transactionID,$cancelPayID);
            $result = 0;
            $comment='';
        } catch (\Exception $e) {
            $result = $e->getCode() == 0?300:$e->getCode();
            $comment = $e->getMessage();
        }

        return $this->sendResponse("<extTransactionID>$extTransactionID</extTransactionID>
 <account>$account</account>
 <result>$result</result>
 <comment>$comment</comment>");

    }

}




