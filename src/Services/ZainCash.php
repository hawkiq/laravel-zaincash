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
use Illuminate\Support\Facades\Validator;

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
        $validation = $this->validate();
        if ($validation->error == 'true') {
            throw new Exception($validation->msg);
        }
        $token = $this->sign($this->organizeData());
        $context = $this->preparePostToZainCash($token);
        $response = $this->sendRequest($context);
        $parsedResponse = $this->parseResponse($response);
        $handledData = $this->handleResponse($parsedResponse);
        return $handledData->error != 'true' ? redirect()->away($handledData->gotoUrl) : $handledData->msg;
    }

    private function validate()
    {
        $validator = Validator::make([
            'msisdn' => config('zaincash.msisdn'),
            'amount' => $this->amount,
            'serviceType' => $this->serviceType,
            'orderId' => $this->orderId,
        ], [
            'msisdn' => ['required', 'regex:/^[0-9]{13}+$/'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'serviceType' => ['required'],
            'orderId' => ['required', 'string', 'max:512'],
        ],[
            'msisdn.regex*'=>'Msisdn Phone number in Config is Invalid',
            'amount.min*'=>'Amount must Be Larger than 1000 IQD',
            'serviceType.required'=>'You must Specify Service Type ex : Shirt',
            'orderId.required'=>'Must specify Order ID which act as recipe ID ex : 20222009',
        ]);

        if ($validator->fails()) {
            return $this->prepareOutput([
                "error" => 'true',
                "msg" => $validator->errors()->first(),
            ]);
        }

        return $this->prepareOutput([
            "error" => 'false',
            "msg" => "OK",
        ]);
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

    private function sendRequest(array $context)
    {
        try {
            $apiUrl = config('zaincash.live', false) === false ? Local::tUrl() : Live::tUrl();
            $response = Http::asForm()
                ->post($apiUrl, $context);

            return $response;
        } catch (Exception $exception) {
            throw $exception->getMessage();
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
        $apiUrl = config('zaincash.live', false) === false ? Local::rUrl() : Live::rUrl();
        return  $apiUrl . $transactionID;
    }

    public function parse(string $token)
    {
        $key = new Key(config('zaincash.secret'), 'HS256');
        $result = JWT::decode($token, $key);

        return $this->prepareOutput($result);
    }

    private function prepareOutput($array)
    {
        return json_decode(json_encode($array), FALSE);
    }
}
