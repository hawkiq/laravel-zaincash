<?php

 /*
    |--------------------------------------------------------------------------
    | Laravel Zain Cash
    |--------------------------------------------------------------------------
    |
    | Laravel package by @hawkiq
    | any issues : https://github.com/hawkiq/laravel-zaincash
    */

namespace Hawkiq\LaravelZaincash\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ZainCash
{
    public $amount; //Please note that it MUST BE MORE THAN 1000 IQD
    public $serviceType; //Type of service you provide, like 'Books', 'ecommerce cart', 'Hosting services', ...
    public $orderId; //Order id, you can use it to help you in tagging transactions with your website IDs, if you have no order numbers in your website, leave it 1
    //Variable Type is STRING, MAX: 512 chars

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

        if ($this->validate()->error == 'true') {
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
        $output = $this->prepareOutput($payload);
        return $output->error != 'true' ? redirect()->away($output->gotoUrl) : $output->msg;
    }

    private function validateMsisdn()
    {
        return preg_match('/^[0-9]{13}+$/', config('zaincash.msisdn'));
    }

    private function validate()
    {
        if (!$this->validateMsisdn()) {
            return $this->prepareOutput(["error" => 'true', "msg" => "Phone number in Config is Invalid"]);
        }

        if ($this->amount <= 999 || empty($this->amount)) {
            return $this->prepareOutput(["error" => 'true', "msg" => "Amount must Be Larger than 1000 IQD"]);
        }
        if ($this->serviceType == null || empty($this->serviceType)) {
            return $this->prepareOutput(["error" => 'true', "msg" => "You must Specify Service Type ex : Shirt"]);
        }
        if ($this->orderId == null || empty($this->orderId)) {
            return $this->prepareOutput(["error" => 'true', "msg" => "Must specify Order ID which act as recipe ID ex : 20222009"]);
        }
        return $this->prepareOutput(["error" => 'false', "msg" => "OK"]);
    }

    private function organizeData()
    {
        return [
            'amount'  => $this->amount,
            'serviceType'  => $this->serviceType,
            'msisdn'  => config('zaincash.msisdn'),
            'orderId'  =>  config('zaincash.order_id') . $this->orderId,
            'redirectUrl'  => route('redirect'),
            'iat'  => time(),
            'exp'  => time() + 60 * 60 * 4
        ];
    }

    //Encoding Token
    private function sign($data)
    {
        return JWT::encode(
            $data,      //Data to be encoded in the JWT
            config('zaincash.secret'),
            'HS256'
        );
    }

    //Preparing data to ZainCash API
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

    //POSTing data to ZainCash API
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
        return json_decode($response);
    }

    private function createUrl($transactionID)
    {
        $apiUrl = config('zaincash.live', 'false') == 'false' ? Local::rUrl() : Live::rUrl();
        return  $apiUrl . $transactionID;
    }

    public function checkToken($token)
    {
        //to check for status of the transaction, use $result->status
        $result = JWT::decode($token, new Key(config('zaincash.secret'), 'HS256'));
        return $this->prepareOutput($result);
    }

    private function prepareOutput($array)
    {
        return json_decode(json_encode($array), FALSE);
    }
}
