<?php
return function ($bindings) {
    $html = " <div class=\"container-fluid\">
                <div class=\"row\">
                    <div class=\"col-lg-12\">
                        <div class=\"card card-primary card-outline\">
                            <div class='card-title'>{{TRAFFIC_ON_PORT}}</div>
                            <div class=\"card-body table-responsive p-0\">";
    foreach ($bindings as $b) {
        $url = "https://sw.loc/zabbix?sw=";
        $ch = curl_init($url . $b['switch'] . '&port=' . $b['port']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $html .= curl_exec($ch);
        curl_close($ch);
    };
    return $html . "</div></div></div></div></div>";
};