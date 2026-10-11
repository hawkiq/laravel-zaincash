<?php

namespace Hawkiq\LaravelZaincash\Actions;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class InitializePayment
{
    public function handle(int $amount, string $serviceType, string $orderId, ?string $customerPhone = null, bool $asWeb = true)
    {
        if ($amount < 1000) {
            throw new Exception('Amount must be larger than 1000 IQD');
        }

        $token = AuthenticateZainCash::getToken();

        $payload = [
            'language' => config('zaincash.lang', 'en'),
            'externalReferenceId' => (string) Str::uuid(),
            'orderId' => config('zaincash.order_id') . $orderId,
            'amount' => [
                'value' => (string) $amount,
                'currency' => 'IQD',
            ],
            'serviceType' => $serviceType,
            'redirectUrls' => [
                'successUrl' => route(config('zaincash.redirection_url')),
                'failureUrl' => route(config('zaincash.redirection_url')),
            ],
        ];

        if ($customerPhone) {
            $payload['customer'] = [
                'phone' => $customerPhone,
            ];
        }

        $response = Http::withToken($token)
            ->post(AuthenticateZainCash::getBaseUrl() . '/api/v2/payment-gateway/transaction/init', $payload);

        if ($response->failed()) {
            throw new Exception('ZainCash Init Error: ' . $response->body());
        }

        $data = $response->json();

        if ($asWeb) {
            return redirect()->away($data['redirectUrl']);
        }

        return [
            'payment_url' => $data['redirectUrl'],
            'payload' => $data,
            'status' => $data['status'] ?? 'PENDING',
        ];
    }
}
