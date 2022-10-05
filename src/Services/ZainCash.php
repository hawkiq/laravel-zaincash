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

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;

class ZainCash
{
    private int $amount; //Please note that it MUST BE MORE THAN 1000 IQD
    private string $serviceType; //Type of service you provide, like 'Books', 'ecommerce cart', 'Hosting services', ...
    private string $orderId; //Order id, you can use it to help you in tagging transactions with your website IDs, if you have no order numbers in your website, leave it 1
    //Variable Type is STRING, MAX: 512 chars


    public function request(int $amount, string $serviceType, string $orderId)
    {
        $this->amount = $amount;
        $this->serviceType = $serviceType;
        $this->orderId = $orderId;

        if ($this->validate()->error == 'true') {
            throw new Exception($this->validate()->msg);
        }
        $token = $this->sign($this->organizeData());
        $context = $this->preparePostToZainCash($token);
        $response = $this->sendRequest($context);
        $parsedResponse = $this->parseResponse($response);
        $handledData = $this->handleResponse($parsedResponse);
        return $handledData->error != 'true' ? redirect()->away($handledData->gotoUrl) : $handledData->msg;
    }

    private function validateMsisdn()
    {
        return preg_match('/^[0-9]{13}+$/', config('zaincash.msisdn'));
    }

    private function validate()
    {
        if (!$this->validateMsisdn()) {
            return $this->prepareOutput(["error" => 'true', "msg" => "Msisdn Phone number in Config is Invalid"]);
        }

        if ($this->amount <= 999 || !isset($this->amount)) {
            return $this->prepareOutput(["error" => 'true', "msg" => "Amount must Be Larger than 1000 IQD"]);
        }
        if ($this->serviceType == null || !isset($this->serviceType)) {
            return $this->prepareOutput(["error" => 'true', "msg" => "You must Specify Service Type ex : Shirt"]);
        }
        if ($this->orderId == null || !isset($this->orderId)) {
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
            'redirectUrl'  => route(config('zaincash.redirection_url')),
            'iat'  => time(),
            'exp'  => time() + 60 * 60 * 4
        ];
    }

    private function sign(array $data)
    {
        return JWT::encode(
            $data,
            config('zaincash.secret'),
            'HS256'
        );
    }

    private function preparePostToZainCash($token)
    {
        return [
            'token' => urlencode($token),
            'merchantId' => config('zaincash.merchantid'),
            'lang' => config('zaincash.lang'),
        ];
    }

    private function sendRequest($context)
    {
        try {
            $response = Http::asForm()
            ->post(config('zaincash.live', 'false') == 'false' ? Local::tUrl() : Live::tUrl(),$context);
            return $response;
        } catch (\Throwable $th) {
            throw $th->getMessage();
        }
    }

    private function parseResponse($response)
    {
        return json_decode($response);
    }

    private function handleResponse($parsedResponse)
    {
        if (!isset($parsedResponse->id)) {
            throw new Exception($parsedResponse->err->msg);
        }
        return $this->prepareOutput([
            'error' => 'false',
            'payload' => $parsedResponse,
            'gotoUrl' => $this->createUrl($parsedResponse->id),
            'transactionStatus' => $parsedResponse->status
        ]);
    }

    private function createUrl(string $transactionID)
    {
        $apiUrl = config('zaincash.live', 'false') == 'false' ? Local::rUrl() : Live::rUrl();
        return  $apiUrl . $transactionID;
    }

    public function parse($token)
    {
        $result = JWT::decode($token, new Key(config('zaincash.secret'), 'HS256'));
        return $this->prepareOutput($result);
    }

    private function prepareOutput($array)
    {
        return json_decode(json_encode($array), FALSE);
    }
}
