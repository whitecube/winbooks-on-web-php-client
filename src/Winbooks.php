<?php

namespace Winbooks;

use GuzzleHttp\Client;
use Winbooks\Exceptions\TokenRequiredException;

class Winbooks
{
    /**
     * The GuzzleHTTP Client instance
     *
     * @var Client
     */
    protected $guzzle;

    /**
     * The folder name
     *
     * @var string
     */
    protected $folder;

    private $access_token;
    private $refresh_token;

    public function __construct(string $email, string $exchange_token)
    {
        if(!$exchange_token) {
            throw new TokenRequiredException('Please provide your Winbooks On Web OAuth Exchange Token.');
        }

        $access_token = $this->getAccessToken($email, $exchange_token);

        $this->guzzle = new Client([
            'base_uri' => 'https://rapi.winbooksonweb.be/app',
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json'
            ]
        ]);
    }

    protected function getAccessToken(string $email, string $exchange_token)
    {
        $guzzle = new Client([
            'base_uri' => 'https://rapi.winbooksonweb.be/app',
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($email),
                'Accept' => 'application/json'
            ]
        ]);

        $response = $guzzle->post('/OAuth20/Token', [
            'form_params' => [
                'grant_type' => 'exchange_token',
                'code' => $exchange_token
            ]
        ]);

        return $response['access_token'];
    }

    /**
     * Set the folder to use for the following requests.
     *
     * @param string $folder
     */
    public function folder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    public function all(string $oms)
    {
        return $this->guzzle->get("/$oms/Folder/$this->folder");
    }

    public function get(string $om, string $code)
    {
        return $this->guzzle->get("/$om/$code/Folder/$this->folder");
    }
}
