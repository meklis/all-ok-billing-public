<?php

use envPHP\classes\std;
use envPHP\ClientPersonalArea\ClientActions;
use envPHP\ClientPersonalArea\ClientInfo;
use envPHP\ClientPersonalArea\Exceptions\DefrostUserHasNegativeBalance;
use envPHP\ClientPersonalArea\Exceptions\FrostActivationsNotFound;
use envPHP\ClientPersonalArea\Exceptions\FrostNotAllowed;

session_start();
require_once __DIR__ . "/../envPHP/load.php";

$auth = new envPHP\ClientPersonalArea\Auth();
$CONF = getGlobalConfigVar('PERSONAL_AREA');
$USER_ID = 0;
//Проверка авторизации
if (isset($_SESSION['agreement']) && isset($_SESSION['pass'])) {
    $USER_ID = $auth->auth($_SESSION['agreement'], $_SESSION['pass']);
    if ($USER_ID <= 0) {
        header('Location: autorize.php?error=true');
        exit;
    }
    $_SESSION['uid'] = $USER_ID;
} elseif (isset($_SESSION['uid'])) {
    $USER_ID = $_SESSION['uid'];
} else {
    header('Location: autorize.php');
    exit;
};

$form = [
    'act' => '',
    'old_pwd' => '',
    'new_pwd' => '',
    'new_pwd_confirm' => '',
    'contact' => '',
    'message' => '',
    'page' => '',
    'price_id' => 0,
    'activation_id' => 0,
];
std::Request($form);


foreach ($form as $k=>$v) {
    $_SESSION['form'][$k] = $v;
}

//Initialize client
$client = new ClientInfo($USER_ID);
$actions = new ClientActions($client);

$setResponse = function ($status, $message, $action = '') use ($form) {
    if (!$action) $action = $form['act'];
    $_SESSION['action_response'] = [
        'action' => $action,
        'status' => $status,
        'message' => $message,
    ];
};

$page = '';

if($form['page']) {
    $page = $form['page'];
}

