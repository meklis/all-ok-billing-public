<?php


namespace Api\V2\Actions\Omo\User;


use Api\V2\Actions\Omo\OmoAction;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class RevokeDevice extends OmoAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $req = $this->getFormData();
        if(isset($req['phone']) && isset($req['agreement_id']) && isset($req['device_id'])) {
            $req['phone'] = "+" . str_replace([' ', '-', '(', ')', '+'], '', $req['phone']);
            $user = $this->control->getLocalOmo()->userGetByPhone($req['phone']);
            $resp = $this->control
                ->deletePhone($req['phone'], $req['agreement_id'])
                ->revokeDevice($user['id'], $req['device_id'])
            ;
            return  $this->respondWithData($resp);
        } else {
            throw new HttpBadRequestException($this->request, "Fields phone, agreement_id, device_id is required");
        }
    }
}
