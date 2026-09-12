<?php

namespace App\Http\Livewire;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\FeeStructure;
use App\Models\FeeType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FeeStructures extends Component
{
    public $classes = [];
    public $feeTypes = [];
    public $classId = '';
    public $feeTypeId = '';
    public $amount = '';
    public $dueDay = '';
    public $academicYearId = '';
    public $editingId = null;

    public function mount(): void
    {
        $instituteId = Auth::user()->institute_id;
        $this->classes = ClassModel::where('institute_id', $instituteId)->orderBy('numeric_order')->get();
        $this->feeTypes = FeeType::where('institute_id', $instituteId)->get();
        $this->academicYearId = AcademicYear::where('institute_id', $instituteId)->where('is_current', true)->first()?->id ?? '';
    }

    public function save(): void
    {
        if (!Auth::user()->can('manageStructures', FeeStructure::class)) {
            session()->flash('error', __('fees.no_permission'));
            return;
        }

        $this->validate([
            'classId' => 'required|exists:classes,id',
            'feeTypeId' => 'required|exists:fee_types,id',
            'amount' => 'required|numeric|min:0',
            'dueDay' => 'nullable|integer|min:1|max:31',
            'academicYearId' => 'required|exists:academic_years,id',
        ]);

        FeeStructure::updateOrCreate(
            [
                'institute_id' => Auth::user()->institute_id,
                'class_id' => $this->classId,
                'fee_type_id' => $this->feeTypeId,
                'academic_year_id' => $this->academicYearId,
            ],
            [
                'amount' => $this->amount,
                'due_day' => $this->dueDay ?: null,
            ]
        );

        $this->reset(['classId', 'feeTypeId', 'amount', 'dueDay', 'editingId']);
        session()->flash('success', __('fees.structure_saved'));
    }

    public function structures()
    {
        return FeeStructure::where('institute_id', Auth::user()->institute_id)
            ->with(['classModel', 'feeType', 'academicYear'])
            ->get();
    }

    public function render()
    {
        return view('livewire.fee-structures', [
            'structures' => $this->structures(),
        ]);
    }
}
