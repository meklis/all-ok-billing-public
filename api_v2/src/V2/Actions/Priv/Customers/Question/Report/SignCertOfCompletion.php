<?php


namespace Api\V2\Actions\Priv\Customers\Question\Report;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class SignCertOfCompletion extends Action
{
    protected $cfg;
    protected function action(): Response
    {
        $cfg = getGlobalConfigVar('CERT_OF_COMPLETION');
        if(!$cfg['enabled']) {
            throw new HttpForbiddenException($this->request, "Cert of completion disabled in global configuration");
        }
        $this->cfg = $cfg;
        $data = $this->getFormData();
        if(!isset($data['id']) || !$data['id']) {
            throw new HttpBadRequestException($this->request, "Field 'id' is required ");
        }
        if(!isset($data['sign']) || !$data['sign']) {
            throw new HttpBadRequestException($this->request, "Field 'sign' is required ");
        }

        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => $cfg['temporary_path'],
        ]);
     //   $mpdf->SetImportUse();
        $pagecount = $mpdf->SetSourceFile($cfg['path'] . "/" . $data['id'] . ".pdf");
        $tplId = $mpdf->ImportPage($pagecount);
        $mpdf->UseTemplate($tplId);
        $fileName = $this->saveTmpPng($data['sign']);
        $mpdf->Image($fileName, 137, 180, 48,32, 'png', '', true, false);
        $mpdf->Output($cfg['subscribed_path'] . "/" . $data['id'] . ".pdf", 'F');
        $this->saveToDb($data['id']);
        return $this->respondWithData("OK");
    }
    protected function saveTmpPng($pngBase64) {
        list($type, $data) = explode(';', $pngBase64);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);
        $md5 = md5($data);
        if(!file_put_contents("{$this->cfg['temporary_path']}/$md5.png", $data)) {
            throw new \Exception("Error save \"{$this->cfg['temporary_path']}/$md5.png\"");
        };
        return "{$this->cfg['temporary_path']}/$md5.png";
    }

    protected function saveToDb($id) {
        if(!dbConn()->query("UPDATE question_responses SET cert_subscribed=1 WHERE id = '$id'")) {
            throw new \Exception("SQL ERR: ". dbConn()->error);
        };
    }
}