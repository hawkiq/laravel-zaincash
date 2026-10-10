# hawkiq Laravel ZainCash (v2 API)

<p align="center">
<a href="https://zaincash.iq" target="_blank"><img src="https://docs.zaincash.iq/_nuxt/buttonSize.JuZ5HSuA.png" width="75"></a>
<a href="https://osama.app" target="_blank"><img src="https://osama.app/assets/img/logo-osama.png" width="75"></a>
</p>

<p align="center">
<a href="https://packagist.org/packages/hawkiq/laravel-zaincash"><img src="https://img.shields.io/packagist/dt/hawkiq/laravel-zaincash" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/hawkiq/laravel-zaincash"><img src="https://img.shields.io/packagist/v/hawkiq/laravel-zaincash" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/hawkiq/laravel-zaincash"><img src="https://img.shields.io/packagist/l/hawkiq/laravel-zaincash" alt="License"></a>
</p>

## About

ZainCash provides a robust payment gateway to transfer money instantly inside Iraq. This package offers a clean and simple integration for Laravel developers, fully upgraded to support the **ZainCash Payment Gateway API v2** (featuring OAuth2 authentication, structured JSON payloads, secure token verification, transaction inquiries, and full/partial reversals).

---

## Installation

Require the package via Composer[cite: 8]:

```bash
composer require hawkiq/laravel-zaincash

```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="zaincash"

```

### Environment Variables (`.env`)

Add your ZainCash v2 client credentials to your `.env` file:

```env
ZCASH_CLIENT_ID=your_client_id_here
ZCASH_CLIENT_SECRET=your_client_secret_here
ZCASH_LIVE=false
ZCASH_LANG=en
ZCASH_CALLBACK_ROUTE=payment.callback

```

### Configuration Options (`config/zaincash.php`)

| Config | Description | Type | Default |
| --- | --- | --- | --- |
| `client_id` | OAuth2 Client ID provided during ZainCash v2 onboarding | `string` | `''` |
| `client_secret` | OAuth2 Client Secret (also used as the signature key for callbacks) | `string` | `''` |
| `live` | Environment toggle (`true` for production, `false` for UAT test)| `bool` | `false` |
| `lang` | Language for the payment gateway page (`en` or `ar`)| `string` | `en` |
| `order_id` | Prefix used to tag transactions with your system order IDs| `string` | `Str::slug(env('APP_NAME')) . '_'` |
| `redirection_url` | Named route name to handle the payment callback response| String | `payment.callback` |

---

## Usage

### 1. Initiating a Payment

In your controller, you can initialize a transaction using web redirection mode or API payload mode. You can also pass or fluently set the customer's phone number.

```php
use Hawkiq\LaravelZaincash\Services\ZainCash;
use Illuminate\Http\Request;

public function sendPayment()
{
    $zaincash = new ZainCash();

    // Optional: Set the customer's phone number if collected during checkout
    $zaincash->setCustomerPhone('9647829744432');

    // Total price in Iraqi Dinars (Must be >= 1000 IQD)
    $amount = 5000;

    // Type of service provided
    $serviceType = 'E-commerce Order';

    // Unique order ID on your website
    $orderId = '12345';

    // Redirects the user directly to the ZainCash gateway (default asWeb = true)
    return $zaincash->request($amount, $serviceType, $orderId);

    // OR 
    // return $zainCash->request(
    //         amount: $amount,
    //         serviceType:  $serviceType,
    //         orderId: $orderId,
    //         customerPhone: '9647829744432'
    //     );
}

```

---

### 2. Handling the Callback & Verifying the Token

When a payment completes, ZainCash redirects the user back to your `redirection_url` with a JWT `token` query parameter. Use the `parse()` method to securely verify the signature and extract transaction details.

```php
use Illuminate\Http\Request;
use Hawkiq\LaravelZaincash\Services\ZainCash;

public function handleCallback(Request $request, ZainCash$zainCash)
{
    if (!$request->has('token')) {
        return response()->json(['error' => 'No payment token provided'], 400);
    }

    $result = $zainCash->parse($request->query('token'));

    if ($result['error']) {
        return response()->json(['error' => $result['message']], 422);
    }

    // Extracted verified transaction data safely
    $status        =$result['status'];          // e.g., 'SUCCESS', 'FAILED'
    $transactionId =$result['transactionId'];   // ZainCash global transaction ID
    $orderId       =$result['orderId'];         // Your system order ID
    $amount        =$result['amount'];          // Transaction amount

    if ($status === 'SUCCESS') {
        // TODO: Mark your local order as paid.
        // CRITICAL: Save the $transactionId so you can use it for refunds later!
        
        return view('payment.success', compact('orderId', 'transactionId', 'amount'));
    }

    return view('payment.failure', compact('orderId', 'status'));
}

```

---

### 3. Advanced Features: Inquiry & Reversals (Refunds)

The v2 package includes built-in methods for tracking and refunding payments:

#### Transaction Inquiry (Reconciliation)

```php
$zainCash = new ZainCash();
$details =$zainCash->inquiry('a801492a-f3be-477f-97ba-086adeec1726');

```

#### Full Refund (Reverse)

```php
$zainCash = new ZainCash();
$response =$zainCash->fullReverse('a801492a-f3be-477f-97ba-086adeec1726', 'Customer cancellation');

```

#### Partial Refund

```php
$zainCash = new ZainCash();
// Refund 2000 IQD from the original transaction
$response =$zainCash->partialReverse('a801492a-f3be-477f-97ba-086adeec1726', 2000, 'Partial item return');

```

---

## Security Vulnerabilities

If you discover a security vulnerability within hawkiq Laravel Zaincash, please send an e-mail to OsaMa via [info@osama.app](https://www.google.com/search?q=mailto%3Ainfo%40osama.app). All security vulnerabilities will be promptly addressed.

## License

Laravel Zaincash is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


