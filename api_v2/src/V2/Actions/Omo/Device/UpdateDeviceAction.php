<?php


namespace Api\V2\Actions\Omo\Device;


use Api\V2\Actions\Omo\OmoAction;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class UpdateDeviceAction extends OmoAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $requestBody = $this->getFormData();
        $this->control->getLocalOmo()->deviceUpdate(
            $this->request->getAttribute('id'),
            $requestBody['house'],
            $requestBody['entrance'],
            $requestBody['floor'],
            $requestBody['apartment'],
            $requestBody['comment']
        );
        return $this->respond($this->control->getLocalOmo()->deviceInfo($this->request->getAttribute('id')));
    }
}
