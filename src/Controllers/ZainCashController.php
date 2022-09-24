<?php

namespace Hawkiq\LaravelZaincash\Controllers;

use Hawkiq\LaravelZaincash\Services\ZainCash;

class ZainCashController
{
    public function redirect(ZainCash $zaincash)
    {
        $token = \Request::input('token');
        if (isset($token)) {
            $zaincash = new ZainCash();
            return $zaincash->checkToken($token);
        }
    }
}
