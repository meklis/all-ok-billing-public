<?php


namespace envPHP\service\Customer;


use function GuzzleHttp\Psr7\str;

class ExtraContacts
{
    protected $pdo;
    function __construct()
    {
        $this->pdo = dbConnPDO();
    }
    function search($filters) {
        $query = "SELECT id, agreement_id, type, value, created_at, employee_id
FROM client_contacts 
WHERE 1=1 ";
        $params = [];
        if(isset($filters['value']) && $filters['value']) {
            $query .= " and value = ?";
            $filters['value'] = str_replace(' ', '+', $filters['value']);
            $params[] = $filters['value'];
        }
        if(isset($filters['type']) && $filters['type']) {
            $query .= " and type = ?";
            $params[] = $filters['type'];
        }
        if(isset($filters['id']) && $filters['id']) {
            $query .= " and id = ?";
            $params[] = $filters['id'];
        }
        if(isset($filters['agreement_id']) && $filters['agreement_id']) {
            $query .= " and agreement_id = ?";
            $params[] = $filters['agreement_id'];
        }
        $query .= " ORDER BY id desc";
        return $this->execQuery($query, $params);
    }
    function get($id) {
        $query = "SELECT id, agreement_id, type, value, created_at, employee_id
FROM client_contacts 
WHERE 1=1 and id = ? ";
        $resp = $this->execQuery($query, [$id]);
        if(count($resp) == 0) {
            throw new \Exception("Not found contact with id = $id");
        }
        return $resp[0];
    }
    function update($id, $type, $value, $employee_id = 0) {
        return $this->pdo
            ->prepare("UPDATE client_contacts SET type = ?, value = ?, employee_id = ?, updated_at = NOW()  WHERE id = ? ")
            ->execute([$type, $value, $employee_id, $id]);
    }
    function add($agreement_id, $type, $value, $employee_id = 0) {
        $this->pdo
            ->prepare("INSERT INTO client_contacts (agreement_id, type, value, created_at, updated_at, employee_id) 
                VALUES (?, ?, ?, NOW(), NOW(), ?)
            ")
            ->execute([$agreement_id, $type, $value, $employee_id]);
        return $this->pdo->lastInsertId();
    }
    function delete($id) {
        return $this->pdo
            ->prepare("DELETE FROM client_contacts WHERE id = ? ")
            ->execute([$id]);
    }

    private function execQuery($query, $params = []) {
        if($params) {
            $sth = $this->pdo->prepare($query);
            $sth->execute($params);
        } else {
            $sth = $this->pdo->query($query);
        }
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }
}