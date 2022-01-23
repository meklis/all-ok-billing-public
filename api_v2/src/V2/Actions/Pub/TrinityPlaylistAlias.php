<?php


namespace Api\V2\Actions\Pub;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use envPHP\service\TrinityControl;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

class TrinityPlaylistAlias extends Action
{
    protected function action(): Response
    {
        $playListId = $this->request->getAttribute('playlist_id');
        try {
            $trinityBinding = TrinityControl::getBindingByPlaylistId($playListId);
            $resp = (new Client([
                'base_uri' => '',
                'timeout'  => 10.0,
            ]))->get($trinityBinding['uuid']);
            $this->response->getBody()->write($resp->getBody()->getContents());
            $this->response->withHeader('Content-Type', 'text/plain; charset=utf-8');
            return  $this->response;
        } catch (\Exception $e) {
            throw new HttpForbiddenException($this->request, "Playlist is disabled. You must activate service first");
        }
    }

}