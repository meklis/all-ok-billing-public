<?php
$data = "";

$BASE = getGlobalConfigVar('BASE');
$display = function ($permission_name, $return) {
    if (\envPHP\service\PSC::isPermitted($permission_name)) {
        return $return;
    }
    return '';
};
$data .= <<<EOL
<!DOCTYPE html>
<html lang="en">
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- uses device width -->
	<meta name="viewport" content="width=device-width" />
	<!-- use of initial-scale means width param is treated as min-width -->
	<meta name="viewport" content="initial-scale=0.85" />
	<title>Service {$BASE['provider_name']} | #title#</title>
	<link href="/res/css/bootstrap.min.css" rel="stylesheet">
	<link href="/res/css/font-awesome.css" rel="stylesheet">
	<link href="/res/css/nprogress.css" rel="stylesheet">

	<link href="/res/css/custom.css?ver=6" rel="stylesheet">
	<link href="/res/css/default.css?ver=6" rel="stylesheet">
    <link rel="stylesheet" href="/res/multiselect/css/bootstrap-multiselect.css" type="text/css">
    <link rel="stylesheet" href="/res/css/bootstrap-datetimepicker.min.css" type="text/css">
	<link href="/res/noty/noty.css" rel="stylesheet">
	<!--<link href="/res/noty/themes/mint.css" rel="stylesheet">-->
	<link href="/res/noty/themes/metroui.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="/res/css/preload.css">
	 
</head>

<body class="nav-md" id="main_body">
<div id="preload">
	<div class="container-preload">
	<!-- 
		<div class="📦"></div>
		<div class="📦"></div>
		<div class="📦"></div>
		<div class="📦"></div>
		<div class="📦"></div>
		-->
		<img src="/res/img/spinner-blue.gif" style="width: 56px">
	</div>
</div>
<script src="/res/js/jquery.min.js"></script>
<script type="text/javascript" src="/res/multiselect/js/bootstrap-multiselect.js"></script>
<script type="text/javascript" src="/res/js/hammer.min.js"></script>
<script type="text/javascript" src="/res/js/jquery.hammer.js"></script>
<script type="text/javascript" src="/res/js/moment-with-locales.min.js"></script>
<script type="text/javascript" src="/res/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" ></script>
<script type="text/javascript" src="/res/js/jquery.form.js" ></script>
 
<script>
$.fn.serializeObject = function()
{
   var o = {};
   var a = this.serializeArray();
   $.each(a, function() {
       if (o[this.name]) {
           if (!o[this.name].push) {
               o[this.name] = [o[this.name]];
           }
           o[this.name].push(this.value || '');
       } else {
           o[this.name] = this.value || '';
       }
   });
   return o;
};

	function enablePreload() {
        $('#preload').show(0);
    }
    function disablePreload() {
		$('#preload').toggle(0);
    }
	$( window).ready(function () {
       disablePreload();
    });
    $( window ).unload(function() {
        enablePreload();
    });

    $(function() {
        $('a').click(function(e){
            console.log(e.delegateTarget.pathname);
             if (e.delegateTarget.pathname !== "" && e.delegateTarget.pathname.indexOf('#') === -1 && e.delegateTarget.pathname.indexOf('/') === -1) {
                 console.log('pathname='+ e.delegateTarget.pathname);
                 console.log('Enable preload');
                enablePreload();
            }
        });
    });
	function savePrevState() {
		window.setTimeout(function () {
			var classes =  $('#main_body').attr('class');
			console.log("Save body classes: "+classes);
			localStorage.navbar_classes = classes;
		}, 500);
	}
	function setNavBarFromPrevState() {
	    var prev =  localStorage.navbar_classes;
	    if($(window).width() < 970) {
	        return true;
            console.log("Width is very small, ignore last state");
        };
	    if (prev) {
			console.log("Set prev state: "+prev);
			$('#main_body').attr('class', prev);
		} else {
			console.log("Not defined prev state for nav menu");
		}
	}
	function checkScrolling() {
        window.setTimeout(function () {
            if($('#main_body').attr('class') === 'nav-md') {
                $('#overflow-scroll-menu').attr('style', 'overflow-y: auto; overflow-x: hidden');
            } else {
                $('#overflow-scroll-menu').attr('style', '');
            }
        }, 300);
		return true;
    }
      function getApiToken() {
            var token;
            jQuery.ajax({
                url: '/users/get_token',
                success: function (result) {
                   token = result;
                },
                async: false,
            });
            return token;
        }
	setNavBarFromPrevState();
