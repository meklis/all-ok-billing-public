<?php


namespace envPHP\MultiSwitcher;


use Curl\Curl;
use Curl\MultiCurl;

class MultiSwitcher
{
    /**
     * @var MultiCurl
     */
    protected $curl;
    protected $response = [];
    function __construct()
    {
        $this->createCurl();
    }
    protected function createCurl() {
        if($this->curl !== null) {
            $this->curl->close();
            $this->curl = null;
        }
        $this->curl = new MultiCurl();
        $this->curl->setConcurrency(100);
        $this->curl->setTimeout(30);
        $this->curl->setRetry(1);
        $this->curl->setJsonDecoder(function($resp) {
           return json_decode($resp, true);
        });
    }
    public function add(string $module, string $ip, array $parameters = []) {
        $curl = new Curl();
        $curl->setHeader('X-Device-Ip', $ip);
        $curl->setUrl(getGlobalConfigVar('BASE')['api2_front_addr'] . '/v2/trusted/equipment/switcher/module/' . $module, $parameters);
        $instance = $this->curl->addCurl($curl);
        $instance->ip = $ip;
        $instance->module = $module;
        return $this;
    }
    public function clear() {
        $this->createCurl();
    }
    public function process() {
        $response = [];
        $this->response = [];

        $this->curl->success(function ($instance) use (&$response) {
            $response[$instance->ip][$instance->module] = $instance->response;
        });
        $this->curl->error(function ($instance) use (&$response) {
            if(isset($instance->response)) {
                $response[$instance->ip][$instance->module] = $instance->response;
            } else {
                $response[$instance->ip][$instance->module] = [
                    'statusCode' => $instance->errorCode,
                    'error' => [
                        'type' => 'HTTP_ERROR',
                        'description' => $instance->errorMessage,
                        'stackTrace' => null,
                    ],
                ];
            }
        });
        $this->curl->start();
        $this->response = $response;
        return $this;
    }
    function getResponse() {
        return $this->response;
    }
    function getByIp($ip) {
        return $this->response[$ip];
    }
    function getByModule($module) {
        $response = [];
        foreach ($this->response as $ip=>$resp) {
            if(!isset($resp[$module])) continue;
            $response[$ip] = $resp[$module];
        }
        return $response;
    }
}