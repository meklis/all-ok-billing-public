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
                                    href="mailto:kibass@ukr.net"> :
                                kibass@ukr.net</a></nobr>
                    </li>
                    <li>
                        <nobr style="color:grey;"><i
                                    class="fab fa-lg fa-telegram"></i><a
                                    href="https://t.me/GoldenNetUaBot"> :
                                TelegramBot</a></nobr>
                    </li>
                    <li><a style="color:steelblue;font-size:19px;" href="tel:0973544545"><i class="fas fa-lg fa-phone"></i>097 354 4545</a></li> 
                     <li><a style="color:red;font-size:19px;" href="tel:0993544545"><i class="fas fa-lg fa-phone"></i>099 354 4545</a></li>
                    <li><a style="color:red;font-size:19px;"
                           href="tel:0933544545"><i
                                    class="fas fa-lg fa-phone"></i>093 354 4545</a>
                    </li>
                  <!--  <li><a style="color:black;font-size:19px;" href="tel:0443344540"><i class="fas fa-lg fa-phone"></i>044 334 4540</a> </li> -->
                </ul>

            </div>
        </div>
    </div>
</div>
</div></div></div></div></div>
HTML;

};
