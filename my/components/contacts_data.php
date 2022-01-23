<?php
return function ($name, $phone, $email)
{
    $html = "<table class=\"table table-hover text-nowrap\" align=\"center\">
				<tbody><tr>
					<td>
						{{APPEAL}}:
					</td><td>
						<b>
						$name
						</b>
				</td></tr><tr>
					<td>
						{{PHONE_NUMBER}}:
					</td><td>
						<b>$phone</b>
				</td></tr><tr>
					<td>
						E-mail:
					</td><td>
						<b>$email</b>
			</td></tr></tbody></table>";
    return $html;
};