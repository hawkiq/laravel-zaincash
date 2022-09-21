<?php

use Illuminate\Support\Str;

return [
    //The mobile phone number for your wallet, example format: 9647835077893. given by Zain Cash
    'msisdn' => '9647835077893', // This is for test only its working for test enviroment


    //This is used to decode and encode JWT during requests. also must be requested from ZainCash.
    'secret' => '$2y$10$hBbAZo2GfSSvyqAyV2SaqOfYewgYpfR1O19gIh4SqyGWdmySZYPuS',// This is for test only its working for test enviroment


    //You can request a Merchant ID from ZainCash's support.
    'merchantid' => '5ffacf6612b5777c6d44266f',// This is for test only its working for test enviroment


    //Test enviroment or Live server (true=live , false=test)
    'live' => 'false',


    //Language 'ar'=Arabic     'en'=english
    'lang' => 'en',


    //Order_id, you can use it to help you in tagging transactions with your website IDs, if you have no order numbers in your website, leave it 1
    //Variable Type is STRING, MAX: 512 chars
    'order_id' => Str::slug(env('APP_NAME')) . '_Support_', // Order will be like this "laravel_Support_xxxxxxx"


    //after a successful or failed order, the user will redirect to this url 
    'redirection_url' => 'redirect', // use route name in web.php ->name('redirect')
];
