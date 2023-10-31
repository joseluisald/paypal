<?php

namespace Joseluisald\Paypal;

use Exception;
use Joseluisald\Paypal\Request;
use stdClass;

/**
 * Class Payment
 */
Class Payment extends Request
{
    /**
     * @var $error
     */
    private $error;
    /**
     * @var $response
     */
    private $response;
    /**
     * @var $url
     */
    private $url;
    /**
     * @var $secret
     */
    private $secret;
    /**
     * @var $clientId
     */
    private $clientId;
    /**
     * @var $accessToken
     */
    private $accessToken;
    /**
     * @var $accessToken
     */
    private $clientToken;
    /**
     * @var $tokenType
     */
    private $tokenType;
    /**
     * @var $appId
     */
    private $appId;
    /**
     * @var $cardId
     */
    private $cardId;
    /**
     * @var $dataDebug
     */
    private $dataDebug;


    /**
     * Payment constructor.
     * @param String $type
     * @param String $clientId
     * @param String $secret
     * @throws \Exception
     */
    public function __construct(String $type, String $clientId, String $secret)
    {

        if(!empty($type) && !empty($clientId) && !empty($secret))
        {
            parent::__construct($type);
            switch($type)
            {
                case 'Sandbox':
                    $this->url = 'https://api-m.sandbox.paypal.com';
                    break;
                case 'Live':
                    $this->url = 'https://api-m.paypal.com';
                    break;
            }
            $this->secret = base64_encode($clientId.':'.$secret);
            $this->clientId = $clientId;

            if($this->getAccessToken())
                $this->getClientToken();
        }
        else
        {
            throw new \Exception("Todos os dados do método construtor são obrigatórios!");
        }
    }

    /**
     * @return bool
     */
    private function getAccessToken()
    {
        $data = http_build_query(['grant_type' => 'client_credentials']);

        $postAPI = $this->postAPI('/v1/oauth2/token', $data, 'Basic', $this->secret, 'application/x-www-form-urlencoded');

        if($postAPI->response)
        {
            $this->accessToken = $postAPI->response->access_token;
            $this->tokenType = $postAPI->response->token_type;
            $this->appId = $postAPI->response->app_id;
            return true;
        }
        if($postAPI->error)
        {
            $this->error = $postAPI->error;
            return false;
        }
    }

    /**
     * @return bool
     */
    private function getClientToken()
    {

        $postAPI = $this->postAPI('/v1/identity/generate-token', [], $this->tokenType, $this->accessToken);

        if($postAPI->response)
        {
            $this->clientToken = $postAPI->response->client_token;
            return true;
        }
        if($postAPI->error)
        {
            $this->error = $postAPI->error;
            return false;
        }
    }

    /**
     * @return bool|string
     */
    public function createOrder($data)
    {
        $postAPI = $this->postAPI('/v2/checkout/orders', $data, $this->tokenType, $this->accessToken);

        if($postAPI->response)
        {
            $this->response = $postAPI->response;
            return true;
        }
        if($postAPI->error)
        {
            $this->error = $postAPI->error;
            return false;
        }
    }

    /**
     * @return bool|int
     */
    public function saveCard($data)
    {
        $postAPI = $this->postAPI('/v3/vault/setup-tokens', $data, $this->tokenType, $this->accessToken);

        $this->dataDebug = $data;

        if($postAPI->response)
        {
            return $postAPI->response;
        }
        if($postAPI->error)
        {
            $this->error = $postAPI->error;
            return false;
        }
    }

    /**
     * @return bool
     */
    public function paymentToken($idCard)
    {
        $data = [
            "payment_source" => [
                "token" => [
                    "id" => $idCard,
                    "type" => "SETUP_TOKEN"
                ]
            ]
        ];

        $jsonSetup = json_encode($data);

        if ($jsonSetup === false)
        {
            return throw new Exception('Erro ao codificar em JSON: ' . json_last_error_msg());
        }
        else
        {
            $postAPI = $this->postAPI('/v3/vault/payment-tokens', $jsonSetup, $this->tokenType, $this->accessToken);

            if($postAPI->response)
            {
                return $postAPI->response;
            }
            if($postAPI->error)
            {
                $this->error = $postAPI->error;
                return false;
            }
        }
    }

    /**
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->clientToken;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->dataDebug;
    }
}