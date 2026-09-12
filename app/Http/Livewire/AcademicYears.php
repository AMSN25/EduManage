<?php

namespace App\Http\Livewire;

use App\Models\AcademicYear;
use Livewire\Component;
use Livewire\WithPagination;

class AcademicYears extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public string $name = '';
    public ?string $start_date = null;
    public ?string $end_date = null;
    public bool $is_current = false;
    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.academic-years', [
            'years' => AcademicYear::orderByDesc('name')->paginate(15),
        ]);
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(AcademicYear $year): void
    {
        $this->editingId = $year->id;
        $this->name = $year->name;
        $this->start_date = $year->start_date?->format('Y-m-d');
        $this->end_date = $year->end_date?->format('Y-m-d');
        $this->is_current = $year->is_current;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_current' => $this->is_current,
        ];

        if ($this->editingId) {
            AcademicYear::findOrFail($this->editingId)->update($data);
        } else {
            AcademicYear::create($data);
        }

        session()->flash('success', __('academic_structure.year_saved'));
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(AcademicYear $year): void
    {
        $year->delete();
        session()->flash('success', __('academic_structure.year_deleted'));
    }

    public function toggleCurrent(AcademicYear $year): void
    {
        $year->update(['is_current' => !$year->is_current]);
        session()->flash('success', __('academic_structure.year_updated'));
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->start_date = null;
        $this->end_date = null;
        $this->is_current = false;
        $this->resetValidation();
    }
}
