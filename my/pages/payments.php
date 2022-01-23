<?php

use envPHP\ClientPersonalArea\Pagination;

return function ($payments, $pageno) {
    if(count($payments) === 0 ) {
        return " <div class=\"container-fluid\">
                <div class=\"row\">
                    <div class=\"col-lg-12\">
                        <div class=\"card card-warning card-outline\">
                            <div class=\"card-body table-responsive p-0\">
                            <div class='card-title'><h3 align='center'>{{RECORDS_NOT_FOUND}}</h3></div></div></div></div></div></div>";
    }
    $PAGINATION_COMPONENT = require __DIR__ . '/../components/pagination.php';
    $paginationData = new Pagination($payments, 10);
    $paginationHMTL = $PAGINATION_COMPONENT('payments', $paginationData->getTotalPages(), $pageno);
    $paymentTableHtml = "";
    foreach ($paginationData->getPage($pageno) as $el) {
        $paymentTableHtml .= "
            <tr>
                <td>{$el['time']}</td>
                <td>{$el['money']}</td>
                <td>{$el['comment']}</td>
            </tr>
        ";
    }
    return <<<HTML
 <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-warning card-outline"> 
<div class="card-title">{{LAST_20_PAYMENTS}}</div>
<div class="card-body  table-responsive">
<table style='width:100%'>
                <tr>
                    <td align='center'>
                       {$paginationHMTL}
                    </td>
                </tr>
                <tr>
                    <td align='right'>

                        {{QUEST_PAGE}} № <b>{$pageno}/{$paginationData->getTotalPages()}<b>
                    </td>
                </tr>
                <tr>
                    <td>
                       <table class="table table-hover text-nowrap" align="center" >
                            <thead>
                            <tr style="background: white">
                                <th>{{DATE}}</th>
                                <th>{{AMOUNT}}</th>
                                <th>{{COMMENT}}</th>
                             </tr>
                             </thead>
                             <tbody>
                                {$paymentTableHtml} 
                            </tbody>
                             
                        </table>
                    </td>
                </tr>
            </table>
            </div>
</div></div></div></div>
HTML;
};
