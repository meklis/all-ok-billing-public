<?php
class sql {
        public $access = FALSE;
        public $accessF = FALSE;
	function __construct() {
		$this->mysqli = dbConn();
		if(!$this->mysqli){$this->error =  $this->mysqli->error; echo "Ошибка подключения к базе данных!"; exit;}
		$this->mysqli->set_charset("utf8");
		$this->mysqli->query("set session sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");
	}
	function __destruct() {
		$this->mysqli->close();
	} 
        function toDate($str) {
            list($d,$m,$y) = explode(".", $str);
            return "$y-$m-$d";
        }
        function multi_query($query) {
            $arr = explode(";", $query);
            foreach ($arr as $q) {
                $this->query($q);
            }
        }
	function query() {
		$mysqli = $this->mysqli;
		$arr = func_get_args();
		$sql = $arr[0];
		$limit ='';
		$order ='';
		unset($arr[0]);
		$string  = array("\n", "'", "\t", "\r\n", "\r", "`");
		foreach($arr as $k=>$v) { 
			if(strpos($v,'LIMIT ') !== FALSE) {$limit = $v; continue;} 
			if(strpos($v,'ORDER BY ')  !== FALSE) {$order = $v; continue;}
			$sql = str_replace("%$k", "'".str_replace($string, '', $v)."'", $sql);
			
		}
                if($this->access) 
                    $sql = str_replace("%access", $this->access, $sql);
                if($this->accessF) 
                    $sql = str_replace("%FA", $this->accessF, $sql);
                $_SESSION['last_query'] = $sql." ".$order." ".$limit;
		if (!$query = $mysqli->query($sql." ".$order." ".$limit)) {
         
			$this->error = $mysqli->error;
                        $sql = $sql." ".$order." ".$limit;
                        $date = date("H:i:s d.m.Y");
                        $error = $this->mysqli->error;
            throw new Exception("SQL ERR: {$this->error}");
		} else return $query;
	}
}
class sybase {
    function __construct($host, $login, $pass, $db) {
        $mdb=mssql_connect($host,$login , $pass);
        mssql_select_db($db,$mdb);
        mssql_query("Set ANSI_NULLS ON;");
        mssql_query("Set ANSI_WARNINGS ON;");
        $this->sql=$mdb;
    }
    function query($query) {
      $mdb = $this->sql;
      $tesw = mssql_query($query , $mdb);
      if(!$tesw) return -1;
      if(mssql_num_rows($tesw) == 0) return 0;
      $i = 0;
      	while ($el = mssql_fetch_assoc($tesw)) {
                  foreach($el as $k=>$v) {
                   $dd[$i][$k] = $v;
                  }
              $i++;
        }
        return $dd; 
    }
    function __destruct() {
        mssql_close ($this->sql);
    }
    

}