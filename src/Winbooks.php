<?php

namespace Whitecube\Winbooks;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ClientException;
use Whitecube\Winbooks\ObjectModel;
use Whitecube\Winbooks\Exceptions\InvalidTokensException;
use Whitecube\Winbooks\Exceptions\UnauthenticatedException;
use Whitecube\Winbooks\Exceptions\UndefinedFolderException;
use Whitecube\Winbooks\Exceptions\InvalidRefreshTokenException;
use Whitecube\Winbooks\Exceptions\UndefinedObjectModelException;

class Winbooks
{
    /**
     * The GuzzleHTTP Client instance
     *
     * @var Client
     */
    protected $guzzle;

    /**
     * The OAuth 2.0 access token
     *
     * @var string
     */
    private $access_token;

    /**
     * The OAuth 2.0 refresh token
     *
     * @var string
     */
    private $refresh_token;

    /**
     * The authentication e-mail
     *
     * @var string
     */
    private $email;

    /**
     * The API base url
     *
     * @var string
     */
    protected $api_host = 'https://prd.winbooksapis.be/wow/v2/';
    // protected $api_host = 'https://rapi.winbooksonweb.be/';

    /**
     * The available models with their associated type
     *
     * @var array
     */
    static protected $models;

    /**
     * The folder name
     *
     * @var string
     */
    protected $folder;

    public function __construct(string $access_token = null, string $refresh_token = null)
    {
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }

    /**
     * Check if given string is a valid model type
     *
     * @param mixed $type
     * @return bool
     */
    public static function isModelType($type): bool
    {
        if(! is_string($type)) {
            return false;
        }

        return array_key_exists($type, static::getModelTypes());
    }

    /**
     * Create a model instance for given object model type
     *
     * @param string $type
     * @param array $attributes
     * @return \Whitecube\Winbooks\ObjectModel
     * @throws UndefinedObjectModelException
     */
    public static function makeModelForType(string $type, array $attributes = []): ObjectModel
    {
        $classname = static::getModelTypes()[$type] ?? null;

        if(! $classname) {
            throw new UndefinedObjectModelException('Undefined object model type "' . $type . '".');
        }

        return new $classname($attributes);
    }

    /**
     * Return all defined models & types
     *
     * @return array
     */
    public static function getModelTypes(): array
    {
        if(is_null(static::$models)) {
            static::$models = static::extractModelTypes(
                __DIR__ . '/Models',
                'Whitecube\\Winbooks\\Models'
            );
        }

        return static::$models;
    }

    /**
     * Recursively retrieve all defined models & types
     * in given directory.
     *
     * @param string $directory
     * @param string $namespace
     * @param array $stack
     * @return array
     */
    protected static function extractModelTypes($directory, $namespace, $stack = []): array
    {
        foreach (scandir($directory) as $item) {
            if(in_array($item, ['.','..'])) continue;

            $path = $directory . '/' . $item;

            if(is_dir($path)) {
                $stack = static::extractModelTypes($path, $namespace . '\\' . ucfirst($item), $stack);
                continue;
            }

            $path = pathinfo($path);

            if($path['extension'] !== 'php') {
                continue;
            }

            $classname = $namespace . '\\' . ucfirst($path['filename']);

            if(! is_a($classname, ObjectModel::class, true)) {
                continue;
            }

            $type = (new $classname)->getType();

            $stack[$type] = $classname;
        }

        return $stack;
    }

    /**
     * Check if the authentication tokens are set
     *
     * @return bool
     */
    public function authenticated(): bool
    {
        return !is_null($this->access_token) && !is_null($this->refresh_token);
    }

