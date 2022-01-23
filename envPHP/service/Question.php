<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 11.05.2019
 * Time: 2:03
 */

namespace envPHP\service;


use envPHP\structs\Client;
use envPHP\structs\Employee;

class Question
{
    public static function create(Client $agreement, $reasonId, Employee $employee, $phone = "", $comment = "", $destTime = "") {
        $destTime = !$destTime ? date("Y-m-d H:i:s") : $destTime;
        if(!$phone) {
            $phone = $agreement->getPhone();
        }
        $test = dbConn()->query("INSERT INTO questions (agreement, created, phone, reason) 
              VALUES ('{$agreement->getId()}',NOW(),'$phone', '$reasonId')");
        $questionId = dbConn()->insert_id;
        if(!$questionId) {
            throw new \Exception("Error create question - ". dbConn()->error);
        }
        $test = dbConn()->query("INSERT INTO question_comments (created_at, question, dest_time, `comment`, employee)
        VALUES (NOW(), '$questionId', '$destTime', '$comment', '{$employee->getId()}')");
        if(!$test) {
            throw new \Exception("Error create question - ". dbConn()->error);
        }
        \envPHP\EventSystem\EventRepository::getSelf()->notify("question:created", [
            'agreement_id' => $agreement->getId(),
            'phone' => $phone,
            'reason_id' => $reasonId,
            'destination_time' => $destTime,
            'id'=> $questionId,
            'responsible_employee_id' => null,
            'employee_id' => $employee->getId(),
            'comment' => $comment,
        ]);
        return $questionId;
    }
}