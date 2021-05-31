<?php

namespace App\Services;

use App\Services\Contracts\ServiceImpl;
use GuzzleHttp\Client;

class Service implements ServiceImpl
{
    function __construct(String $baseURL, string $api_key)
    {
        $this->baseURL = $baseURL;
        $this->api_key = $api_key;
        $this->init();
    }

    private function init()
    {
        $this->client = new Client([
            'base_uri' => $this->baseURL,
            'headers' => [
                's-api-key' => $this->api_key,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            ]
        ]);
    }

    /**
     * Makes HTTP calls using Guzzle Client
     * 
     * @param mixed $route Enpoint routes
     * @param string $method HTTP Method
     * @param mixed $data Post data
     * @param mixed $header HTTP header
     * @return GuzzleHttp\Message\ResponseInterface
     */
    public function call($route, string $method = 'GET', $data = [], $header = [])
    {
        $response = null;


        if (is_null($method)) {
            throw new \Exception("Empty method not allowed");
        }

        switch (strtolower($method)) {
            case 'get':
                $response = $this->get($route, $header);
                break;

            case 'post':
                $response = $this->post($route, $data, $header);
                break;

            case 'put':
                $response = $this->put($route, $data, $header);
                break;

            case 'patch':
                $response = $this->patch($route, $data, $header);
                break;

            case 'delete':
                $response = $this->delete($route, $header);
                break;

            default:
                throw new \Exception("Method not supported", 1);
                break;
        }

        return $response;
    }


    private function get($route, $header)
    {
        return $this->client->get(
            $this->baseURL . $route,
            ["header" => json_encode($header)]
        );
    }

    private function delete($route, $header)
    {
        return $this->client->delete(
            $this->baseURL . $route,
            ["header" => json_encode($header)]
        );
    }

    private function patch($route, $data, $header)
    {
        return $this->client->patch(
            $this->baseURL . $route,
            ["body" => json_encode($data), "header" => json_encode($header)]
        );
    }
    private function post($route, $data, $header)
    {
        return $this->client->post(
            $this->baseURL . $route,
            ["body" => json_encode($data), "header" => json_encode($header)]
        );
    }
    private function put($route, $data, $header)
    {
        return $this->client->put(
            $this->baseURL . $route,
            ["body" => json_encode($data), "header" => json_encode($header)]
        );
    }
}
