<?php
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 10.01.2017
 * Time: 16:16
 */
namespace envPHP\classes;

class memory
{
    static private $cache = false;
    static private $params = false;
    static private $host = false;
    static private $port = false;
    private function __construct() {}
    private function __destruct() {
        //if(self::$cache) self::$cache->close();
       // self::msg("Соединение с MemCache закрыто",3);
    }
    static private function open  ()  {
            global $MEMCACHE;
           // $cache = new Memcache;
           // if(self::$host && self::$port) {
            //    $host  = self::$host;
            //    $port  = self::$port;
           // } else {
            //    $host = $MEMCACHE['host'];
             //   $port = $MEMCACHE['port'];
           // }
           // if($cache->connect($host,$port)) {
            //    self::$cache = $cache;
             //   new self;
              //  return true;
            //}
        return false;
    }
    static public function setHost($host,$port) {
       // self::$host = $host;
      //  self::$port = $port;
        return true;
    }
    static public  function set   ($name, $value, $timeout = false)   {
        global $MEMCACHE;
        //if(!self::$cache) {self::open();}
        //if(!self::$cache) {
        //    std::msg("Не удалось открыть memcache",0);
        //    return false;
        //}
        //if(!$timeout) $timeout = $MEMCACHE['timeout'];
        //$result = self::$cache->replace($name, $value, false,  $timeout);
       // if( $result === false )
       // {
       //     self::$cache->set($name, $value, false, $timeout);
        //}

        return true;
    }
    static private function msg   ($msg,$level = 3) {
            std::msg($msg,$level);
    }
    static public  function get   ($name)           {
       // if(!self::$cache) {self::open();}
       // if(!self::$cache) {
       //     self::msg("Не удалось открыть memcache",0);
       //     return false;
       // }
      //  $var_key = @self::$cache->get($name);
      //  if(!empty($var_key))
      //  {
       //    return $var_key;
       // } else {
          //  self::msg("В MemCache не найдена переменная $name");
            return false;
       // }
    }
    static public  function add($ip,$name,$value) {
        //        $data = self::get($ip);
       // $data[$name]=$value;
       // self::set($ip,$data);
        return true;
    }
    static public function remove($ip,$name) {
       // $data = self::get($ip);
       // unset($data[$name]);
       // self::set($ip,$data);
        return true;
    }
    static public function delete($name) {
       // if(!self::$cache) {self::open();}
       // if(!self::$cache) {
        //    std::msg("Не удалось открыть memcache",0);
         //   return false;
       // }
    //   return self::$cache->delete($name);
    }
}

