<?php

use envPHP\classes\ComposerFileReader;

require_once __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

//Initialize event system
(new \envPHP\EventSystem\EventRepository())->attachAllDirectoryEvents();


function Env()
{
    return $_ENV;
}

$configPath = '/www/configs';
if(isset(Env()['CONFIG_DIR'])) {
  $configPath = Env()['CONFIG_DIR'];
}

function extraConf($name)
{
    global $configPath;
    if (file_exists(  "{$configPath}/extra/" . $name . ".php")) {
        return require "{$configPath}/extra/" . $name . ".php";
    }
    return false;
}

$CONFIGURATION =  require "{$configPath}/global.conf.php";
$composerData = new ComposerFileReader(__DIR__ . '/../composer.json');
$CONFIGURATION['VERSION'] = $composerData->getVersion();
$CONFIGURATION['PROJECT_NAME'] = $composerData->getProjectName();

\envPHP\Wildcore\ClientInitializer::init();

function getGlobalConfigVar($name) {
    if(!is_string($name) && !is_int($name)) {
       die("Incorrect type for get global parameter");
    }
    global $CONFIGURATION;
    if(isset($CONFIGURATION[$name])) {
        return $CONFIGURATION[$name];
    }
    return false;
}

function conf($property) {
    global $CONFIGURATION;
    $elements = explode(".", $property);
    $arrayKey = join('', array_map(function ($e) {
        return "['{$e}']";
    }, $elements));
    $return = null;
    $evalArrayBlock = "if(isset(\$CONFIGURATION{$arrayKey})) {\$return = \$CONFIGURATION{$arrayKey}; }";
    eval($evalArrayBlock);
    return $return;
}

\envPHP\classes\Logger::init(getGlobalConfigVar('LOGGER'));

//Initialize cache
$memConf = getGlobalConfigVar('MEMCACHE');
\envPHP\service\Cache::init($memConf['host'], $memConf['port']);


mb_regex_encoding('utf-8');
//Подключение к базе
$MYSQL_CONNECTION_POOL = [];
function dbConn($database = '') {
    $DATABASE = getGlobalConfigVar('DATABASE');
    global $MYSQL_CONNECTION_POOL;
    if(!$database) $db = $DATABASE['db']['use']; else $db = $database;
    if(isset($MYSQL_CONNECTION_POOL[$db])) {
        return $MYSQL_CONNECTION_POOL[$db];
    }
    $sql = new mysqli($DATABASE['db']['host'], $DATABASE['db']['login'], $DATABASE['db']['pass'], $db);
    $sql->set_charset("utf8");
    $sql->query("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    $MYSQL_CONNECTION_POOL[$db] = $sql;
    return $sql;
}

function dbConnPDO() {
    $DATABASE = getGlobalConfigVar('DATABASE');
    global $MYSQL_CONNECTION_POOL;
    $db = $DATABASE['db']['use'];
    if(isset($MYSQL_CONNECTION_POOL["PDO_" . $db])) {
        return $MYSQL_CONNECTION_POOL["PDO_" . $db];
    }

    $sql = new PDO(sprintf("mysql:host=%s;dbname=%s;charset=utf8", $DATABASE['db']['host'], $db), $DATABASE['db']['login'], $DATABASE['db']['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_PERSISTENT    => false,
    ]);
    $sql->exec("SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
    $MYSQL_CONNECTION_POOL["PDO_" . $db] = $sql;
    return $sql;
}

//Преобразование текста
function rus2lat($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',  'ї' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'і'=>'i', 'є'=>'є',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '',  'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'І'=>'I', 'Є' => 'E',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    ); //Шановний абонент, на рахунку ".$a['agreement']." зал." .$a['balance'].", опл. до $d. Для подальшого користування необхідно поповнити рахунок
    return strtr($string, $converter);
}

//Генерация POST запроса
function sendPost($href, $data) {
    $postdata = http_build_query($data);
    $opts = array('http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        )
    );
    $context  = stream_context_create($opts);
    $result = file_get_contents($href, false, $context);
    return $result;
}
//Первый и последний IP по маске
function cidrToRange($cidr) {
    $range = array();
    $cidr = explode('/', $cidr);
    $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
    $range[1] = long2ip((ip2long($cidr[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
    return $range;
}

/**
 * @param $length
 * @return string
 */
function randomPassword($length) {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}


function url_array_encode(array $arr) {
    $url = "";
    foreach ($arr as $k=>$v) {
        if(is_array($v)) {
            if(count($v) == 0) continue;
            $k .= '[]';
            $v =  join(',', $v);
        }
        $url .= "&{$k}=".urlencode($v);
    }
    return trim($url, "&");
}

