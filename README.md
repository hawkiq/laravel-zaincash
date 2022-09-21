# hawkiq Laravel Zain Cash Package

## Installation

```
composer require hawkiq/laravel-zaincash
```

## Publish config file

```
php artisan vendor:publish --tag="zaincash"
```

all config variables are well described

```php

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
```

in your controller init the class

```php
use Hawkiq\LaravelZaincash\Services\ZainCash;

$zaincash = new ZainCash();
//The total price of your order in Iraqi Dinar only like 1000 (if in dollar, multiply it by dollar-dinar exchange rate, like 1*1300=1300)
//Please note that it MUST BE MORE THAN 1000 IQD
$amount = 1000;

//Type of service you provide, like 'Books', 'ecommerce cart', 'Hosting services', ...
$service_type="Shirt";

//Order id, you can use it to help you in tagging transactions with your website IDs, if you have no order numbers in your website, leave it 1
//Variable Type is STRING, MAX: 512 chars
$order_id="20222009";



$payload =  $zaincash->request($amount,$service_type,$order_id);
```

now we check if there are no errors in our request then we redirect to Zain Cash Website

```php
return $payload->error != 'true' ? redirect()->away($payload->gotoUrl) : $payload->msg;
```

which `$payload->gotoUrl` is the Url for Zain cash Website

so if there are no error we redirect

```php
return redirect()->away($payload->gotoUrl);
```

if the response was successful either transaction was success or failed.
the api will add a new parameter (token) to its end like:
https://example.com/redirect?token=XXXXXXXXXXXXXX

so we check using

```php
$token = $request->input('token');
if (isset($token)) {
    $zaincash = new ZainCash();
    $result =  $zaincash->checkToken($token);
}
```

we check for

```php
$result->status == 'success' // or failed
```

and we can do what ever you like , insert transaction into database, send email etc..

## Security Vulnerabilities

If you discover a security vulnerability within Larapsn, please send an e-mail to OsaMa via [info@osama.app](mailto:info@osama.app). All security vulnerabilities will be promptly addressed.

## Preview

this class used in following sites

- [O! Software](https://osama.app).

feel free to contact me if you want to add your site.

## License

Larapsn is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
