<?php

namespace App\Services\Sms;

interface SmsGatewayInterface
{
    /**
     * Send an SMS to the given phone number.
     *
     * @param string $phone E.164 or local format
     * @param string $message The SMS body
     * @return SmsSendResult
     */
    public function send(string $phone, string $message): SmsSendResult;
}
