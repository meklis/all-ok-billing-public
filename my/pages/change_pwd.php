<?php
return function () {
    return '
 <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-warning card-outline">
                            <div class="card-title">{{PAGE_CHANGE_PASSWORD}}</div>
                            <div class="card-body table-responsive">
<form action="act.php?act=change_pwd" method="POST">
<table class="class="table table-hover text-nowrap"" align="center" >
			<tr>
				<td>
					{{PWD_OLD}}:
				<td>
					<input class="form-control" type="password" name="old_pwd">
			<tr>
				<td>
					{{PWD_NEW}}:
				<td>
					<input class="form-control"  type="password" name="new_pwd">
			<tr>
				<td>
					{{PWD_NEW_REPEAT}}:
				<td>
					<input class="form-control"  type="password" name="new_pwd_confirm">
			</table><br>
				<input type="hidden" name="act" value="change_pwd">
			<input type="submit"  class="btn btn-default" value="  {{PWD_CHANGE_BTN}}   ">
			</form>
			<br><br></form></div></div></div></div></div>';
};