    /**
     * Authenticate with the e-mail and exchange token
     *
     * @param string $email
     * @param string $exchange_token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function authenticate(string $email, string $exchange_token): array
    {
        $data = $this->getAccessToken($email, $exchange_token);

        $this->email = $email;
        $this->access_token = $data['access_token'];
        $this->refresh_token = $data['refresh_token'];

        return [$data['access_token'], $data['refresh_token']];
    }

    /**
     * Get the access and refresh tokens
     *
     * @param string $email
     * @param string $exchange_token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccessToken(string $email, string $exchange_token): array
    {
        return $this->getAuth($email, 'exchange_token', $exchange_token);
    }

    /**
     * Get auth credentials
     *
     * @param string $email
     * @param string $grant_type
     * @param string $token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getAuth($email, $grant_type, $token): array
    {
        $guzzle = new Client([
            'base_uri' => $this->api_host,
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($email),
                'Accept' => 'application/json'
            ]
        ]);

        $response = $guzzle->post('OAuth20/Token', [
            'form_params' => [
                'grant_type' => $grant_type,
                'code' => $token
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Use the Refresh Token to get new Auth credentials
     *
     * @throws UnauthenticatedException
     */
    protected function refreshAuth()
    {
        try {
            $auth = $this->getAuth($this->email, 'refresh_token', $this->refresh_token);

            $this->access_token = $auth['access_token'];
            $this->refresh_token = $auth['refresh_token'];

            $this->initialize();
        } catch(ClientException $exception) {
            throw new InvalidRefreshTokenException('Please provide a valid Refresh Token.');
        }
    }

