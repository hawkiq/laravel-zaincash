<?php

namespace Hawkiq\LaravelZaincash\Services;

class Local
{
    public static function tUrl()
    {
        return 'https://test.zaincash.iq/transaction/init';
    }

    public static function rUrl()
    {
        return 'https://test.zaincash.iq/transaction/pay?id=';
    }
}
