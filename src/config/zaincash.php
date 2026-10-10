<?php

use Illuminate\Support\Str;

return [
    /*
    |--------------------------------------------------------------------------
    | OAuth Required to authenticate with ZainCash Servers
    |--------------------------------------------------------------------------
    |
    */

    'client_id' => env('ZCASH_CLIENT_ID', '758055f4a8044779a35f6ceb69f858b3'),
    'client_secret' => env('ZCASH_CLIENT_SECRET', 'bibLCGTxVAig5To3OLLKPJQMlRR7Pefp'),

    /*
    |--------------------------------------------------------------------------
    | API Enviroment
    |--------------------------------------------------------------------------
    |
    | Enviroment for using Zain cash API.
    | false for test enviroment | true for live after you getting all cred. from zain cash
    */
    'live' => env('ZCASH_LIVE', false),

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    |
    | setting langauge for zain cashe payment page.
    | ar for Arabic | en  for English
    */
    'lang' => env('ZCASH_LANG', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Order ID
    |--------------------------------------------------------------------------
    |
    | Order_id, you can use it to help you in tagging transactions with your website IDs.
    | if you have no order numbers in your website, leave it 1.
    | Order will be like this "laravel_hawkiq_xxxxxxx".
    */
    'order_id' => Str::slug(env('APP_NAME')) . '_hawkiq_',

    /*
    |--------------------------------------------------------------------------
    | Redirect Url
    |--------------------------------------------------------------------------
    |
    | First you need to Specify name for redirect route in web.php.
    | then put name in Zaincash config file.
    | redirect.
    */
    'redirection_url' => env('ZCASH_CALLBACK_ROUTE', 'payment.callback'),
];
