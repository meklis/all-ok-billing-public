<?php
declare(strict_types=1);

namespace Api\V2\Actions\User;

use Api\V2\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;
use OpenApi\Annotations\Post;

class GetUserStatusAction extends UserAction
{

    protected function action(): Response
    {
       $employeeId = $this->request->getQueryParams()['USER_ID'];
       return $this->respondWithData($this->user->getUserStatus($employeeId));
    }
}
