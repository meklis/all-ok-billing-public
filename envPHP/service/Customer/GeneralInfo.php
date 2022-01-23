<?php


namespace envPHP\service\Customer;


use envPHP\service\Exceptions\NotFoundException;
use envPHP\service\ServiceDataWrapper;

class GeneralInfo extends ServiceDataWrapper
{
    protected $editableFields = [
      'client_status' => ['status', 'string'],
      'provider_id' => ['provider', 'int'],
      'main_email' => ['email', 'string'],
      'main_phone' => ['phone', 'string'],
      'notification_email' => ['notice_mail', 'bool'],
      'notification_sms' => ['notice_sms', 'bool'],
      'name' => ['name', 'string'],
      'ack_enabled' => ['enable_credit', 'bool'],
      'credit_period_enabled' => ['enable_credit_period', 'bool'],
      'address_entrance' => ['entrance', 'int'],
      'address_floor' => ['floor', 'int'],
      'address_apartment' => ['apartment', 'int'],
      'address_house_id' => ['house', 'int'],
      'descr' => ['descr', 'string']  ,
    ];
    protected $customerId;
    function __construct($customerId)
    {
        $this->customerId = $customerId;
    }
    function get() {
        $psth = dbConnPDO()->prepare("
                SELECT 
                s.id, 
                s.`status` client_status, 
                gr.id group_id,  
                gr.name group_name, 
                s.provider provider_id, 
                s.balance, 
                s.agreement, 
                if(s.notice_mail = 0, false, true) notification_email,
                if(s.notice_sms = 0, false, true) notification_sms,
                s.add_time created_at, 
                s.name,    
                s.descr,  
                enable_credit as ack_enabled, 
                enable_credit_period as credit_period_enabled,
                a.full_addr address_house,
                s.entrance address_entrance, 
                s.floor address_floor,
                s.apartment address_apartment, 
                s.house address_house_id
                FROM clients s 
                JOIN addr a on a.id = s.house
                LEFT JOIN addr_groups gr on gr.id = a.group_id
                WHERE s.id = ?
        ");
        $psth->execute([$this->customerId]);
        if($psth->rowCount() == 0) {
            throw new NotFoundException("Agreement with id {$this->customerId} not found", 404, 'customer');
        }
        return $this->wrapForResponse($psth->fetchAll()[0]);
    }
    function update($params = []) {
         list($fieldSQL, $arguments) = $this->wrapForUpdate($params);
         $arguments[] = $this->customerId;
        dbConnPDO()->prepare("UPDATE clients SET $fieldSQL WHERE id = ?")->execute($arguments);
        return $this;
    }
}