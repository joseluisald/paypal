<?php

namespace Joseluisald\Paypal;

/**
 * Class Request
 */
class Request
{
    /**
     * @var $url
     */
    private $url;

    /**
     * Request constructor.
     * @param String $type
     */
    public function __construct(String $type)
    {
        switch($type)
        {
            case 'Sandbox':
                $this->url = 'https://api-m.sandbox.paypal.com';
                break;
            case 'Live':
                $this->url = 'https://api-m.paypal.com';
                break;
        }
    }

    /**
     * @param String $url
     * @param $data
     * @param String $content
     * @param String $tokenType
     * @param String $accessToken
     * @return \stdClass
     */
    public function postAPI(String $url, $data, String $tokenType, String $accessToken, String $content = 'application/json')
    {
        $curl = curl_init();

        $request_id = uniqid().strtotime("now");

        $httpHeader = array();
        $httpHeader[] = 'Content-Type: '.$content;
        $httpHeader[] = 'PayPal-Request-Id: '.$request_id;
        $httpHeader[] = 'Authorization: '.$tokenType.' '.$accessToken;

        $httpHeaderStrings = array_map('strval', $httpHeader);

        $postFields = !empty($data) ? $data : "";

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url.$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => $httpHeaderStrings,
        ));

        curl_close($curl);

        $response = new \stdClass();
        $response->response = json_decode(curl_exec($curl));
        $response->error = json_decode(curl_error($curl));
        return $response;
    }
}