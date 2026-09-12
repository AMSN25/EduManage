<?php

namespace App\Services\Sms;

use App\Models\Institute;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use Illuminate\Support\Facades\App;

class SmsService
{
    private SmsGatewayInterface $gateway;

    public function __construct(?SmsGatewayInterface $gateway = null)
    {
        $this->gateway = $gateway ?? $this->resolveGateway();
    }

    /**
     * Send SMS using a template key, rendering with variables.
     */
    public function sendTemplate(
        int $instituteId,
        string $templateKey,
        string $phone,
        array $vars = [],
        ?string $relatedType = null,
        ?int $relatedId = null,
        string $language = 'en'
    ): SmsLog {
        $template = SmsTemplate::where('institute_id', $instituteId)
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return $this->logFailed($instituteId, $phone, "Template '{$templateKey}' not found or inactive", $relatedType, $relatedId);
        }

        $message = $template->render($language, $vars);

        return $this->sendRaw($instituteId, $phone, $message, $relatedType, $relatedId);
    }

    /**
     * Send a raw SMS message.
     */
    public function sendRaw(
        int $instituteId,
        string $phone,
        string $message,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): SmsLog {
        // Create log entry as queued
        $log = SmsLog::create([
            'institute_id' => $instituteId,
            'recipient_phone' => $phone,
            'message' => $message,
            'status' => 'queued',
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);

        // Dispatch the job
        \App\Jobs\SmsJob::dispatch($log->id);

        return $log;
    }

    /**
     * Execute the actual SMS send (called by the job).
     */
    public function executeSend(SmsLog $log): SmsSendResult
    {
        $result = $this->gateway->send($log->recipient_phone, $log->message);

        $log->update([
            'status' => $result->success ? 'sent' : 'failed',
            'provider_response' => $result->providerResponse,
            'cost' => $result->cost,
        ]);

        return $result;
    }

    private function resolveGateway(): SmsGatewayInterface
    {
        $provider = config('sms.provider', 'log');

        return match ($provider) {
            'http_post' => new HttpPostGateway(),
            default => new LogGateway(),
        };
    }

    private function logFailed(int $instituteId, string $phone, string $reason, ?string $relatedType, ?int $relatedId): SmsLog
    {
        return SmsLog::create([
            'institute_id' => $instituteId,
            'recipient_phone' => $phone,
            'message' => '',
            'status' => 'failed',
            'provider_response' => $reason,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);
    }
}
