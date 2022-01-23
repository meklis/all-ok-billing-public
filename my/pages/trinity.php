<?php


use envPHP\ClientPersonalArea\ClientInfo;

/**
 * @return string
 * @var ClientInfo $client
 */
return function ($client, $form) {
    $services = array_filter($client->getServices(), function ($e) {
        if ($e['work_type'] == 'trinity') {
            return true;
        }
        return false;
    });
    $select_activation = function () use ($client, $services, $form) {
        $input = "";
        foreach ($services as $service) {
            if ($service['status'] === 'FROSTED') continue;
            $sel = $service['id'] == $form['activation_id'] ? "SELECTED " : "";
            $input .= "<OPTION value='{$service['id']}'>{$service['name']}</OPTION>";
        }
        return $input;
    };
    $activate_service_form = function () use ($client, $services, $form) {
        $prices = $client->getPriceList();
        $HTML = "";
        //Услуга не активирована.
        //Должно быть описание что и как
        $HTML .= "<h3 align='center'>{{OTT_NOT_ACTIVATED}}</h3>";
        $HTML .= "
        <br>{{OTT_DETAILED_INFO}}
        <br><small>{{OTT_CHOOSE_PRICE}}</small>
         <small>{{OTT_LINK_PRICES_LIST}}</small>
        <form action='/act.php' method='post'>
        <SELECT name='price_id' class='form-control' style='margin: 3px'>";
        foreach ($prices as $price) {
            if ($price['work_type'] != 'trinity') continue;
            $sel = $form['price_id'] == $price['id'] ? 'SELECTED ' : '';
            $HTML .= "<OPTION value='{$price['id']}' $sel>{$price['name']}</OPTION>";
        }
        $HTML .= "</SELECT>";
        $HTML .= "<INPUT name='page' value='ott' hidden='hidden'>";
        $HTML .= "<button name='act' value='add_price' style='margin: 3px' class='btn btn-default'>{{ACTIVATE_PRICE}}</button>";
        return $HTML;
    };

    $registered_services_header = function () use ($client, $services, $form) {
        $names = [];
        foreach ($services as $service) {
            $names[] = $service['name'];
        }
        return "<h3 align='center'>{{OTT_ACTIVATED_SERVICE_LIST}}: " . join(', ', $names) . "</h3>";
    };

    $prices = $client->getPriceList();
    $HTML = "";
    if (count($services) == 0) {
        $HTML .= $activate_service_form();
    } else {
        $HTML .= $registered_services_header();
        $HTML .= "<div class='row'><div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-2'></div><div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-8'>" . registrationForm($form, $select_activation) . "</div></div>";

        $devices = $client->getTrinityBindings();
        if (count($devices) == 0) {
            $HTML .= "<h4 align='center' style='color: darkred'>{{OTT_NOT_FOUND_REGISTERED_DEVICES}}</h4>";
        } else {
            $HTML .= "<h4>{{OTT_REGISTERED_DEVICES}}</h4>";
            $HTML .= "<div class='table-responsive'>
        <table style='width:100%' class='table table-striped table-bordered table-condensed'>
        <tr> 
        <th>{{OTT_REGISTRATION_DATE}}</th>
        <th>{{OTT_PRICE_NAME}}</th>
        <th>{{OTT_MAC_ADDR}}</th>
        <th>{{OTT_UUID_PLAYLIST}}</th>
        <th>{{OTT_ACTIONS}}</th>
        </tr>";
            foreach ($devices as $dev) {
                $uuid = $dev['uuid'];
                if ($dev['local_playlist_id']) {
                    $url = getGlobalConfigVar('BASE')['api2_front_addr'] . '/playlist/' . $dev['local_playlist_id'];
                    $uuid = "<a href='$url' target='_blank'>$url</a>";
                }
                $HTML .= "<tr>
                    <td>{$dev['created']}</td>
                    <td>{$dev['price_name']}</td>
                    <td>{$dev['mac']}</td>
                    <td>{$uuid}</td>
                    <td><a title='{{OTT_DELETE_DEVICE}}' class='btn btn-danger' href='/act.php?act=delete_trinity_device&page=ott&id={$dev['id']}' onclick='return  confirm(\"{{ARE_YOU_SURE}}\");'><span class='fa  fa-minus-circle'></span></a></td>
                    </tr>
";
            }
            $HTML .= "</table></div>";
        }

    }


    return <<<HTML
 <div class="container-fluid">
                <div class="row">
                        <div class='col-xs-12 col-sm-1 col-md-1 col-lg-1'></div>
                        <div class='col-xs-12 col-sm-10  col-md-10 col-lg-10'>
                        <div class="card card-warning card-outline">     
<div class="card-title">{{OTT_SERVICES}}</div>
<div class="card-body">
$HTML
</div></div></div></div></div>
HTML;
};


