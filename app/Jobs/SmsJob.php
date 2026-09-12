<?php

namespace App\Jobs;

use App\Models\SmsLog;
use App\Services\Sms\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public int $smsLogId,
    ) {}

    public function handle(SmsService $smsService): void
    {
        $log = SmsLog::find($this->smsLogId);

        if (!$log || $log->status === 'sent') {
            return;
        }

        $smsService->executeSend($log);
    }

    public function failed(\Throwable $exception): void
    {
        $log = SmsLog::find($this->smsLogId);
        if ($log) {
            $log->update([
                'status' => 'failed',
                'provider_response' => 'Job failed: ' . $exception->getMessage(),
            ]);
        }
    }
}
