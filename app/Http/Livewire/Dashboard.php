<?php

namespace App\Http\Livewire;

use App\Models\Student;
use App\Models\User;
use App\Models\Attendance;
use Livewire\Component;

class Dashboard extends Component
{
    public int $totalStudents = 0;
    public int $totalTeachers = 0;
    public float $monthlyIncome = 0;
    public int $attendanceToday = 0;

    public function mount(): void
    {
        $instituteId = auth()->user()->institute_id;

        $this->totalStudents = Student::where('institute_id', $instituteId)->count();

        $this->totalTeachers = User::where('institute_id', $instituteId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->count();

        $this->attendanceToday = Attendance::where('institute_id', $instituteId)
            ->where('date', today())
            ->count();

        $this->monthlyIncome = \App\Models\FeePayment::where('institute_id', $instituteId)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
