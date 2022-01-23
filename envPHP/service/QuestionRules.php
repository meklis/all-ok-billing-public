<?php


namespace envPHP\service;


class QuestionRules
{
    protected static $config;
    const NOT_PROCESSED = -1;
    const DISABLED = -2;
    const NOT_FOUND_ACTIVATIONS = 3;
    const MUST_BE_ACTIVATED = 1;
    const MUST_BE_DEACTIVATED = 2;
    const ACTIVATED_EARLY = 4;
    const DEACTIVATED_EARLY = 5;

    protected $agreement;
    protected $question;
    protected $activation;
    protected $message;
    protected $validate_status = 0;
    function __construct($questionId)
    {
        $this->question = dbConn()->query("SELECT id, agreement, created, reason_id, created_employee
FROM questions_full WHERE id = $questionId")->fetch_assoc();
        $this->agreement = $this->question['agreement'];
        $this->activation = dbConn()->query("SELECT 
                    pr.id, pr.agreement, pr.price, 
                    if(time_stop is null, 'activated', 'deactivated') stat,
                    bp.name 
                    FROM client_prices pr 
                    JOIN (SELECT max(id) id, price, agreement FROM client_prices GROUP BY price, agreement) m on m.id = pr.id 
                    JOIN bill_prices bp on bp.id = pr.price 
                    WHERE pr.agreement = '{$this->question['agreement']}' and pr.price in (" . join(',', self::getControlPriceIds() ) . ") ORDER BY id desc LIMIT 1")->fetch_assoc();
    }

    function validate() {
        if(!self::isEnabled()) {
            $this->validate_status = self::DISABLED;
            return self::DISABLED;
        }
         $questionsToProccessing  = array_merge(self::getQuestReasonsToActivate(), self::getQuestReasonsToDeActivate());
        if(!in_array($this->question['reason_id'], $questionsToProccessing)) {
            $this->validate_status = self::NOT_PROCESSED;
            return self::NOT_PROCESSED;
        }
        if(!$this->activation['id']) {
            return self::NOT_FOUND_ACTIVATIONS;
        }
        if($this->activation['stat'] == "activated" && in_array($this->question['reason_id'],self::getQuestReasonsToDeActivate())) {
            $this->validate_status = self::MUST_BE_DEACTIVATED;
        } elseif ($this->activation['stat'] == "deactivated" && in_array($this->question['reason_id'],self::getQuestReasonsToActivate())) {
            $this->validate_status = self::MUST_BE_ACTIVATED;
        } elseif ($this->activation['stat'] == "deactivated" && in_array($this->question['reason_id'],self::getQuestReasonsToDeActivate())) {
           $this->validate_status = self::DEACTIVATED_EARLY;
        } elseif ($this->activation['stat'] == "activated" && in_array($this->question['reason_id'],self::getQuestReasonsToActivate())) {
            if(self::isCheckDuplicatesEnabled()) {
                $this->validate_status = self::ACTIVATED_EARLY;
            } else {
                $this->validate_status = self::MUST_BE_ACTIVATED;
            }
        }
        return $this->validate_status;
    }

    function proccess($employeeId) {
        $status = $this->validate_status;
        if(!$this->validate_status) {
            $status = $this->validate();
        }
        switch ($status) {
            case self::MUST_BE_ACTIVATED:
                activations::activate($this->agreement, $this->activation['price'],$employeeId, false);
                break;
            case self::MUST_BE_DEACTIVATED:
                activations::deactivate($this->activation['id'], $employeeId, false);
                break;
            default:
                return false;
        }

    }
    function getPriceName() {
        if(!$this->validate_status) {
            $this->validate();
        }
        return $this->activation['name'];
    }

    static public function setConfig($conf) {
        self::$config = $conf;
    }
    static public function isEnabled() {
        self::isConfExists();
        return self::$config['enabled'];
    }
    static public function getQuestReasonsToActivate() {
        self::isConfExists();
        return self::$config['question_reasons_to_activate'];
    }
    static public function getQuestReasonsToDeActivate() {
        self::isConfExists();
        return self::$config['question_reasons_to_deactivate'];
    }
    static public function getControlPriceIds() {
        self::isConfExists();
        return self::$config['control_price_ids'];
    }
    static public function isCheckDuplicatesEnabled() {
        self::isConfExists();
        return self::$config['check_duplicates'];
    }
    protected static function isConfExists() {
        if(!self::$config) {
            throw new \Exception("Configuration not loaded");
        }
    }
}