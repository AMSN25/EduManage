<?php

namespace App\Http\Livewire;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use Livewire\Component;
use Livewire\WithPagination;

class AcademicClasses extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public string $name = '';
    public int $numeric_order = 0;
    public ?int $academic_year_id = null;
    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'numeric_order' => 'required|integer|min:1',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ];
    }

    public function render()
    {
        return view('livewire.academic-classes', [
            'classes' => ClassModel::with('academicYear')
                ->orderBy('numeric_order')
                ->paginate(15),
            'academicYears' => AcademicYear::orderByDesc('name')->get(),
        ]);
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(ClassModel $class): void
    {
        $this->editingId = $class->id;
        $this->name = $class->name;
        $this->numeric_order = $class->numeric_order;
        $this->academic_year_id = $class->academic_year_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'numeric_order' => $this->numeric_order,
            'academic_year_id' => $this->academic_year_id,
        ];

        if ($this->editingId) {
            ClassModel::findOrFail($this->editingId)->update($data);
        } else {
            ClassModel::create($data);
        }

        session()->flash('success', __('academic_structure.class_saved'));
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(ClassModel $class): void
    {
        if ($class->sections()->exists()) {
            session()->flash('error', __('academic_structure.class_has_sections'));
            return;
        }

        $class->delete();
        session()->flash('success', __('academic_structure.class_deleted'));
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->numeric_order = 0;
        $this->academic_year_id = null;
        $this->resetValidation();
    }
}
