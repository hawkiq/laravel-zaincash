<?php

namespace Hawkiq\LaravelZaincash\Services;

class Live
{
    public static function tUrl()
    {
        return 'https://api.zaincash.iq/transaction/init';
    }

    public static function rUrl()
    {
        return 'https://api.zaincash.iq/transaction/pay?id=';
    }
}
