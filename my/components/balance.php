<?php
return function ($balance, $payed_to) {
    $color = "#303f9f";
    if($balance <= 0) {
        $color = "darkred";
    }
    if($payed_to) {
        $payed_to = ", {{PAYED_TO}}: $payed_to";
    }
    return <<<HTML
<span style="color: $color; font-weight: bold ">{{BALANCE}}: {$balance} {{UAH}}{$payed_to}</span>
HTML;
};
