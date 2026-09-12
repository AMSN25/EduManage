<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Fake SMS gateway for local development.
 * Just logs the message without making any HTTP call.
 */
class LogGateway implements SmsGatewayInterface
{
    public function send(string $phone, string $message): SmsSendResult
    {
        Log::info('SMS sent (log driver)', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return SmsSendResult::success('Logged', 0.0);
    }
}
