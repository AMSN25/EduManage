<?php

namespace App\Http\Livewire;

use App\Models\SmsLog;
use App\Jobs\SmsJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SmsDashboard extends Component
{
    use WithPagination;

    public $statusFilter = '';
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function retryFailed(int $logId): void
    {
        $log = SmsLog::where('institute_id', Auth::user()->institute_id)
            ->where('id', $logId)
            ->where('status', 'failed')
            ->first();

        if (!$log) {
            session()->flash('error', __('sms.log_not_found'));
            return;
        }

        $log->update(['status' => 'queued']);
        SmsJob::dispatch($log->id);

        session()->flash('success', __('sms.retry_queued'));
    }

    public function render()
    {
        $instituteId = Auth::user()->institute_id;

        $query = SmsLog::where('institute_id', $instituteId);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where('recipient_phone', 'like', '%' . $this->search . '%');
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs = $query->orderByDesc('created_at')->paginate(20);

        $stats = SmsLog::where('institute_id', $instituteId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalCost = SmsLog::where('institute_id', $instituteId)
            ->whereNotNull('cost')
            ->sum('cost');

        return view('livewire.sms-dashboard', [
            'logs' => $logs,
            'stats' => $stats,
            'totalCost' => $totalCost,
        ]);
    }
}
