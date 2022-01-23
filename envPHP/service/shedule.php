<?php
namespace envPHP\service;

/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 17.08.2017
 * Time: 23:14
 */
class shedule
{
    const SOURCE_AUTOCREDIT_FROST = 18;
    const SOURCE_AUTOCREDIT_DEFROST = 19;
    const SOURCE_PAYMANT_LIQPAY = 23;
    const SOURCE_PINGER_SMS = 24;
    const SOURCE_NOTIFICATION_GENERATOR = 25;
    static function add($generator, $method, $request =[], $startTime = false) {
        $request = addslashes(json_encode($request,  JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK ));
        if(!$startTime) $startTime = date("Y-m-d H:i:s");
        $t = dbConn()->query("INSERT INTO shedule (generator, method, request, start) VALUES ($generator, '$method', '$request', '$startTime')");
        if(!$t) throw new \Exception(__METHOD__ . " -> ".dbConn()->error);
        return dbConn()->insert_id;
    }
    static function get() {
        self::lock();
        $data = dbConn()->query("SELECT id, generator, method, request, created FROM shedule WHERE start <= NOW() and `begin` is null ORDER BY id LIMIT 1")->fetch_assoc();
        if($data === null) {
            self::unlock();
            return false;
        }
        dbConn()->query("UPDATE shedule SET begin = NOW() WHERE id = '{$data['id']}'");
        self::unlock();
        $data['request'] = json_decode($data['request'], true);
        return $data;
    }
    static function update($id, $code, $response) {
        if(!is_string($response)) {
            $response = json_encode($response,  JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK ) ;
        }
        $prep = dbConnPDO()->prepare("UPDATE shedule SET code = ?, response = ?, finished = NOW() WHERE id = ?");
        $prep->execute([$code, $response, $id]);
        return true;
    }
    static protected function lock() {
        dbConn()->query("LOCK TABLES shedule WRITE;");
    }
    static protected function unlock() {
        dbConn()->query("UNLOCK TABLES;");
    }
}
