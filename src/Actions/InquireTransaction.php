<?php

namespace Hawkiq\LaravelZaincash\Actions;

use Exception;
use Illuminate\Support\Facades\Http;

class InquireTransaction
{
    public function handle(string $transactionId)
    {
        $token = AuthenticateZainCash::getToken();
        $response = Http::withToken($token)
            ->get(AuthenticateZainCash::getBaseUrl()."/api/v2/payment-gateway/transaction/inquiry/{$transactionId}");

        if ($response->failed()) {
            throw new Exception('ZainCash Inquiry Error: '.$response->body());
        }

        return $response->json();
    }
}
