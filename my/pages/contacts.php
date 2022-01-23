<?php
return function () {
    return <<<HTML
 <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-warning card-outline">
                         <div class="card-title">{{PAGE_CONTACTS}}</div>
                            <div class="card-body">
<div>
    <div class="row">
        <div class="col-12" style="text-align:center;">
            <div class="card-body">

                <ul style="list-style:none;">
                    <li class=""><i class="fas fa-lg fa-building"></i> : <span
                                style="color:grey;">{{COMPANY_ADDR}}</span></li>
                    <li>
                        <nobr style="color:grey;"><i
                                    class="fas fa-lg fa-at"></i><a
                                    href="mailto:all-ok-billing"> :
                                kibass@ukr.net</a></nobr>
                    </li>
                    <li>
                        <nobr style="color:grey;"><i
                                    class="fab fa-lg fa-telegram"></i><a
                                    href="https://t.me/all-ok-billing"> :
                                TelegramBot</a></nobr>
                    </li>
                    <li><a style="color:steelblue;font-size:19px;" href="tel:0631234567"><i class="fas fa-lg fa-phone"></i>0631234567</a></li> 
                     <li><a style="color:red;font-size:19px;" href="tel:0631234567"><i class="fas fa-lg fa-phone"></i>0631234567</a></li>
                    <li><a style="color:red;font-size:19px;"
                           href="tel:0631234567"><i
                                    class="fas fa-lg fa-phone"></i>0631234567</a>
                    </li>
                   </ul>

            </div>
        </div>
    </div>
</div>
</div></div></div></div></div>
HTML;

};
