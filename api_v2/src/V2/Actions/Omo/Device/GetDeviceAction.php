<?php


namespace Api\V2\Actions\Omo\Device;


use Api\V2\Actions\Omo\OmoAction;
use OpenApi\Annotations\OpenApi;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;

class GetDeviceAction extends OmoAction
{
    /**
     * {@inheritdoc}
     *
     */
    protected function action(): Response
    {
        $devId = $this->request->getAttribute('id');
        $local = $this->control->getLocalOmo();
        try {
            $dev = $local->deviceInfo($devId);
        } catch (\Exception $e ) {
            throw new HttpNotFoundException($this->request, $e->getMessage());
        }
        $dev['users'] = $local->userFindByDevice($devId);

        return  $this->respondWithData($dev);
    }
}