</script>
<div id="menu-to-top"></div>
<div class="container body">
	<div class="main_container">
		<div class="col-md-3 left_col menu_fixed" id="overflow-scroll-menu" >
			<div class=" scroll-view">
				<div class="navbar nav_title" style="border: 0;">
					<a href="/" class="site_title">
					    <img src="/res/img/logo_light.png" style="width: 42px; margin-top: -5px; margin-right: 3px; margin-left: 3px; "/> 
					    <!-- <i class="fa fa-cog"></i> --> 
					    <span>{$BASE['provider_name']}</span>
					</a>
				</div>
				<div class="clearfix"></div>

				<br /> 
				<!-- sidebar menu -->
				<div id="sidebar-menu" class="main_menu_side hidden-print main_menu" >
						<ul class="nav side-menu">
EOL;
$data .= $display('customer_search', '<li><a href="/abonents/search"  ><i class="fa fa-search"></i> Поиск абонента</a></li>');

if (\envPHP\service\PSC::isGrpPermitted('questions')) {
    $data .= '<li><a><i class="fa fa-check-square"></i>Заявки<span class="fa  fa-chevron-down"></span></a>
								<ul class="nav child_menu">';

    $data .= $display('question_search', '<li><a href="/abonents/questions?action=search&responsible=me">Мои заявки</a></li>');
    $data .= $display('question_search', '<li><a href="/abonents/questions">Список заявок</a></li>');
    $data .= $display('question_search', ' <li><a href = "/abonents/questions?action=search" > Заявки на сегодня </a ></li > ');
    $data .= $display('question_create', '<li><a href="/abonents/new_questions">Новая заявка</a></li>');
    $data .= '</ul ></li> ';
}
if (\envPHP\service\PSC::isGrpPermitted('customer')) {
    $data .= '<li><a><i class="fa fa-pencil-square-o"></i>Абоненты<span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">';

    $data .= $display('customer_create', '<li><a href="/abonents/new_agreement">Создать новый договор</a></li>');
    $data .= $display('customer_deptors', '<li><a href="/abonents/credit">Работа с должниками</a></li>');
    $data .= $display('customer_report_certs', '<li><a href="/abonents/purpose_of_payment">Печать квитанций</a></li>');
    $data .= $display('customer_mass_messages', '<li><a href="/sms/send_to_all">Массовые СМС</a></li>');
    $data .= '</ul ></li>';
}

if (\envPHP\service\PSC::isGrpPermitted('payments')) {
    $data .= '<li><a><i class="fa fa-money"></i>Платежи<span class="fa fa-chevron-down"></span></a>
								<ul class="nav child_menu">';

    $data .= $display('payment_search', '<li><a href="/paymants/search">Поиск платежей</a></li>');
    $data .= $display('payment_source', '<li><a href="/paymants/payment_sources">Источники платежей</a></li>');
    $data .= $display('payment_summary_source', '<li><a href="/paymants/stat">Сводные (графики)</a></li>');
    $data .= $display('payment_summary_price', '<li><a href="/paymants/abon_prices">Сводные по прайсам</a></li>');
    $data .= $display('payment_create', '<li><a href="/paymants/add">Внести платеж</a></li>');
    $data .= $display('payment_liqpay', '<li><a href="/paymants/check_liqpay">Ручная проверка LiqPay</a></li>');
    $data .= '</ul ></li>';
}

$data .= $display('eq_binding_search', '<li><a href="/equipment/bindings"><i class="fa fa-signal"></i>Поиск привязок</a></li>');

if (\envPHP\service\PSC::isGrpPermitted('equipments')) {
    $data .= '<li><a><i class="fa fa-cogs"></i>Железки<span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu">';
    $data .= $display('eq_pinger', '<li><a href="/equipment/pinger">Пингер (mobile)</a></li>');
    $data .= $display('eq_pinger', '<li><a href="/sw/">Свитчер</a></li>');
    $data .= $display('eq_binding_create', '<li><a href="/equipment/bindingsAdd">Внести привязку</a></li>');
    $data .= $display('eq_show', '<li><a href="/equipment/list">Список железок</a></li>');
    $data .= $display('eq_create', '<li><a href="/equipment/edit">Внести железку</a></li>');
    $data .= $display('eq_models', '<li><a href="/equipment/models">Модели</a></li>');
    $data .= $display('eq_group', '<li><a href="/equipment/groups">Группы</a></li>');
    $data .= $display('eq_access', '<li><a href="/equipment/access">Доступы</a></li>');

    if (getGlobalConfigVar('OMO_SYSTEMS') && getGlobalConfigVar('OMO_SYSTEMS')['enabled']) {
        $data .= <<<EOL
    <li><a href="/omo/devices">Список устройств OMO</a></li>
    <li><a href="/omo/device_bindings">Привязки к OMO</a></li>
EOL;
    }

    $data .= '</ul></li>';
}


