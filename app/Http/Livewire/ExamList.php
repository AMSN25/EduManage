<?php

namespace App\Http\Livewire;

use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExamList extends Component
{
    public function render()
    {
        $exams = Exam::where('institute_id', Auth::user()->institute_id)
            ->with('academicYear')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.exam-list', [
            'exams' => $exams,
        ]);
    }
}