function registrationForm($form, $select_activation)
{
    $HTML =  <<<HTML
<form  method="POST" enctype="multipart/form-data" action="/act.php">
    <div class="row" id="reg_btn">
        <div class="col-sm-12 col-lg-12 col-md-12 col-xs-12">
            <button class="btn btn-default btn-block" onclick="showRegBlock(); return false;">{{OTT_ADD_DEVICE}}</button>
        </div>    
    </div>
    <div class="row" id="reg_block" style="display: none">
        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
            <input name="page" hidden="hidden" value="ott">   
            <span style="text-align: center">{{OTT_REGISTRATION_INSTRUCTION}}</span>
            <div class="form-horizontal  ">
                <div class="form-group">
                    <label class="control-label col-md-12 col-sm-12 col-xs-12" for="agree">{{OTT_CHOOSE_ACTIVATION}}
                    </label>
                    <div class="col-sm-12 col-lg-12  col-md-12 col-xs-12">
                        <select name="activation_id" class="form-control btnPdn" onchange="submit();">
                           {$select_activation()}
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
            <ul class="nav nav-tabs"  >
                <li ><a id="link_code" class="nav-link reglink" href="javascript:void(0)" onclick="openRegTab('code');" >{{OTT_REG_BY_CODE}}</a></li>
                <li ><a id="link_mac" class="nav-link reglink" href="javascript:void(0)" onclick="openRegTab('mac');"  >{{OTT_REG_BY_MAC}}</a></li>
                <li ><a id="link_playlist" class="nav-link reglink" href="javascript:void(0)" onclick="openRegTab('playlist');"   >{{OTT_REG_PLAYLIST}}</a></li>
            </ul>
        </div>
        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12" >
            <div class="tab-content" style="border: 1px solid #F0F0F0;  margin-top: -1px" >
                <div id="code" class="tab-pane" style="display: none">
                    <h5 align="center">{{OTT_REG_BY_CODE}}</h5>
                    <div class="form-group">
                        <label class="control-label col-lg-12 col-sm-12 col-md-12 col-xs-12" for="agree">{{OTT_ACTIVATION_CODE}}
                        </label>
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                            <input name="code" class="form-control " value="{$form['code']}"  placeholder="1234">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                        </div>
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                            <button class="btn btn-default btn-block clearfix" type="submit" name="act"  value="trinity_add_by_code" >{{OTT_REG_DEVICE}}</button>
                        </div>
                    </div>
                </div>
                <div id="mac" class="tab-pane" style="display: none">
                    <h5 align="center">{{OTT_REG_BY_MAC}}</h5>
                    <div class="form-group">
                        <label class="control-label col-lg-12 col-sm-12 col-md-12 col-xs-12" for="agree">MAC
                        </label>
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                            <input name="mac" class="form-control " value="{$form['mac']}"  placeholder="AA:BB:CC:DD:EE:FF">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-lg-12 col-sm-12 col-md-12 col-xs-12" for="agree">UUID <small>{{OTT_UUID_NOT_REQUIRED}}</small>
                        </label>
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                            <input name="uuid" class="form-control " value="{$form['uuid']}"  placeholder="abcdef-abcd....">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                        </div>
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                            <button class="btn btn-default btn-block clearfix" type="submit" name="act"  value="trinity_reg_by_mac" >{{OTT_REG_DEVICE}}</button>
                        </div>
                    </div>
                </div>
                <div id="playlist" class="tab-pane" style="display: none">
                    <h5 align="center">{{OTT_PLAYLIST_GENERATION}}</h5>
                    <div class="form-group">
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                        </div>
                        <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
                            <button class="btn btn-default btn-block clearfix" type="submit" name="act"  value="trinity_generate_playlist" >{{OTT_GENERATE_PLAYLIST}}</button>
                        </div>
                    </div>
                </div>
            </div> 
        </div> 
        </div>
    </form>
<script>
function openRegTab(tabName) {
  var i, x, tablinks;
  x = document.getElementsByClassName("tab-pane");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("reglink");
  for (i = 0; i < x.length; i++) {
      console.log(tablinks[i]);
    tablinks[i].className = tablinks[i].className.replace("active", "");
  }
  document.getElementById(tabName).style.display = "block";
  document.getElementById('link_' + tabName).className += " active";
}
function showRegBlock() {
   document.getElementById('reg_block').style.display = 'block';
   document.getElementById('reg_btn').style.display = 'none';
}
</script>
HTML;
    if($form['act']) {
        $HTML .= "<script>
                    window.addEventListener('load', function() {
                        document.getElementById('reg_block').style.display = 'block';
                        document.getElementById('reg_btn').style.display = 'none';
                    });
                  </script>";
        switch ($form['act']) {
            case 'trinity_generate_playlist':
                $HTML .= "<script>openRegTab('playlist');</script>";
                break;
            case 'trinity_reg_by_mac':
                $HTML .= "<script>openRegTab('mac');</script>";
                break;
            case 'code':
                $HTML .= "<script>openRegTab('code');</script>";
                break;
            default:
                $HTML .= "<script>openRegTab('code');</script>";
        }
    } else {
        $HTML .= "<script>
                    window.addEventListener('load', function() {
                        document.getElementById('reg_block').style.display = 'none';
                        document.getElementById('reg_btn').style.display = 'block';
                    });
                  </script>";
    }

    return $HTML;

}
