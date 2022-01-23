<?php
$mpdf = new \Mpdf\Mpdf([
    'tempDir' => '/tmp/php/pdf',
    'format' => [210, 297 ],
]);

$form = [
    'agreements' => [],
];
envPHP\classes\std::Request($form);

$output = "";
foreach ($form['agreements'] as $agree) {
    $variables = getGlobalConfigVar('PDF_PRINTING')['message_of_payment'];
    $variables['params']['Нараховано'] = $agree['counted'];
    $variables['params']['Заборгованість'] = $agree['score'];
    $variables['params']['Всього до оплати'] = $agree['total_for_payment'];
    $variables['params']['Період платежу'] = $agree['period'];
    $variables['params']['Призначення платежу'] = $agree['destination'];
    $variables['params']['Код виду платежу'] = $agree['code'];
    $receiptRAW = file_get_contents($variables['path']);
    foreach ($variables['params'] as $key=>$value) {
        $receiptRAW = preg_replace("/({{{$key}}})/", $value,$receiptRAW);
    }
    $output .= "<table><tr><td>$receiptRAW</td></tr></table>";
}

$mpdf->WriteFixedPosHTML($output, 15,10,180,297);
$mpdf->Output('payment_list.pdf', 'I');
