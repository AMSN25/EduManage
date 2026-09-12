<?php

namespace App\Http\Livewire;

use App\Models\ClassModel;
use App\Models\Notice;
use App\Models\Student;
use App\Models\User;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NoticeManage extends Component
{
    public $notices = [];
    public $title = '';
    public $body = '';
    public $audience = 'all';
    public $classId = '';
    public $sendSms = false;
    public $classes;
    public $editingId = null;
    public $showForm = false;

    public function mount(): void
    {
        $instituteId = Auth::user()->institute_id;
        $this->classes = ClassModel::where('institute_id', $instituteId)->orderBy('numeric_order')->get();
        $this->loadNotices();
    }

    public function loadNotices(): void
    {
        $this->notices = Notice::where('institute_id', Auth::user()->institute_id)
            ->with('classModel')
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    public function toggleForm(): void
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->reset(['title', 'body', 'audience', 'classId', 'sendSms', 'editingId']);
        }
    }

    public function edit(int $id): void
    {
        $notice = Notice::findOrFail($id);
        $this->editingId = $id;
        $this->title = $notice->title;
        $this->body = $notice->body;
        $this->audience = $notice->audience;
        $this->classId = $notice->class_id ?? '';
        $this->sendSms = $notice->send_sms;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'audience' => 'required|in:all,class,teachers',
            'classId' => 'required_if:audience,class|nullable|exists:classes,id',
        ]);

        $instituteId = Auth::user()->institute_id;

        $notice = Notice::updateOrCreate(
            ['id' => $this->editingId, 'institute_id' => $instituteId],
            [
                'title' => $this->title,
                'body' => $this->body,
                'audience' => $this->audience,
                'class_id' => $this->audience === 'class' ? $this->classId : null,
                'send_sms' => $this->sendSms,
                'published_at' => now(),
            ]
        );

        // Send SMS if requested
        if ($this->sendSms) {
            $this->dispatchSms($notice);
        }

        $this->reset(['title', 'body', 'audience', 'classId', 'sendSms', 'editingId']);
        $this->showForm = false;
        $this->loadNotices();

        session()->flash('success', __('sms.notice_saved'));
    }

    private function dispatchSms(Notice $notice): void
    {
        $instituteId = Auth::user()->institute_id;
        $students = Student::where('institute_id', $instituteId)
            ->where('status', 'active')
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if ($notice->audience === 'class' && $notice->class_id) {
            $students->where('class_id', $notice->class_id);
        }

        $students = $students->get();

        $smsService = app(SmsService::class);

        foreach ($students as $student) {
            $smsService->sendTemplate(
                $instituteId,
                'notice',
                $student->phone,
                [
                    'student_name' => $student->name,
                    'title' => $notice->title,
                    'body' => $notice->body,
                ],
                Notice::class,
                $notice->id
            );
        }
    }

    public function render()
    {
        return view('livewire.notice-manage');
    }
}
