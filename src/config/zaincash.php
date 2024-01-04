<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Wallet phone Number
    |--------------------------------------------------------------------------
    |
    | The mobile phone number for your wallet, example format: 9647835077893. given by Zain Cash
    | current number is for test only its working for test enviroment
    */
    'msisdn' => env('ZCASH_MSISDN', '9647835077893'), 

    /*
    |--------------------------------------------------------------------------
    | Secret Hash
    |--------------------------------------------------------------------------
    |
    | This is used to decode and encode JWT during requests. also must be requested from ZainCash.
    | current secret is for test only its working for test enviroment
    */
    'secret' => env('ZCASH_SECRET', '$2y$10$hBbAZo2GfSSvyqAyV2SaqOfYewgYpfR1O19gIh4SqyGWdmySZYPuS'),

    /*
    |--------------------------------------------------------------------------
    | Merchant ID
    |--------------------------------------------------------------------------
    |
    | You can request a Merchant ID from ZainCash's support.
    | current merchantid is for test only its working for test enviroment
    */
    'merchantid' => env('ZCASH_MERCHANTID','5ffacf6612b5777c6d44266f'),

    /*
    |--------------------------------------------------------------------------
    | API Enviroment
    |--------------------------------------------------------------------------
    |
    | Enviroment for using Zain cash API.
    | false for test enviroment | true for live after you getting all cred. from zain cash
    */
    'live' => false,

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    |
    | setting langauge for zain cashe payment page.
    | ar for Arabic | en  for English
    */
    'lang' => 'en',

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
    'redirection_url' => 'redirect',
];
