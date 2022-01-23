<?php

return function ($phone) {
    return '
 <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-warning card-outline">
                            <div class="card-title">{{PAGE_CREATE_QUESTION}}</div>
                            <div class="card-body">
                                <form class="form-horizontal" action="/act.php?act=mail" method="POST">
                                   <div class="form-group row ">
                                        <div class="offset-sm-3 col-sm-6">
                                            <small>{{QUEST_CONTACT_PHONE}}:</small><br>
                                            <input  type="text" name="contact" class="form-control" placeholder="+380631234567" value="'.$phone.'">
                                        </div>
                                    </div>
                                    <div class="form-group row ">
                                        <div class="offset-sm-3 col-sm-6">
                                            <small>Текст {{PAGE_QUESTIONS}}:</small><br>
                                            <textarea size="600" class="form-control" rows="7" cols="45" name="message"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row ">
                                        <div class="offset-sm-3 col-sm-6">
                                             <input type="submit" class="btn btn-default"   value="  {{PAGE_CREATE_QUESTION}}   ">       
                                        </div>
                                    </div>
                                </form>
		                       </div>
		                     </div>
		                </div>
		            </div>
</div>         ';
};
