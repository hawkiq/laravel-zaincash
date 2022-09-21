<?php

use Illuminate\Support\Str;

return [
    'msisdn' => '9647835077893',
    'secret' => '$2y$10$hBbAZo2GfSSvyqAyV2SaqOfYewgYpfR1O19gIh4SqyGWdmySZYPuS',
    'merchantid' => '5ffacf6612b5777c6d44266f',
    'live' => 'false',
    'lang' => 'en',
    'order_id' => Str::slug(env('APP_NAME')) . '_Support_',
    'redirection_url' => 'redirect',
];
