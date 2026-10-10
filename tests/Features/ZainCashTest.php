<?php

use Firebase\JWT\JWT;
use Hawkiq\LaravelZaincash\Services\ZainCash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $longSecret = 'bibLCGTxVAig5To3OLLKPJQMlRR7Pefp';
    config([
        'zaincash.client_id' => '758055f4a8044779a35f6ceb69f858b3',
        'zaincash.client_secret' => $longSecret,
        'zaincash.live' => false,
        'zaincash.lang' => 'en',
        'zaincash.order_id' => 'test_app_',
        'zaincash.redirection_url' => 'payment.callback',
    ]);
    Route::get('/fake-callback', function () {
        return 'ok';
    })->name('payment.callback');
});

it('throws an exception if the amount is less than 1000 IQD', function () {
    $zainCash = new ZainCash;

    // Amount must be >= 1000
    $zainCash->request(500, 'Test Service', '12345');
})->throws(Exception::class, 'Amount must be larger than 1000 IQD');

it('successfully initializes a payment transaction', function () {
    // Mock both the OAuth token endpoint and the transaction init endpoint
    Http::fake([
        '*/oauth2/token' => Http::response([
            'access_token' => 'mocked_oauth_token_123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 200),
        '*/api/v2/payment-gateway/transaction/init' => Http::response([
            'redirectUrl' => 'https://pg-api-uat.zaincash.iq/transaction/pay?id=mocked_id',
            'status' => 'PENDING',
        ], 200),
    ]);

    $zainCash = new ZainCash;
    $result = $zainCash->asApi()->request(5000, 'Hosting', '12345');

    expect($result)
        ->toBeArray()
        ->toHaveKey('payment_url')
        ->toHaveKey('status', 'PENDING');

    expect($result['payment_url'])->toContain('mocked_id');
});

it('successfully parses and verifies a valid callback JWT token', function () {
    $secret = config('zaincash.client_secret');

    // Create a mock signed JWT token mimicking ZainCash callback response
    $payload = [
        'eventType' => 'STATUS_CHANGED',
        'data' => [
            'transactionId' => 'a801492a-f3be-477f-97ba-086adeec1726',
            'merchantReferenceId' => 'bedd7904-d501-43f5-912f-6e59b795586e',
            'orderId' => 'test_app_12345',
            'currentStatus' => 'SUCCESS',
            'amount' => [
                'currency' => 'IQD',
                'value' => 5000,
                'feeValue' => 150,
            ],
            'customerMsisdn' => '9647829744432',
        ],
    ];

    $jwtToken = JWT::encode($payload, $secret, 'HS256');

    $zainCash = new ZainCash;
    $parsed = $zainCash->parse($jwtToken);

    expect($parsed)
        ->toBeArray()
        ->toHaveKey('error', false)
        ->toHaveKey('status', 'SUCCESS')
        ->toHaveKey('amount', 5000)
        ->toHaveKey('transactionId', 'a801492a-f3be-477f-97ba-086adeec1726');
});

it('fails when parsing a tampered or invalid JWT token', function () {
    $zainCash = new ZainCash;

    // Pass a fake/invalid token string
    $parsed = $zainCash->parse('invalid.jwt.token.string');

    expect($parsed)
        ->toBeArray()
        ->toHaveKey('error', true)
        ->toHaveKey('message');
});

it('successfully processes a full refund (reversal)', function () {
    Http::fake([
        '*/oauth2/token' => Http::response(['access_token' => 'mocked_token'], 200),
        '*/api/v2/payment-gateway/transaction/reverse' => Http::response([
            'status' => 'REVERSED',
        ], 200),
    ]);

    $zainCash = new ZainCash;
    $response = $zainCash->fullReverse('a801492a-f3be-477f-97ba-086adeec1726', 'Customer request');

    expect($response)->toHaveKey('status', 'REVERSED');
});
