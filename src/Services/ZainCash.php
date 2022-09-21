<?php

namespace Hawkiq\LaravelZaincash\Services;

use Firebase\JWT\JWT;

class ZainCash
{
    public $amount;
    public $serviceType;
    public $orderId;

    public function __construct()
    {
        $this->amount = 0;
        $this->serviceType = '';
        $this->orderId = '';
    }

    public function request($amount, $service_type, $order_id)
    {
        $this->amount = $amount;
        $this->serviceType = $service_type;
        $this->orderId = $order_id;
        if ($this->validate()['error'] == 'true') {
            return $this->validate();
        }
        $token = $this->sign($this->organizeData());
        $context = $this->preparePostToZainCash($token);
        $response = $this->sendRequest($context);
        $parsedResponse = $this->parseResponse($response);
        if (!isset($parsedResponse->id)) {
            return ["error" => 'true', "msg" => $parsedResponse->err->msg];
        }
        $payload =  [
            'error' => 'false',
            'payload' => $parsedResponse,
            'gotoUrl' => $this->createUrl($parsedResponse->id),
            'transactionStatus' => $parsedResponse->status
        ];
        return json_decode (json_encode ($payload), FALSE);
    }

    private function validate()
    {
        if ($this->amount <= 999 || empty($this->amount)) {
            return ["error" => 'true', "msg" => "Amount must Be Larger than 1000 IQD"];
        }
        if ($this->serviceType == null || empty($this->serviceType)) {
            return ["error" => 'true', "msg" => "You must Specify Service Type ex : Shirt"];
        }
        if ($this->orderId == null || empty($this->orderId)) {
            return ["error" => 'true', "msg" => "Must specify Order ID which act as recipe ID ex : 20222009"];
        }
        return ["error" => 'false', "msg" => "OK"];
    }

    private function organizeData()
    {
        return [
            'amount'  => $this->amount,
            'serviceType'  => $this->serviceType,
            'msisdn'  => config('zaincash.msisdn'),
            'orderId'  =>  config('zaincash.order_id') . $this->orderId,
            'redirectUrl'  => url(config('zaincash.redirection_url')),
            'iat'  => time(),
            'exp'  => time() + 60 * 60 * 4
        ];
    }

    private function sign($data)
    {
        return JWT::encode(
            $data,      //Data to be encoded in the JWT
            config('zaincash.secret'),
            'HS256'
        );
    }

    private function preparePostToZainCash($token)
    {
        $data_to_post = array();
        $data_to_post['token'] = urlencode($token);
        $data_to_post['merchantId'] = config('zaincash.merchantid');
        $data_to_post['lang'] = config('zaincash.lang');
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data_to_post),
            ),
        );
        return stream_context_create($options);
    }

    private function sendRequest($context)
    {
        try {
            $response = file_get_contents(config('zaincash.live', 'false') == 'false' ? Local::tUrl() : Live::tUrl(), false, $context);
        } catch (\Throwable $th) {
            throw $th;
        }
        return $response;
    }

    private function parseResponse($response)
    {
        //Parsing response
        return json_decode($response);
    }

    private function createUrl($transactionID)
    {
        $apiUrl = config('zaincash.live', 'false') == 'false' ? Local::rUrl() : Live::rUrl();
        return  $apiUrl . $transactionID;
    }
}
