<?php

namespace Hawkiq\LaravelZaincash\Actions;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ParseCallback
{
    public function handle(string $token): array
    {
        try {
            $key = new Key(config('zaincash.client_secret'), 'HS256');
            $decoded = JWT::decode($token, $key);

            $data = $decoded->data ?? null;

            return [
                'error' => false,
                'eventType' => $decoded->eventType ?? null,
                'status' => $data->currentStatus ?? 'UNKNOWN',
                'transactionId' => $data->transactionId ?? null,
                'merchantReferenceId' => $data->merchantReferenceId ?? null,
                'orderId' => $data->orderId ?? null,
                'amount' => $data->amount->value ?? null,
                'feeValue' => $data->amount->feeValue ?? 0,
                'currency' => $data->amount->currency ?? 'IQD',
                'customerMsisdn' => $data->customerMsisdn ?? null,
                'payload' => $decoded,
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'JWT Verification Failed: '.$e->getMessage(),
            ];
        }
    }
}
