<?php

use envPHP\ClientPersonalArea\Pagination;

return function ($questions, $pageno) {
    if (count($questions) == 0) {
        return " <div class=\"container-fluid\">
                <div class=\"row\">
                    <div class=\"col-lg-12\">
                        <div class=\"card card-primary card-outline\">
                            <div class=\"card-body table-responsive p-0\"> 
                            <div class='card-body'><h3 style='text-align: center'>{{RECORDS_NOT_FOUND}}</h3></div></div></div></div></div></div>";
    }
    $PAGINATION_COMPONENT = require __DIR__ . '/../components/pagination.php';
    $paginationData = new Pagination($questions, 10);
    $paginationHMTL = $PAGINATION_COMPONENT('questions', $paginationData->getTotalPages(), $pageno);
    $tableHTML = "";
    foreach ($paginationData->getPage($pageno) as $el) {
        switch ($el['report_status']) {
            case 'IN_PROCESS':
                $color = "#ADBDFA";
                $report = "<b>{{QUEST_IN_PROCCESS}}</b>";
                break;
            case 'DONE':
                $color = "#AEECA0";
                $report = "<b>{{QUEST_DONE}}</b>";
                break;
            case 'CANCEL':
                $color = "#FAADAD";
                $report = "<b>{{QUEST_CANCELED}}</b></small>";
                break;
            case '':
                $color = "#F9FAAD";
                $report = "{{QUEST_WAIT}}";
                break;
            default:
                $color = "";
                $report = "Невiдомо";
        }
        $details = "";
        if ($el['completion_report_status'] == 'SUBSCRIBED') {
            $details = "<a target='_blank' href='/cert_of_completion.php?id={$el['report_id']}' title='{{QUEST_COMPLETION_CERTIFICATE}}'><img src='assets/img/download.png' style='width: 32px'></a>";
        }
        $tableHTML .= "
            <tr>
                <td>{$el['id']}</td>
                <td>{$el['dest_time']}</td>
                <td>{$el['reason']}</td>
                <td>{$report}</td>
                <td>{$details}</td>
            </tr>
        ";
    }
    return <<<HTML
 <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-warning card-outline">     
<div class="card-title">{{QUESTION_LIST}}</div>
<div class="card-body table-responsive ">
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
                                <tr>
                                    <th>ID {{PAGE_QUESTIONS}}</th>
                                    <th>{{TIME}}</th>
                                    <th>{{REASON}}</th>
                                    <th>{{QUEST_STATUS}}</th>
                                    <th><img src='assets/img/download.png'
                                             style='width: 32px'></th>
                                </tr>
                                </thead>
                             <tbody>
                                {$tableHTML} 
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
</div></div></div></div></div>
HTML;

};
