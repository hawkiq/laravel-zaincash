<?php

namespace Hawkiq\LaravelZaincash\Services;

use Hawkiq\LaravelZaincash\Actions\FullReverseTransaction;
use Hawkiq\LaravelZaincash\Actions\InquireTransaction;
use Hawkiq\LaravelZaincash\Actions\InitializePayment;
use Hawkiq\LaravelZaincash\Actions\ParseCallback;
use Hawkiq\LaravelZaincash\Actions\PartialReverseTransaction;

class ZainCash
{
    private ?string $customerPhone = null;
    private bool $asWeb = true;

    public function asWeb(bool $value = true): self
    {
        $this->asWeb = $value;
        return $this;
    }

    public function asApi(): self
    {
        $this->asWeb = false;
        return $this;
    }

    public function setCustomerPhone(string $phone): self
    {
        $this->customerPhone = $phone;
        return $this;
    }

    public function request(int $amount, string $serviceType, string $orderId, ?string $customerPhone = null)
    {
        $phone = $customerPhone ?? $this->customerPhone;
        return (new InitializePayment)->handle($amount, $serviceType, $orderId, $phone, $this->asWeb);
    }

    public function parse(string $token): array
    {
        return (new ParseCallback)->handle($token);
    }

    public function inquiry(string $transactionId)
    {
        return (new InquireTransaction)->handle($transactionId);
    }

    public function fullReverse(string $transactionId, ?string $reason = null)
    {
        return (new FullReverseTransaction)->handle($transactionId, $reason);
    }

    public function partialReverse(string $transactionId, int $amount, ?string $reason = null)
    {
        return (new PartialReverseTransaction)->handle($transactionId, $amount, $reason);
    }
}
