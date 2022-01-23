<?php
// if($_SERVER['REMOTE_ADDR'] != '37.57.212.100') {
// echo "<center><h1 align='center'>Уважаемые абоненты!</h1><h3>Личный кабинет временно недоступен,  проводятся технические работы.<br>Приносим свои извинения за предоствленные неудобства. </h3><img src='/uc.png' align='center'></center>";
// exit;
// }
use envPHP\ClientPersonalArea\LangTranslator;

require_once __DIR__ . "/../envPHP/load.php";
session_start();
if (isset($_POST['lc']) && isset($_POST['pwd'])) {
    $_SESSION['agreement'] = trim($_POST['lc']);
    $_SESSION['pass'] = trim($_POST['pwd']);

    //Регистрация перевода
    $lang = 'ua';
    $langTranslate = new LangTranslator(__DIR__ . '/langs');
    if (isset($_REQUEST['lang']) && $langTranslate->isTranslateExists($_REQUEST['lang'])) {
        $lang = $_REQUEST['lang'];
    }
    $_SESSION['lang'] = $lang;
    header('Location: index.php');
    exit;
};

if (isset($_GET['exit'])) {
    unset($_SESSION['agreement']);
    unset($_SESSION['pass']);
    unset($_SESSION['uid']);
}

$config = getGlobalConfigVar('PERSONAL_AREA');
$langForm = '';
if ($config['multi_lang']['enabled']) {
    $langForm = '<SELECT class="form-control" name="lang">';
    foreach ($config['multi_lang']['langs'] as $k => $v) {
        $selected = isset($_SESSION['lang']) && $_SESSION['lang'] === $k ? 'SELECTED' : "";
        $langForm .= "<OPTION value='$k'>$v</OPTION>";
    }
    $langForm .= "</SELECT>";
}


?>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo getGlobalConfigVar('BASE')['provider_name'] ?> - personal area</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="assets/css/all.min.css">
    <!-- IonIcons -->
    <link rel="stylesheet" href="assets/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="assets/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

</head>
<body class="hold-transition login-page" style="background-color:rgb(42, 59, 71);">
<div class="login-box">
    <div class="login-logo">
        <a href="#" style="color:#ffffff;"><?php echo getGlobalConfigVar('BASE')['provider_name'] ?></a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">

            <?php if (isset($_GET['error'])) echo '<div class="alert alert-danger alert-dismissible">Неправильний логін або пароль.</div>';
            if (isset($_SESSION['message']) && $_SESSION['message']) {
                echo '<div class="alert alert-success alert-dismissible">'.$_SESSION['message'].'</div>';
                unset($_SESSION['message']);
            }
            if (isset($_GET['require'])) echo '<font color="#9F0404">' . $_GET['require'] . '</font><br><br>';
            if (empty($_GET['error']) && empty($_GET['require'])) echo '<br> ';
            ?>

            <div id="auth" style="display: block">
                <form action="autorize.php" method='POST'>
                    <div class="input-group mb-3">
                        <input class="form-control" placeholder="Login" id='vvod' type='text' name="lc">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input class="form-control" placeholder="Password" id='vvod1' type='password' name="pwd">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <?= $langForm ?>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-globe"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-warning btn-md btn-block" style="margin-bottom: 10px" type="submit" id="submit">
                                Авторизуватись
                            </button>
                        </div>
                        <div class="col-12" style="margin-bottom: 5px">
                            <a href="/auth_by_phone.php" class="btn btn-warning btn-md btn-block" >
                                Авторизуватись за телефоном
                            </a>
                        </div>
                        <!-- /.col -->
                    </div>
                    <?php if($config['recover_password']['enabled']) { ?>
                    <div class="row">
                        <div class="col-12">
                            <a href="recover_access.php" style="margin-top: 20px;" class="btn btn-default btn-md btn-block" >
                                Відновити пароль
                            </a>
                        </div>
                        <!-- /.col -->
                    </div>
                    <?php } ?>
                </form>
            </div>

        </div>
        <!-- /.login-card-body -->
    </div>
</div>


<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="assets/js/jquery.min.js"></script>
<!-- AdminLTE App -->
<script src="assets/js/adminlte.min.js"></script>
<!-- Input mask library -->
<script src="/assets/inputmask/dist/inputmask.js"></script>
<script src="/assets/inputmask/dist/bindings/inputmask.binding.js"></script>


</body>
</html>


