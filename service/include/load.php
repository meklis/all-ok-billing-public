<?php
//xdebug_enable();
define('_home',$_SERVER['DOCUMENT_ROOT']);
session_start();
require_once __DIR__ . "/../../envPHP/load.php";
require(_home . "/include/classes/db.php");
require(_home . "/include/classes/htmlBuilder.php");
require(_home . "/include/classes/access.php");


function init($disableHTML = false)
{ global $sql ,$html,$rank, $auth;
   header('Content-Type: text/html; charset=utf-8');
$sql = new sql();
$auth = new Auth($sql);
if(!$auth->CheckAuth()) {echo "<h1>Не авторизован.</h1>"; exit;}
$u = $_SESSION['user'];
$cache = md5($_SERVER['REMOTE_ADDR'].$u['id']);
\envPHP\classes\memory::set($cache, true, 360);

define('_uid', $u['id']);
define('_uname', $u['name']);
define('_urank', $u['rank']);
define('_uposition', $u['position']);

envPHP\EventSystem\EventRepository::getSelf()->notify('service:page_request', [
   'server' => [
     'SCRIPT_URL' => isset($_SERVER['SCRIPT_URL'])  ? $_SERVER['SCRIPT_URL'] : $_SERVER['SCRIPT_FILENAME'],
     'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
     'HTTP_USER_AGENT' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
   ],
   'user' => $u,
   'request' => $_REQUEST,
]);

if(!$disableHTML) $html = new html();

\envPHP\service\PSC::init($u['group_id']);

}
function tpl($name, $arr = array()) {
    $data = require _home . "/include/blocks/".$name.".php";

    foreach ($arr as $k=>$v) {

        $data = str_replace("#$k#", $v, $data);
    }
    if(isset($_SESSION['user']))
    foreach ($_SESSION['user'] as $k=>$v) {
        $data = str_replace("#U:{$k}#", $v, $data);
    }
    return $data;
}

if(!isset($_SESSION['LAST_PAGE'])) {
    $_SESSION['LAST_PAGE'] = "";
}

function lastPage() {
    $pageHist = [];
    if(isset($_SESSION['PAGE_HIST'])) {
        $pageHist = $_SESSION['PAGE_HIST'];
    }
    array_unshift($pageHist, $_SERVER['REQUEST_URI']);
    while(count($pageHist) > 100) {
        array_pop($pageHist);
    }
    $lastPage = $_SERVER['REQUEST_URI'];
    foreach ($pageHist as $page) {
        if($page != $lastPage) {
            $lastPage = $page;
            break;
        }
    }
    $_SESSION['PAGE_HIST'] = $pageHist;
    $_SESSION['LAST_PAGE'] = $lastPage;
    return $lastPage;
}
lastPage();
function pageNotPermittedAction()   {
    global $html;
    $lastPage = lastPage();
    $html->addBackendNoty('error', 'Недостаточно прав для доступа к странице ' . $_SERVER['REQUEST_URI']);
    header('Location: ' . $lastPage);
    exit;
}

/**
 * @return html
 */
function html() {
    global $html;
    return $html;
}