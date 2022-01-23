<?php
//Данный клас будет записывать переменную user и возвращать по просьбе данные
use envPHP\service\User;

class Auth
{
    /**
     * @var mysqli
     */
    private $sql;
    private $state;
    /**
     * @var User
     */
    protected $user;
    protected $token;

    function __construct($sql)
    {
        $this->sql = $sql;
        $this->debug = array();
        if (!isset($_SESSION)) session_start();
        $this->user = new User();
    }

    function CheckAuth()
    {
        $headers = getallheaders();
        if(isset($headers['X-Auth-Key']) && $this->authOverToken($headers['X-Auth-Key']))  {
            return  $_SESSION['user'];
        }
        $user = false;
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $login = trim($_SERVER['PHP_AUTH_USER']);
            $pass = trim($_SERVER['PHP_AUTH_PW']);
            $user = $this->authFromDB($login, $pass);
        } else if (isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
        }
        if (!$user && !$user['id']) {
             $this->AuthUser();
             return false;
        }
        if(!isset($_SESSION['user_token'])) {
            $_SESSION['user_token'] = $this->user->generateToken($user['id']);
        }
        return $user;
    }

    public function getToken()
    {
        return $_SESSION['user_token'];
    }
    private function authOverToken($token) {
        $test = $this->sql->query("SELECT e.id, name, phone, `rank`, p.`position`, mail, p.id group_id  FROM `employees` e
            JOIN emplo_positions p on p.id = e.`position`
                WHERE e.id in (
                SELECT employee FROM emplo_tokens WHERE token='{$token}'
            )");
        if ($test->num_rows == 0) return false;
        $u = $test->fetch_assoc();
        $_SESSION['user'] = $u;
        return  $u;
    }
    private function authFromDB($login, $pass)
    {
        if ($login == '' || $pass == '') return false;
        $test = $this->sql->query("SELECT e.id, name, phone, `rank`, p.`position`, mail, p.id group_id FROM `employees` e
            JOIN emplo_positions p on p.id = e.`position`
            WHERE e.login = %1 and BINARY `password` = %2;", $login, $pass);
        if ($test->num_rows == 0) return false;
        $u = $test->fetch_assoc();
        $_SESSION['user'] = $u;
        return $u;
    }

    private function AuthUser()
    {//Проверка доменной авторизации #return (string)login
        header('WWW-Authenticate: Basic realm="private:"');
        header('HTTP/1.0 401 Unauthorized');
    }
}
    