    /**
     * Initialize the GuzzleHTTP instance
     *
     * @throws UnauthenticatedException
     */
    public function initialize()
    {
        if(! $this->authenticated()) {
            throw new UnauthenticatedException("Please authenticate first, by passing your e-mail and Exchange Token to the authenticate() method, or by providing your Access and Refresh Tokens to the constructor.");
        }

        $this->guzzle = new Client([
            'base_uri' => $this->api_host,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->access_token,
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * Make sure Guzzle has been initialized and a folder has been set
     *
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    protected function ensureInitialized()
    {
        if(! $this->guzzle) {
            $this->initialize();
        }

        if(! $this->folder) {
            throw new UndefinedFolderException("Please specify a folder before making requests.");
        }
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

    /**
     * Attempt to use the API, and try to refresh the access token if it is invalid
     *
     * @param callable $callback
     * @param bool $secondAttempt
     * @return mixed
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     */
    protected function attempt(callable $callback, $secondAttempt = false)
    {
        try {
            $response = $callback();
        }
        catch (ClientException $exception) {
            if($secondAttempt) {
                throw new InvalidTokensException('Access Token and Refresh Token are invalid');
            }

            if($this->isUnauthorized($exception)) {
                $this->refreshAuth();

                return $this->attempt($callback, true);
            }

            throw $exception;
        }

        return $response;
    }

    /**
     * Check if the guzzle exception is a 401 response
     *
     * @param ClientException $exception
     * @return bool
     */
    protected function isUnauthorized(ClientException $exception): bool
    {
        return $exception->getResponse()->getStatusCode() == '401';
    }

    /**
     * Set the access token. Mainly for testing purposes.
     *
     * @param string $access_token
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * Set the refresh token. Mainly for testing purposes.
     *
     * @param string $refresh_token
     */
    public function setRefreshToken($refresh_token)
    {
        $this->refresh_token = $refresh_token;
    }

    /**
     * Decode the response if it worked
     *
     * @param Response $response
     * @return mixed
     */
    protected function decode(Response $response)
    {
        if($response->getStatusCode() !== 200) {
            return null;
        }

        $data = json_decode($response->getBody());

        if(! is_array($data)) {
            return $this->toModel($data);
        }

        return array_map(function($item) {
            return $this->toModel($item);
        }, $data);
    }

    /**
     * Transform an incoming data object into a model
     * instance when possible.
     *
     * @param null|\stdClass $data
     * @return mixed
     */
    public function toModel($data)
    {
        if(! is_a($data, \stdClass::class)) {
            return $data;
        }

        $attributes = (array) $data;

        if(! isset($attributes['$type']) || ! static::isModelType($attributes['$type'])) {
            return $data;
        }

        return static::makeModelForType($attributes['$type'], $attributes);
    }

    /**
     * Make a manual request to the API
     *
     * @param callable $attempt
     * @param null|array $stack
     * @return mixed
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function request(callable $attempt, array $stack = null)
    {
        $this->ensureInitialized();

        $response = $this->attempt($attempt);

        $result = $this->decode($response);

        if($stack) {
            $result = array_merge($stack, is_array($result) ? $result : [$result]);
        }

        if(! is_array($result) || ! $response->hasHeader('ContinuePath')) {
            return $result;
        }

        // The REST API has indicated that the result was truncated, we should
        // now continue filling the results array with the missing data. This
        // is done by sending the request again including the API's response
        // "ContinuePath" header until everything has been fetched.

        return $this->request(function($options = []) use ($attempt, $response) {
            $options = array_merge($options, [
                'headers' => ['ContinuePath' => $response->getHeader('ContinuePath')[0]],
            ]);

            return $attempt($options);
        }, $result);
    }

    /**
     * Get all objects from an object model namespace
     *
     * @param string $oms
     * @return mixed
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function all(string $oms)
    {
        return $this->request(function($options = []) use ($oms) {
            return $this->guzzle->get("app/$oms/Folder/$this->folder", $options);
        });
    }

    /**
     * Get an object from an object model namespace
     *
     * @param string $om
     * @param string $code
     * @param int $maxLevel
     * @return mixed
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function get(string $om, string $code, $maxLevel = 1)
    {
        return $this->request(function($options = []) use ($om, $code, $maxLevel) {
            return $this->guzzle->get("app/$om/$code/Folder/$this->folder?maxLevel=$maxLevel", $options);
        });
    }

    /**
     * Add an object
     *
     * @param string $om
     * @param string $code
     * @param array $data
     * @return \stdClass
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function add(string $om, string $code, array $data)
    {
        if(!isset($data['Code'])) {
            $data['Code'] = $code;
        }

        return $this->request(function($options = []) use ($om, $code, $data) {
            return $this->guzzle->post("app/$om/$code/Folder/$this->folder", array_merge($options, [
                'json' => $data
            ]));
        });
    }

    /**
     * Add many objects at once
     *
     * @param string $oms
     * @param array $objects
     * @return \stdClass|void
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function addMany(string $oms, array $objects)
    {
        return $this->request(function($options = []) use ($oms, $objects) {
            return $this->guzzle->post("app/$oms/Folder/$this->folder", array_merge($options, [
                'json' => $objects
            ]));
        });
    }

    /**
     * Update an object
     *
     * @param string $om
     * @param string $code
     * @param array $data
     * @return mixed
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function update(string $om, string $code, array $data)
    {
        return $this->request(function($options = []) use ($om, $code, $data) {
            return $this->guzzle->put("app/$om/$code/Folder/$this->folder", array_merge($options, [
                'json' => $data
            ]));
        });
    }

    /**
     * Update multiple objects at once
     *
     * @param string $oms
     * @param array $objects
     * @return \stdClass|void
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function updateMany(string $oms, array $objects)
    {
        return $this->request(function($options = []) use ($oms, $objects) {
            return $this->guzzle->put("app/$oms/Folder/$this->folder", array_merge($options, [
                'json' => $objects
            ]));
        });
    }

    /**
     * Delete an object
     *
     * @param string $om
     * @param string $code
     * @return \stdClass|void
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function delete(string $om, string $code)
    {
        return $this->request(function($options = []) use ($om, $code) {
            return $this->guzzle->delete("app/$om/$code/Folder/$this->folder", $options);
        });
    }

    /**
     * Add a model instance
     *
     * @param ObjectModel $model
     * @return \stdClass|void
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function addModel(ObjectModel $model)
    {
        return $this->request(function($options = []) use ($model) {
            $om = $model->getOM();
            $code = $model->getCode();

            return $this->guzzle->post("app/$om/$code/Folder/$this->folder", array_merge($options, [
                'json' => $model
            ]));
        });
    }

    /**
     * Add multiple model instances at once
     *
     * @param array $models
     * @return \stdClass|void
     * @throws InvalidTokensException
     * @throws UnauthenticatedException
     * @throws UndefinedFolderException
     */
    public function addModels(array $models)
    {
        return $this->request(function($options = []) use ($models) {
            $oms = $models[0]->getOMS();

            return $this->guzzle->post("app/$oms/Folder/$this->folder", array_merge($options, [
                'json' => $models
            ]));
        });
    }

}
