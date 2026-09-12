<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpPostGateway implements SmsGatewayInterface
{
    private string $apiUrl;
    private string $apiKey;
    private string $senderId;

    public function __construct(?string $apiUrl = null, ?string $apiKey = null, ?string $senderId = null)
    {
        $this->apiUrl = $apiUrl ?? config('sms.api_url', '');
        $this->apiKey = $apiKey ?? config('sms.api_key', '');
        $this->senderId = $senderId ?? config('sms.sender_id', '');
    }

    public function send(string $phone, string $message): SmsSendResult
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            return SmsSendResult::failed('SMS gateway not configured');
        }

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->post($this->apiUrl, [
                    'api_key' => $this->apiKey,
                    'sender_id' => $this->senderId,
                    'phone' => $phone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                $body = $response->json();
                return SmsSendResult::success(
                    $body['message'] ?? 'Sent',
                    $body['cost'] ?? null
                );
            }

            return SmsSendResult::failed(
                $response->body() ?: 'HTTP ' . $response->status()
            );
        } catch (\Exception $e) {
            Log::error('SMS gateway error: ' . $e->getMessage());
            return SmsSendResult::failed($e->getMessage());
        }
    }
}