if (\envPHP\service\PSC::isGrpPermitted('network_vlan')) {
    $data .= '<li><a><i class="fa fa-sitemap"></i>Сети<span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu">';
    $data .= $display('vlan_show', '<li><a href="/network/vlans">Вланы</a></li>');
    $data .= $display('network_show', '<li><a href="/network/networks">Подсети</a></li>');
    $data .= '</ul></li>';
}

if (\envPHP\service\PSC::isGrpPermitted('employees')) {
    $data .= '
                            <li><a><i class="fa fa-user"></i>Персонал<span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu">';
    $data .= $display('employees_show', '<li><a href="/users/list">Список персонала</a></li>');
    $data .= $display('employees_group', '<li><a href="/users/groups">Группы</a></li>');
    $data .= $display('employees_notification', '<li><a href="/users/notifications">Уведомления</a></li>');
    $data .= $display('employees_schedule_show', '<li><a href="/users/schedule/">График дежурств</a></li>');
    $data .= $display('question_loading', '<li><a href="/users/question_loading">Загрузка по территориям</a></li>');
    $data .= $display('employees_reaction_stat', '<li><a href="/users/reaction_stat">Реакция</a></li>');
    $data .= '</ul></li>';
}


if (\envPHP\service\PSC::isGrpPermitted('trinity') && getGlobalConfigVar('TRINITY')) {
    $data .= '<li><a><i class="fa fa-tv"></i>TrinityTV<span class="fa fa-chevron-down"></span></a>
            <ul class="nav child_menu">';
    $data .= $display('trinity_contracts', ' <li><a href="/trinity/contracts">Список контрактов</a></li>');
    $data .= $display('trinity_search', '<li><a href="/trinity/bindings">Привязки</a></li>');
    $data .= $display('trinity_binding_add', '<li><a href="/trinity/add_binding">Добавить привязку</a></li>');
    $data .= '</ul></li>';
}


if (\envPHP\service\PSC::isGrpPermitted('sys') && getGlobalConfigVar('TRINITY')) {
    $data .= '<li><a><i class="fa fa-server"></i>Система<span class="fa fa-chevron-down"></span></a>
            <ul class="nav child_menu">';
    $data .= $display('sys_question_reason', ' <li><a href="/system/question_reasons">Управление типом заявок</a></li>');
    $data .= '</ul></li>';
}


 if (getGlobalConfigVar('OFFICIAL_WEB_SITE')) {
$data .= <<<EOL
    <ul class="nav side-menu">
        <li><a><i class="fa fa-tags"></i>Сайт<span class="fa fa-chevron-down"></span></a>
            <ul class="nav child_menu">
                <li><a href="/site/list?cat=1">Новости</a></li>
                <li><a href="/site/list?cat=2">Статьи</a></li>
                <li><a href="/site/pages">Страницы</a></li>
                <li><a href="/site/questions">Быстрые заявки</a></li>
            </ul>
                        </li>
	</ul>
EOL;
}
$data .= <<<EOL
				</div>
				<!-- /sidebar menu -->
			</div>
		</div>

		<!-- top navigation -->
		<div class="top_nav">
			<div class="nav_menu">
				<nav>
					<div class="nav toggle">
						<a id="menu_toggle" onclick="checkScrolling();savePrevState(); return true;"><i class="fa fa-bars"></i></a>
					</div>

					<ul class="nav navbar-nav navbar-right">
						<li class="">
							<a href="#" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
							 #U:name#
								<span class=" fa fa-angle-down"></span>
							</a>

                            <ul class="dropdown-menu dropdown-usermenu pull-right">
								<!-- <li><a href="javascript:;"> Profile</a></li>
								<li><a href="javascript:;">Help</a></li> -->
								<li><a href="{$_SERVER['REQUEST_SCHEME']}://logout@{$_SERVER['HTTP_HOST']}/"><i class="fa fa-sign-out pull-right"></i>Выйти</a></li>
							</ul>
						</li>
					</ul>
				</nav>
			</div>
		</div>
		<!-- /top navigation -->
		<script>
            checkScrolling();
		</script>
		<!-- page content -->
		<div class="right_col" role="main" id="content_body">
			<div class="">
				<div class="page-title">
					<div class="title_left">
						<h3>#title#</h3>
					</div>

					<div class="title_right">
					</div>
				</div>
			</div>
			<div class="clearfix"></div>

EOL;

 return $data;

 