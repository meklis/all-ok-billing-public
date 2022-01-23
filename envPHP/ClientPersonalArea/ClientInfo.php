<?php


namespace envPHP\ClientPersonalArea;


use envPHP\service\BillingDisableDay;

class ClientInfo extends AbstractClientPersonalArea
{
    protected $client_id;
    function __construct($client_id)
    {
        $this->client_id = $client_id;
    }

    /**
     * Возвращает ID клиента
     * @return mixed
     */
    function getClientId() {
        return $this->client_id;
    }

    /**
     * Возвращает договора, найденные по этой же квартире и подьезду
     *
     * @return array
     * @throws \Exception
     */
    function getNeighborAgreements() {
        $clInf = $this->getGeneralInfo();
        $sth = $this->getConnection()->prepare("SELECT cl.id, cl.agreement, cl.`name`, GROUP_CONCAT(p.`name`) prices 
        FROM clients cl 
        LEFT JOIN client_prices pr on pr.agreement = cl.id and time_stop is null 
        LEFT JOIN bill_prices p on p.id = pr.price 
        WHERE cl.apartment = :apartment and house = :house_id and cl.id != :client_id
        GROUP BY cl.agreement");
        if(!$sth->execute([
            ':client_id'=>$this->client_id,
            ':apartment'=>$clInf['apartment'],
            ':house_id'=>$clInf['house_id'],
        ])) {
            throw new \Exception("SQL ERR: " . join(', ', $sth->errorInfo()));
        };
        return $sth->fetchAll();
    }

    /**
     * Возвращает информацию о пользователе, поля:
     * name, provider_id, house_id, id, agreement, full_addr, entrance, floor, apartment, phone, email, balance, status, notice_mail, notice_sms, enable_credit_period
     *
     * @return mixed
     * @throws \Exception
     */
    function getGeneralInfo() {
        $sth = $this->getConnection()->prepare("SELECT 
       c.name, 
       c.provider provider_id, 
       c.house house_id, 
       c.id, 
       c.agreement, 
       a.full_addr, 
       c.entrance, 
       c.floor, 
       c.apartment, 
       ph.phone, 
       em.email, 
       c.balance, 
       c.`status`, 
       c.notice_mail, 
       c.notice_sms, 
       c.enable_credit_period
FROM clients c 
JOIN addr a on a.id = c.house
LEFT JOIN (SELECT agreement_id, `value` phone FROM client_contacts WHERE main = 1 and type = 'PHONE') ph on ph.agreement_id = c.id 
LEFT JOIN (SELECT agreement_id, `value` email FROM client_contacts WHERE main = 1 and type = 'EMAIL') em on em.agreement_id = c.id 
WHERE c.id = :id");
        if(!$sth->execute([':id'=>$this->client_id])) {
            throw new \Exception("SQL ERR: " . join(', ', $sth->errorInfo()));
        };
        return $sth->fetch();
    }

    /**
     * Возвращает дату - оплачено до
     *
     * @return mixed
     * @throws \Exception
     */
    function getPayedTo() {
       return BillingDisableDay::getByAgreement($this->client_id);
    }

    /**
     * Возвращает привязки (замороженные и активные), поля:
     * id, price(имя), switch, port, ip, mac, status
     *
     * @return array
     * @throws \Exception
     */
    function getBindings() {
        $sth = $this->getConnection()->prepare("SELECT 
 b.id,
 pr.name price,
 eq.ip switch, 
 b.port, 
 b.ip, 
 b.mac,
 if(p.time_stop is null, 'ACTIVATED', 'FROSTED') `status`
FROM eq_bindings b
JOIN client_prices p on p.id = b.activation
JOIN bill_prices pr on pr.id = p.price
JOIN equipment eq on eq.id = b.switch
WHERE agreement = :id");
        if(!$sth->execute([':id'=>$this->client_id])) {
            throw new \Exception("SQL ERR: " . join(', ', $sth->errorInfo()));
        };
        return $sth->fetchAll();
    }
    function getTrinityBindings() {
        $sth = $this->getConnection()->prepare("SELECT b.id, b.created, b.mac, b.uuid, b.local_playlist_id, bp.name price_name, bp.id price_id,  if(p.time_stop is null, 'ACTIVATED', 'FROSTED') `status`
FROM trinity_bindings b 
JOIN client_prices p on p.id = b.activation
JOIN bill_prices bp on bp.id = p.price
WHERE p.agreement = :id
ORDER BY 2 desc 
");
        if(!$sth->execute([':id'=>$this->client_id])) {
            throw new \Exception("SQL ERR: " . join(', ', $sth->errorInfo()));
        };
        return $sth->fetchAll();
    }

    /**
     * Возвращает список услуг (активных и приостановленных), массив
     * []{id, time_start, name, price_day, binding, status, allow_actions[deactivate,frost,activate], work_type[no_action,inet,question,trinity]}
     *
     * @return array
     * @throws \Exception
     */
    function getServices() {
        $sth = $this->getConnection()->prepare("SELECT DISTINCT 
                p.id, 
                cast(p.time_start as date) time_start, 
                b.`name`,
                b.price_day, 
                b.price_month,
                b.recalc_time,
                IF(eb.activation is not null or tb.activation is not null, '*', '') binding, 
                if(time_stop is null, 'ACTIVATED', 'FROSTED') status,
				if(time_stop is null, if(eb.activation is not null or tb.activation is not null, 'deactivate, frost','deactivate'), 'activate') allow_actions,
                b.work_type,
                p.disable_day,
                p.disable_day_static
FROM client_prices p
JOIN bill_prices b on b.id  = p.price
LEFT JOIN (SELECT DISTINCT activation FROM eq_bindings) eb on eb.activation = p.id
LEFT JOIN (SELECT DISTINCT activation FROM trinity_bindings) tb on tb.activation = p.id
WHERE (time_stop is null or eb.activation is not null) and agreement =  :id order by id desc");
        if(!$sth->execute([':id'=>$this->client_id])) {
            throw new \Exception("SQL ERR: " . join(', ', $sth->errorInfo()));
        };
        return $sth->fetchAll();
    }

    /**
     * Возвращает список платежей, массив
     * []{money, time, comment}
     *
     * @return array
     * @throws \Exception
     */
    function getPayments() {
        $sth = $this->getConnection()->prepare("SELECT money, cast(time as date) time, comment FROM paymants WHERE agreement = :id order by time desc");
        if(!$sth->execute([':id'=>$this->client_id])) {
            throw new \Exception("SQL ERR: " . join(', ', $sth->errorInfo()));
        };
        return $sth->fetchAll();
    }

    /**
     * Возвращает список заявок
     * []{id, dest_time(время, на которое оформлена заявка), phone, reason, report_status, report_id, completion_report_status[SUBSCRIBED,NOT_SUBSCRIBED,NOT_EXISTS]}
     *
     * @return array
     * @throws \Exception
     */
    function getQuestions() {
        $sth = $this->getConnection()->prepare("SELECT id,dest_time,
            phone,
            reason,
            report_status,
            report_id,
            if(cert_of_completion is not null, if(cert_subscribed, 'SUBSCRIBED', 'NOT_SUBSCRIBED'), 'NOT_EXISTS') completion_report_status
            FROM questions_full
            WHERE agreement = :id
            ORDER BY dest_time desc");
        if(!$sth->execute([':id'=>$this->client_id])) {
            throw new \Exception("SQL ERR: " . join(', ', $sth->errorInfo()));
        };
        return $sth->fetchAll();
    }

    /**
     * Возвращает информацию о том, включен ли кредитный период
     *
     * @return bool
     */
    function isCreditEnabled() {
        $sth = $this->getConnection()->prepare("SELECT amount FROM client_credit WHERE client_id = ':id'  and `status` = 'OPEN'");
         $sth->execute([':id'=>$this->client_id]);
        if($sth->rowCount() != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Возвращает сумму начисленного кредита, если он открыт
     *
     * @return float|mixed
     */
    function getCreditAmount() {
        $sth = $this->getConnection()->prepare("SELECT amount FROM client_credit WHERE client_id = :id  and `status` = 'OPEN' ");
        $sth->execute([':id'=>$this->client_id]);
        $amount = $sth->fetch();
        if(!$amount) $amount = 0.00;
        return $amount;
      //  SELECT amount FROM client_credit WHERE client_id = 8  and `status` = 'OPEN'
    }

    /**
     * Возвращает список прайсов, которые могут быть у абонента, массив
     * []{id, name, price_day, price_month, speed, work_type}
     * @return array
     * @throws \Exception
     */
    public function getPriceList() {
        $provider = $this->getGeneralInfo()['provider_id'];
        return $this->getConnection()->query("SELECT id, `name`, price_day, price_month, speed, work_type
FROM bill_prices p WHERE `show` = 1 and price_day != 0
ORDER BY 2")->fetchAll();
    }

    /**
     * Возвращает информацию, можно ли производить регистрацию еще одного устройства
     *
     * @param $activationId
     * @return bool
     */
    public function isTrinityRegAllowed($activationId) {
        $sth = dbConnPDO()->prepare("SELECT id FROM trinity_bindings WHERE activation = :activation");
        $sth->execute([':activation'=>$activationId]);
        if($sth->rowCount() <= getGlobalConfigVar('TRINITY')['max_devices_per_activation']) {
            return true;
        } else {
            return false;
        }
    }
}