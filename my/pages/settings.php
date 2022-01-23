<?php

return function ($client) {
    $credit_period_btn = "<a href='act.php?act=enable_credit_period' class='btn btn-default' style='margin: 5px'>{{SERVICE_CONTROL_CREDIT_ACTIVATE}}</a>";
    $action_btn = "<a href='act.php?act=defrost' class='btn btn-default' style='margin: 5px'>{{SERVICE_CONTROL_BTN_START}}</a>";
    $services = $client->getServices();
    if(count($services) == 0 ) {
        $credit_period_btn = "";
        $action_btn = "";
    } else {
        foreach ($services as $service) {
            if ($service['status'] == 'ACTIVATED') {
                $action_btn = "<a href='act.php?act=frost' class='btn btn-default' style='margin: 5px'>{{SERVICE_CONTROL_BTN_STOP}}</a>";
            }
        }
    }
    if($client->isCreditEnabled()) {
        $credit_period_btn = "<a href='#' class='btn btn-default disabled' style='margin: 5px'>{{SERVICE_CONTROL_CREDIT_ACTIVATED}}</a>";
    }

    return <<<HTML
 <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
                        <div class="card card-warning card-outline">
                            <div class="card-title">{{SERVICE_CONTROL_STOP_START}}</div>
                            <div class="card-body"> 
                                {{SERVICE_CONTROL_STOP_START_DESCR}}<br>
                                $action_btn<br>
                                <small>{{SERVICE_CONTROL_STOP_START_LIMIT}} </small>
                                <br>
                                <small style="color:red;">{{SERVICE_CONTROL_STOP_START_PERIOD}}</small>
                            </div>
                        </div>  
                    </div>  
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
                        <div class="card card-warning card-outline">
                            <div class="card-title">{{SERVICE_CONTROL_CREDIT}}</div>
                            <div class="card-body"> 
                                {{SERVICE_CONTROL_CREDIT_DESCR}}<br>
                                $credit_period_btn
                                <br><br>
                               <span style="color:red;"> {{SERVICE_CONTROL_CREDIT_PERIOD}}</span>
                            </div>
                        </div>  
                    </div>  
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4">
                        <div class="card card-warning card-outline">
                            <div class="card-title">{{SERVICE_CONTROL_CHANGE_PWD}}</div>
                            <div class="card-body"> 
                                <a href="index.php?p=change_pwd" class="btn btn-default">{{PWD_CHANGE_BTN}} в {{PERSONAL_AREA_LABEL}}</a>
                            </div>
                        </div>  
                    </div>   
</div></div>
HTML;
};
