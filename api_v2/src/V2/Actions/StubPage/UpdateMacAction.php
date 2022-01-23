<?php


namespace Api\V2\Actions\StubPage;


use Api\V2\Actions\Action;
use Api\V2\Actions\ActionPayload;
use envPHP\service\bindings;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface as Response;
use Ramsey\Uuid\Uuid;

class UpdateMacAction extends Action
{
    protected function action(): Response
    {
        $uuid = $this->request->getAttribute('uuid');
        $data = $this->getDataForUpdateByUid($uuid);
        $employee_id = getGlobalConfigVar('BASE')['lc_user_id'];

        if($data['old_mac_addr'] == $data['new_mac_addr']) {
            return $this->respondWithData("NO_UPDATE_REQUIRED");
        }
        try {
            $resp = bindings::edit($data['binding_id'], '', $data['new_mac_addr'], '', '', $employee_id);
            dbConnPDO()->prepare("UPDATE stub_page_results SET binding_updated_at = NOW(), binding_update_result = :update_result WHERE id = :id")
                ->execute([':update_result'=>json_encode(['status'=> 'success', 'data' => $resp]), ':id' => $data['id']]);
            \envPHP\EventSystem\EventRepository::getSelf()->notify('stub_page:binding_updated', $data);
        } catch (\Exception $e) {
            dbConnPDO()->prepare("UPDATE stub_page_results SET binding_updated_at = NOW(), binding_update_result = :update_result WHERE id = :id")
                ->execute([':update_result'=>json_encode(['status'=> 'fail', 'error' =>$e->getMessage()]), ':id' => $data['id']]);
            \envPHP\EventSystem\EventRepository::getSelf()->notify('stub_page:error_binding_update', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw new ClientException("ERROR_UPDATE_BINDING");
        }
        return $this->respondWithData("SUCCESS_UPDATED");
    }
    protected function getDataForUpdateByUid($uid) {
        $prepared = dbConnPDO()->prepare("SELECT id, uuid, remote_addr, agreement_id, old_mac_addr, new_mac_addr, binding_id FROM stub_page_results WHERE uuid = :id");
        $prepared->execute([':id'=>$uid]);
        return $prepared->fetch();
    }

}

