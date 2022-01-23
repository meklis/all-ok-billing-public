<?php


namespace Api\V2\Actions\Omo\User;


use Api\V2\Actions\Omo\OmoAction;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class GetUserFilterAction extends OmoAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $filter = $this->request->getQueryParams();
        $this->logger->debug(json_encode($filter));
        $data = $this->control->getLocalOmo()->userGetFiltered($filter);
        $this->logger->debug("User response: ". json_encode($data));
        return  $this->respondWithData($data);
    }
}
