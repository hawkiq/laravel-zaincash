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
use Illuminate\Support\Str;

class ZainCash
{
    private int $amount;

    private string $serviceType;

    private string $orderId;

    private ?string $customerPhone = null;

    private bool $asWeb = true;

    public function asWeb(bool $value = true): self
    {
        $this->asWeb = $value;

        return $this;
    }

    public function asApi(): self
    {
        $this->asWeb = false;

        return $this;
    }

    public function setCustomerPhone(string $phone): self
    {
        $this->customerPhone = $phone;

        return $this;
    }

    private function getBaseUrl(): string
    {
        return config('zaincash.live', false) === false
            ? 'https://pg-api-uat.zaincash.iq'
            : 'https://pg-api.zaincash.iq';
    }

    private function getAccessToken(): string
    {
        $response = Http::asForm()->post($this->getBaseUrl().'/oauth2/token', [
            'grant_type' => 'client_credentials',
            'client_id' => config('zaincash.client_id'),
            'client_secret' => config('zaincash.client_secret'),
            'scope' => 'payment:read payment:write reverse:write reverse:read disbursement:read disbursement:write',
        ]);

        if ($response->failed() || ! isset($response->json()['access_token'])) {
            throw new Exception('Failed to obtain ZainCash OAuth2 token: '.$response->body());
        }

        return $response->json()['access_token'];
    }

    /**
     * Initialize a payment transaction.
     */
    public function request(int $amount, string $serviceType, string $orderId, ?string $customerPhone = null)
    {
        $this->amount = $amount;
        $this->serviceType = $serviceType;
        $this->orderId = $orderId;

        $phone = $customerPhone ?? $this->customerPhone;

        if ($this->amount < 1000) {
            throw new Exception('Amount must be larger than 1000 IQD');
        }

        $token = $this->getAccessToken();

        $payload = [
            'language' => config('zaincash.lang', 'en'),
            'externalReferenceId' => (string) Str::uuid(),
            'orderId' => config('zaincash.order_id').$this->orderId,
            'amount' => [
                'value' => (string) $this->amount,
                'currency' => 'IQD',
            ],
            'serviceType' => $this->serviceType,
            'redirectUrls' => [
                'successUrl' => route(config('zaincash.redirection_url')),
                'failureUrl' => route(config('zaincash.redirection_url')),
            ],
        ];

        if ($phone) {
            $payload['customer'] = [
                'phone' => $phone,
            ];
        }

        $response = Http::withToken($token)
            ->post($this->getBaseUrl().'/api/v2/payment-gateway/transaction/init', $payload);

        if ($response->failed()) {
            throw new Exception('ZainCash Init Error: '.$response->body());
        }

        $data = $response->json();

        if ($this->asWeb) {
            return redirect()->away($data['redirectUrl']);
        }

        return [
            'payment_url' => $data['redirectUrl'],
            'payload' => $data,
            'status' => $data['status'] ?? 'PENDING',
        ];
    }

    /**
     * Verify and decode the incoming callback JWT token from ZainCash.
     */
    public function parse(string $token): array
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

    /**
     * Retrieve transaction status and details (Critical for fallback reconciliation).
     */
    public function inquiry(string $transactionId)
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->get($this->getBaseUrl()."/api/v2/payment-gateway/transaction/inquiry/{$transactionId}");

        if ($response->failed()) {
            throw new Exception('ZainCash Inquiry Error: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Full Refund (Reverse) Support.
     */
    public function fullReverse(string $transactionId, ?string $reason = null)
    {
        $token = $this->getAccessToken();
        $payload = ['transactionId' => $transactionId];

        if ($reason) {
            $payload['reason'] = substr(trim($reason), 0, 44); // Max 44 chars restriction
        }

        $response = Http::withToken($token)
            ->post($this->getBaseUrl().'/api/v2/payment-gateway/transaction/reverse', $payload);

        if ($response->failed()) {
            throw new Exception('ZainCash Full Reverse Error: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Partial Refund Support.
     */
    public function partialReverse(string $transactionId, int $amount, ?string $reason = null)
    {
        $token = $this->getAccessToken();
        $payload = [
            'transactionId' => $transactionId,
            'amount' => $amount,
        ];

        if ($reason) {
            $payload['reason'] = substr(trim($reason), 0, 44);
        }

        $response = Http::withToken($token)
            ->post($this->getBaseUrl().'/api/v2/payment-gateway/transaction/partial-reverse', $payload);

        if ($response->failed()) {
            throw new Exception('ZainCash Partial Reverse Error: '.$response->body());
        }

        return $response->json();
    }
}
