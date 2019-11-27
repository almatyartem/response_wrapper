<?php

namespace ApiSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Auth;
use Psr\Http\Message\ResponseInterface;

class AuthApi
{
    /**
     * @var GatewayApi
     */
    public $gatewayApi;

    /**
     * @var string
     */
    protected $authAppCode = 'auth';

    /**
     * CoreApi constructor.
     * @param GatewayApi $gatewayApi
     */
    public function __construct(GatewayApi $gatewayApi)
    {
        $this->gatewayApi = $gatewayApi;
    }

    /**
     * @return null
     * @throws GuzzleException
     */
    protected function getAppAccessToken()
    {
        $response = $this->gatewayApi->request($this->authAppCode , 'post','oauth/token',  [
            'grant_type' => 'client_credentials',
            'client_id' => env('GATEWAY_API_CLIENT_ID'),
            'client_secret' => env('GATEWAY_API_CLIENT_SECRET'),
        ], [], false);

        if($response and isset($response['access_token']) and $response['access_token'])
        {
            return $response['access_token'];
        }

        return null;
    }

    /**
     * @param $code
     * @return string|null
     * @throws GuzzleException
     */
    public function getClientToken($code) : ?string
    {
        $this->accessToken = null;

        $response = $this->gatewayApi->request($this->authAppCode , 'post','oauth/token',  [
            'grant_type' => 'authorization_code',
            'client_id' => env('GATEWAY_API_CLIENT_ID'),
            'client_secret' => env('GATEWAY_API_CLIENT_SECRET'),
            'redirect_uri' => env('APP_URL').'/oauth_callback',
            'code' => $code,
        ], [], false);

        if($response and isset($response['access_token']) and $response['access_token'])
        {
            return $response['access_token'];
        }

        return null;
    }

    /**
     * @return mixed
     * @throws GuzzleException
     */
    public function getUserByToken()
    {
        $response = $this->gatewayApi->request($this->authAppCode , 'get','api/user',  [], [
            'Authorization' => 'Bearer ' .session()->get('access_token')
        ], false);

        $user = $this->gatewayApi->getData($response);

        return $user;
    }
}