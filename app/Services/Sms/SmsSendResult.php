<?php

namespace App\Services\Sms;

class SmsSendResult
{
    public function __construct(
        public bool $success,
        public string $providerResponse = '',
        public ?float $cost = null,
    ) {}

    public static function success(string $response = '', ?float $cost = null): self
    {
        return new self(true, $response, $cost);
    }

    public static function failed(string $response = ''): self
    {
        return new self(false, $response);
    }
}
