<?php


namespace envPHP\structs;


use envPHP\classes\Logger;
use envPHP\service\Cache;

class ClientContact
{
    const PHONE = 'PHONE';
    const EMAIL = 'EMAIL';
    const TELEGRAM = 'TELEGRAM';
    const VIBER = 'VIBER';

    protected $id;
    protected $name;
    /**
     * @var integer
     */
    protected $agreementId;
    /**
     * @var string
     */
    protected $value;
    /**
     * @var self
     */
    protected $type;
    /**
     * @var integer
     */
    protected $employeeId;

    /**
     * @var \PDO
     */
    protected $sql;

    protected $main = false;

    function __construct($type, int $agreementId, string $value, string $name, int $employeeId, bool $main, int $id = 0)
    {
        $this->type = $type;
        $this->agreementId = $agreementId;
        $this->value = $value;
        $this->name = $name;
        $this->employeeId = $employeeId;
        $this->id = $id;
        $this->main = $main;
    }

    /**
     * @param $extraContactId
     * @return ClientContact
     */
    public static function getById(int $extraContactId, $asArray = false)
    {
        $psth = dbConnPDO()->prepare('SELECT id, agreement_id, name, type, value, employee_id, main FROM client_contacts WHERE id = ? ');
        $psth->execute([$extraContactId]);
        if ($psth->rowCount() == 0) {
            throw new \Exception("Not found contact by ID {$extraContactId}");
        }
        $data = $psth->fetch();
        Logger::get()->withName('customer.cc')->info("Found contact by id {$extraContactId}", $data);
        $data['main'] = $data['main'] == 1 ? true : false;
        if ($asArray) return $data;
        return new self(
            $data['type'],
            $data['agreement_id'],
            $data['value'],
            $data['name'],
            $data['employee_id'],
            $data['main'],
            $data['id']
        );
    }

    /**
     * @param int $agreementId
     * @param bool $asArray
     * @return ClientContact[] | array
     */
    public static function getAllContacts(int $agreementId, $asArray = false)
    {
        $psth = dbConnPDO()->prepare('SELECT id, agreement_id, name, type, `value`, employee_id, main FROM client_contacts WHERE agreement_id = ? ');
        $psth->execute([$agreementId]);
        $contacts = [];
        $array = $psth->fetchAll();
        if ($asArray) {
            foreach ($array as $k=>$v) {
                $array[$k]['main'] = $v['main'] == 1 ? true : false;
            }
            return $array;
        }
        foreach ($array as $data) {

            $contacts[] = new self(
                $data['type'],
                $data['agreement_id'],
                $data['value'],
                $data['name'],
                $data['employee_id'],
                $data['main'],
                $data['id']
            );
        }
        return $contacts;
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->main;
    }

    /**
     * @param bool $main
     * @return ClientContact
     */
    public function setMain(bool $main): ClientContact
    {
        $this->main = $main;
        return $this;
    }


    public static function purgeAllContacts(int $agreementId)
    {
        dbConnPDO()->prepare("DELETE FROM client_contacts WHERE agreement_id = ?")->execute([$agreementId]);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function validate()
    {
        $valFunc = null;
        switch ($this->type) {
            case 'TELEGRAM':
            case 'VIBER':
            case 'PHONE':
                $valFunc = function ($value) {
                    $value = preg_replace('/\D/', '', $value);
                    if (strlen($value) == 9) {
                        return "+380" . $value;
                    }
                    if (strlen($value) == 10) {
                        return "+38" . $value;
                    }
                    if (strlen($value) == 11) {
                        return "+3" . $value;
                    }
                    if (strlen($value) == 12) {
                        return "+" . $value;
                    }
                    return '';

                };
                break;
            case 'EMAIL':
                $valFunc = function ($value) {
                    $value = trim($value);
                    $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
                    if (preg_match($pattern, $value)) {
                        return $value;
                    }
                    return '';
                };
                break;
            default:
                throw new \Exception("Invalid type");
        }
        return $valFunc($this->value);
    }

    public function save()
    {
        $val = $this->validate();
        if (!$val) {
            throw new \InvalidArgumentException("Incorrect value for contact with type {$this->type}");
        }
        try {
            while (Cache::instance()->get("AGREEMENT-CONTACT-UPDATE:{$this->agreementId}")) {
                usleep(100000);
            }
            Cache::instance()->set("AGREEMENT-CONTACT-UPDATE:{$this->agreementId}", true, 5);
            $this->value = $val;
            $main = $this->main;
            if (!$main) {
                $psth = dbConnPDO()->prepare("SELECT id FROM client_contacts WHERE type = ? and agreement_id = ? and main = 1 and id != ?");
                $psth->execute([$this->type, $this->agreementId, $this->id]);
                if ($psth->rowCount() == 0) {
                    dbConnPDO()->prepare("UPDATE client_contacts SET main = 0 WHERE type = ? and agreement_id = ?")->execute([$this->type, $this->agreementId]);
                    $main = true;
                }
            } elseif ($main) {
                $psth = dbConnPDO()->prepare("UPDATE client_contacts SET main = 0 WHERE type = ? and agreement_id = ?");
                $psth->execute([$this->type, $this->agreementId]);
            }


            if ($this->id) {
                Logger::get()->withName('customer.cc')->info("Try update contact", (array)$this);
                dbConnPDO()->prepare("UPDATE client_contacts 
            SET updated_at = NOW(), 
                agreement_id = ?,
                name = ?,
                type = ?,
                value = ?,
                employee_id = ?,
                main = ?
             WHERE id = ?")->execute([
                    $this->agreementId,
                    $this->name,
                    $this->type,
                    $this->value,
                    $this->employeeId,
                    $main ? 1 : 0,
                    $this->id,
                ]);
            } else {
                Logger::get()->withName('customer.cc')->info("Try save new contact", (array)$this);
                dbConnPDO()->prepare("INSERT INTO client_contacts 
                (
                     created_at, 
                     updated_at,
                     agreement_id,
                     `name`,
                     `type`,
                     `value`,
                     `employee_id`,
                      `main` 
                 ) VALUES (NOW(),NOW(),?,?,?,?,?, ?)
                 ")->execute([
                    $this->agreementId,
                    $this->name,
                    $this->type,
                    $this->value,
                    $this->employeeId,
                    $main ? 1 : 0
                ]);
                $this->id = dbConnPDO()->lastInsertId();
            }
            Cache::instance()->delete("AGREEMENT-CONTACT-UPDATE:{$this->agreementId}");
        } catch (\Exception $e) {
            Cache::instance()->delete("AGREEMENT-CONTACT-UPDATE:{$this->agreementId}");
            throw new \Exception($e->getMessage());
        }
        return $this;
    }

    public static function delete(ClientContact $contact)
    {
        dbConnPDO()->prepare("DELETE FROM client_contacts WHERE id = ? ")->execute([$contact->getId()]);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return ClientContact
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getAgreementId(): int
    {
        return $this->agreementId;
    }

    /**
     * @param int $agreementId
     * @return ClientContact
     */
    public function setAgreementId(int $agreementId): ClientContact
    {
        $this->agreementId = $agreementId;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return ClientContact
     */
    public function setValue(string $value): ClientContact
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return ClientContact
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return string
     */
    public function setType(string $type): ClientContact
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getEmployeeId(): int
    {
        return $this->employeeId;
    }

    /**
     * @param int $employeeId
     * @return ClientContact
     */
    public function setEmployeeId(int $employeeId): ClientContact
    {
        $this->employeeId = $employeeId;
        return $this;
    }


}