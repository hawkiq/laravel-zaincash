<?php

namespace Hawkiq\LaravelZaincash\Actions;

use Exception;
use Illuminate\Support\Facades\Http;

class AuthenticateZainCash
{
    public static function getBaseUrl(): string
    {
        return config('zaincash.live', false) === false
            ? 'https://pg-api-uat.zaincash.iq'
            : config('zaincash.live_url', 'https://pg-api.zaincash.iq');
    }

    public static function getToken(): string
    {
        $response = Http::asForm()->post(self::getBaseUrl().'/oauth2/token', [
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
}
