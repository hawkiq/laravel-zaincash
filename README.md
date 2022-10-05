# hawkiq Laravel Zain Cash Package

<p align="center">
<a href="https://zaincash.iq" target="_blank"><img src="https://docs.zaincash.iq/assets/images/logo.png" width="100"></a>
<a href="https://osama.app" target="_blank"><img src="https://osama.app/build/assets/logo-osama.ead70dda.png" width="75"></a>
</p>

<p align="center">
<a href="https://packagist.org/packages/hawkiq/laravel-zaincash"><img src="https://img.shields.io/packagist/dt/hawkiq/laravel-zaincash" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/hawkiq/laravel-zaincash"><img src="https://img.shields.io/packagist/v/hawkiq/laravel-zaincash" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/hawkiq/laravel-zaincash"><img src="https://img.shields.io/packagist/l/hawkiq/laravel-zaincash" alt="License"></a>
</p>

## About hawkiq laravel zaincash

ZainCash offers a simple robust payment gateway to transfer money instantly from anywhere to everywhere inside Iraq, and this package for Laravel developers since there are no official package to use in Laravel so I've decided to create one.

## Requiremnt

| Version | PHP    | Laravel    |
| :---:   | :---: | :---: |
| 1.x | 7.1 &lt;= PHP  | 5.8 &lt;= Laravel   |

## Installation

```
composer require hawkiq/laravel-zaincash
```

## Publish config file

```
php artisan vendor:publish --tag="zaincash"
```

setup redirect route in web.php to handle payment result and put route name in zaincash.php config file

```php
Route::get('/redirect', [App\Http\Controllers\HomeController::class, 'redirect'])->name('redirect');
```


all config variables are well described

| config | Description    | Type    | Default    |
| :---:   | :---: | :---: | :---: |
| msisdn |The mobile phone number for your wallet, example format: 9647835077893. given by Zain Cash  | String  | 9647835077893  |
|   |  |  |  |
| secret | This is used to decode and encode JWT during requests. also must be requested from ZainCash    | String    | secret found in config    |
|   |  |  |  |
| merchantid | You can request a Merchant ID from ZainCash's support    | String    | 5ffacf6612b5777c6d44266f    |
|   |  |  |  |
| live | Test enviroment or Live server (true=live , false=test)    | Bool    | false    |
|   |  |  |  |
| lang | setting langauge for zain cashe payment page    | String    | en    |
|   |  |  |  |
| order_id | you can use it to help you in tagging transactions with your website IDs    | String    | `Str::slug(env('APP_NAME')) . '_hawkiq_'`    |
|   |  |  |  |
| redirection_url | to handle payment after successfull First you need to Specify name for redirect route in web.php    | String    | redirect    |



inside your controller

```php

//Your Controller 
use Hawkiq\LaravelZaincash\Services\ZainCash;

public function send()
{
    $zaincash = new ZainCash();
    //The total price of your order in Iraqi Dinar only like 1000 (if in dollar, multiply it by dollar-dinar exchange rate, like 1*1500=1500)
    //Please note that it MUST BE MORE THAN 1000 IQD
    $amount = 1000;

    //Type of service you provide, like 'Books', 'ecommerce cart', 'Hosting services', ...
    $service_type="Shirt";

    //Order id, you can use it to help you in tagging transactions with your website IDs, if you have no order numbers in your website, leave it 1
    $order_id="20222009";

    $payload =  $zaincash->request($amount, $service_type, $order_id);
    return $payload;
}

```

this will redirect us to Zain Cash page to enter user credentials ( MSISDN and Pin)

<img src="https://i.imgur.com/r62v9CV.png"/>

you can use this test user

| MSISDN | PIN    | OTP    |
| :---:   | :---: | :---: |
| 9647802999569 | 1234   | 1111   |


We check for status in our redirect method in controller

```php
//get token from request Url
$token = \Request::input('token');
    if (isset($token)) {
        $zaincash = new ZainCash();
        $result = $zaincash->parse($token);
        if ($result->status == 'success'){ // success ||  failed  || pending
            return 'Thanks for Buying';
            // We can do what ever you like , insert transaction into database, send email etc..
        }
```

result will be in JSON format like this 

```json
{
   "status":"success",
   "orderid":"laravel_hawkiq_20222009",
   "id":"632eb6cb8726f6b4b8ea2fc3",
   "operationid":"1006596",
   "msisdn":"9647802999569"
}
```

## Security Vulnerabilities

If you discover a security vulnerability within hawkiq Laravel Zaincash, please send an e-mail to OsaMa via [info@osama.app](mailto:info@osama.app). All security vulnerabilities will be promptly addressed.

## Preview

this class used in following sites

- [O! Software](https://osama.app).

feel free to contact me if you want to add your site.


## Todo

- <s>Add custom redirect route.</s>
- Add additional views for easy integration into blade.

## License

Laravel Zaincash is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
