<?php

use envPHP\ClientPersonalArea\ClientActions;

return function ($client, $payState, $amount) {
    $info = $client->getGeneralInfo();
    $actions = new ClientActions($client);

    if(!$actions->isPossibleLiqPay()) {
        return "<h3 align='center'>На жаль, для даної послуги поки не підтримується оплата LiqPay</h3>";
    }

    if(!$amount) $amount = 120;
    $html = "";
    if($payState) {
        $html .= "
Договір: <b>{$info['agreement']}</b><br>
Зареєсровано на: <b>{$info['name']}</b><br>
Сума оплати: <b>{$amount} UAH</b><br>
{$actions->createLiqPayOrder($amount)}
";
    }

    return <<<HTML
 <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-warning card-outline">
                            <div class="card-title">{{PAY_OVER_LIQPAY}}</div>
                            <div class="card-body table-responsive  ">
    <table class='' >
       <tr>
         <td>
            <form method='post'>
            	<input hidden type='hidden' name='page' value='pay'> 
            	<small><i>{{PAY_LIQPAY_DETAIL}}</i></small><br>
            	<br>
	            <small>{{PAY_LIQPAY_AMOUNT_FIELD_DESCR}}</small><br>
	            <input name='amount' value='$amount' class='form-control col-sm-6' pattern='[0-9]{1,5}'>
                <button style='border: 1px solid gray; margin-top: 5px;' class='btn btn-default' type='submit' name="liqpay_prepaded" value="1">{{PAY_LIQPAY_BTN}}</button>
            </form> 
         </td>
       </tr>
       <tr>
          <td>
            $html
          </td>
       </tr>
    </table>
</div></div></div></div></div>
HTML;

};