try {
    switch ($form['act']) {
        case  'change_pwd' :
            $page = 'change_pwd';
            if ($form['old_pwd'] == '' || $form['new_pwd'] == '' || $form['new_pwd_confirm'] == '') {
                throw new \InvalidArgumentException("{{NO_REQUIRED_FIELDS}}", -1);
            } elseif ($form['new_pwd'] !== $form['new_pwd_confirm']) {
                throw new \InvalidArgumentException("{{PASSWORDS_NOT_MATCH}}");
            } elseif ($auth->auth($client->getGeneralInfo()['agreement'], $form['old_pwd']) < 0) {
                throw new \InvalidArgumentException("{{INCORRECT_OLD_PASSWORD}}", -1);
            } else {
                $auth->changePasswd($client->getClientId(), $form['new_pwd']);
            }
            $setResponse('success', '{{PASSWORD_SUCCESS_CHANGED}}');
            break;
        case 'mail':
            $page = 'mail';
            $questionId = $actions->createQuestion($form['contact'], $form['message']);
            $setResponse('success', '{{QUESTION_SUCCESS_CREATED}}');
            break;
        case 'frost':
            $page = 'settings';
            try {
                $actions->frost();
            } catch (FrostActivationsNotFound $e) {
                throw new \Exception('{{FROST_ACTIVATION_NOT_FOUND}}', -1);
            } catch (FrostNotAllowed $e) {
                throw new \Exception('{{FROST_ACTIVATION_NOT_ALLOWED}}', -1);
            }
            $setResponse('success', '{{FROST_SUCCESS}}');
            break;
        case 'defrost':
            $page = 'settings';
            try {
                $actions->defrost();
            } catch (\envPHP\ClientPersonalArea\Exceptions\DefrostActivationsNotFound $e) {
                throw new \Exception('{{DEFROST_ACTIVATION_NOT_FOUND}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\DefrostUserHasNegativeBalance $e) {
                throw new \Exception('{{DEFROST_ACTIVATION_NEGATIVE_BALANCE}}', -1);
            }
            $setResponse('success', '{{DEFROST_SUCCESS}}');
            break;
        case 'enable_credit_period':
            $page = 'settings';
            try {
                $actions->enableCreditPeriod();
            } catch (\envPHP\ClientPersonalArea\Exceptions\CreditNotAllowed $e) {
                throw new \Exception('{{NOT_ALLOW_ENABLE_CREDIT_PERIOD}}', -1);
            }
            $setResponse('success', '{{DEFROST_SUCCESS}}');
            break;
        case 'add_price':
            try {
                $actions->createActivation($form['price_id']);
            } catch (\envPHP\ClientPersonalArea\Exceptions\DefrostUserHasNegativeBalance $e) {
                throw new \Exception('{{DEFROST_ACTIVATION_NEGATIVE_BALANCE}}', -1);
            }
            $setResponse('success', '{{SERVICE_SUCCESS_ACTIVATED}}');
            break;
        case 'trinity_add_by_code':
            try {
                $actions->addTrinityBinding($form['activation_id'], $form['code']);
            } catch (\envPHP\ClientPersonalArea\Exceptions\TrinityIncorrectCode $e) {
                throw new \Exception('{{OTT_INCORRECT_CODE}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\ReachedLimitMaxDevice $e) {
                throw new \Exception('{{OTT_DEVICE_REGISTRATION_LIMIT}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\DefrostUserHasNegativeBalance $e) {
                throw new \Exception('{{DEFROST_ACTIVATION_NEGATIVE_BALANCE}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\IncorrectInputData $e) {
                throw new \Exception('{{INCORRECT_INPUT_DATA}}', -1);
            }
            $setResponse('success', '{{OTT_DEVICE_SUCCESS_REGISTERED}}');
            break;
        case 'trinity_reg_by_mac':
            try {
                $actions->addTrinityBinding($form['activation_id'], '', $form['mac'], $form['uuid']);
            } catch (\envPHP\ClientPersonalArea\Exceptions\TrinityIncorrectCode $e) {
                throw new \Exception('{{OTT_INCORRECT_CODE}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\ReachedLimitMaxDevice $e) {
                throw new \Exception('{{OTT_DEVICE_REGISTRATION_LIMIT}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\DefrostUserHasNegativeBalance $e) {
                throw new \Exception('{{DEFROST_ACTIVATION_NEGATIVE_BALANCE}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\IncorrectInputData $e) {
                throw new \Exception('{{INCORRECT_INPUT_DATA}}', -1);
            }
            $setResponse('success', '{{OTT_DEVICE_SUCCESS_REGISTERED}}');
            break;
        case 'trinity_generate_playlist':
            try {
                $actions->addTrinityBinding($form['activation_id']);
            } catch (\envPHP\ClientPersonalArea\Exceptions\TrinityIncorrectCode $e) {
                throw new \Exception('{{OTT_INCORRECT_CODE}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\ReachedLimitMaxDevice $e) {
                throw new \Exception('{{OTT_DEVICE_REGISTRATION_LIMIT}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\DefrostUserHasNegativeBalance $e) {
                throw new \Exception('{{DEFROST_ACTIVATION_NEGATIVE_BALANCE}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\IncorrectInputData $e) {
                throw new \Exception('{{INCORRECT_INPUT_DATA}}', -1);
            }
            $setResponse('success', '{{OTT_DEVICE_SUCCESS_REGISTERED}}');
            break;
        case 'delete_trinity_device':
            try {
                $actions->deleteTrinityBinding($form['id']);
            } catch (\envPHP\ClientPersonalArea\Exceptions\TrinityIncorrectCode $e) {
                throw new \Exception('{{OTT_INCORRECT_CODE}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\ReachedLimitMaxDevice $e) {
                throw new \Exception('{{OTT_DEVICE_REGISTRATION_LIMIT}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\DefrostUserHasNegativeBalance $e) {
                throw new \Exception('{{DEFROST_ACTIVATION_NEGATIVE_BALANCE}}', -1);
            } catch (\envPHP\ClientPersonalArea\Exceptions\IncorrectInputData $e) {
                throw new \Exception('{{INCORRECT_INPUT_DATA}}', -1);
            }
            $setResponse('success', '{{OTT_DEVICE_SUCCESS_DELETED}}');
            break;
    }
    unset($_SESSION['form']);
} catch (Exception $e) {
    if($e->getCode() == -1) {
        $setResponse('error', $e->getMessage());
    } else {
        $setResponse('error', '{{UNKNOWN_ERROR}}');
        $setResponse('error',  $e->getMessage());
    }

}

header("Location: index.php?p=$page");
