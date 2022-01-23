<?php
return function ($client, $name, $phone, $email) {
    $html = "
     <div class=\"container-fluid\">
                <div class=\"row\">
                    <div class=\"col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6\">
                        <div class=\"card card-warning card-outline \">
<div class='card-title' style='text-align:'center;'>{{SERVICES_LIST}}:</div>
                            <div class=\"card-body table-responsive\">
<table class='table table table-services'>
        <thead>
            <tr>
                <th>{{PRICE_NAME}}</th>
                <th>{{PRICE}}</th>
                <th>{{ACTIVATED_FROM}}</th>
                <th>{{PRICE_STATE}}</th>
            </tr>
        </thead><tbody>";
    foreach ($client->getServices() as $service) {
        $status = $service['status'] == 'ACTIVATED' ? '{{SERVICE_ACTIVATED}}':'{{SERVICE_FROSTED}}';

        if($service['recalc_time'] == 'day') {
            $price = "{$service['price_day']} {{UAH}}/сут";
        } else {
            $price = "{$service['price_month']} {{UAH}}/мес";
        }
        $html .= "
            <tr>
                <td>{$service['name']}</td>
                <td>{$price}</td>
                <td>{$service['time_start']}</td>
                <td>{$status}</td>
            </tr>
        ";
    }
    $html .= "</tbody></table><br><a href='?p=settings' class='btn btn-default' style='margin: 20px; float: right' >{{PAGE_SERVICE_CONTROL}}</a></div></div></div>";
    $html .= "  <div class='col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6'>
                        <div class='card card-warning card-outline '>
    <div class='card-title'>{{CONTACTS_DATA}}</div>
                            <div class='card-body table-responsive'>
			<table  class=\"table table-striped table-bordered text-nowrap\" align=\"center\" >
				<tr>
					<td>
						{{APPEAL}}:
					<td>
						<b>
						{$name}
						</b>
				<tr>
					<td>
						{{PHONE_NUMBER}}:
					<td>
						<b>{$phone}</b>
				<!-- <tr>
					<td>
						E-mail:
					<td>
						<b>{$email}</b> -->
			</table>
			<br></div></div></div>";
    $conf = getGlobalConfigVar('PERSONAL_AREA');
    if($conf['show_registered_devices']) {
        $bindings = $client->getBindings();
        if(count($bindings) > 0 ) {
            $bhtml = '<table class="table table-striped table-bordered text-nowrap">
                        <tr> 
                               <th>{{PRICE_NAME}}</th>
                               <th>IP</th>
                               <th>MAC</th> 
                           ';
            foreach ($bindings as $binding) {
                $bhtml .= "<tr>
                    <td>{$binding['price']}</td>
                    <td>{$binding['ip']}</td>
                    <td>{$binding['mac']}</td>
                    </tr>
                    ";
            }
            $bhtml .= " </table>";
            $html .= "  <div class='col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6'>
                        <div class='card card-warning card-outline '>
    <div class='card-title'>{{REGISTERED_DEVICES}}</div>
                            <div class='card-body table-responsive'> 
			            
			{$bhtml}</div></div></div>";
        }
    }
    if($conf['show_neighbors']) {
        $neighborAgreements = $client->getNeighborAgreements();
        if(count($neighborAgreements) > 0 ) {
            $neighbors = '<ul>';
            foreach ($neighborAgreements as $agree) {
                $prices = $agree['prices'] ? ' - ' . $agree['prices'] : '';

                $neighbors .= "<li><a href='/?goto_agreement={$agree['id']}'>{$agree['agreement']}</a> $prices </li>";
            }
            $neighbors .= "</ul>";
            $html .= "  <div class='col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6'>
                        <div class='card card-primary card-outline '>
    <div class='card-title'>{{NEIGHBOR_AGREEMENTS}}</div>
                            <div class='card-body table-responsive'>
			            {{NEIGHBOR_AGREEMENTS_DESCRIPTION}}
			            
			<br>{$neighbors}</div></div></div>";
        }
    }
    return $html .'</div></div>';
};
