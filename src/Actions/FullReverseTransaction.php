<?php

namespace Hawkiq\LaravelZaincash\Actions;

use Exception;
use Illuminate\Support\Facades\Http;

class FullReverseTransaction
{
    public function handle(string $transactionId, ?string $reason = null)
    {
        $token = AuthenticateZainCash::getToken();
        $payload = ['transactionId' => $transactionId];

        if ($reason) {
            $payload['reason'] = substr(trim($reason), 0, 44); // Max 44 chars restriction by ZainCash
        }

        $response = Http::withToken($token)
            ->post(AuthenticateZainCash::getBaseUrl() . '/api/v2/payment-gateway/transaction/reverse', $payload);

        if ($response->failed()) {
            throw new Exception('ZainCash Full Reverse Error: ' . $response->body());
        }

        return $response->json();
    }
}
