<?php

namespace App\Http\Livewire;

use App\Models\Subject;
use Livewire\Component;
use Livewire\WithPagination;

class Subjects extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public string $name = '';
    public ?string $name_bangla = null;
    public ?string $code = null;
    public bool $is_optional = false;
    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_bangla' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
            'is_optional' => 'boolean',
        ];
    }

    public function render()
    {
        return view('livewire.subjects', [
            'subjects' => Subject::orderBy('name')->paginate(15),
        ]);
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Subject $subject): void
    {
        $this->editingId = $subject->id;
        $this->name = $subject->name;
        $this->name_bangla = $subject->name_bangla;
        $this->code = $subject->code;
        $this->is_optional = $subject->is_optional;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'name_bangla' => $this->name_bangla,
            'code' => $this->code,
            'is_optional' => $this->is_optional,
        ];

        if ($this->editingId) {
            Subject::findOrFail($this->editingId)->update($data);
        } else {
            Subject::create($data);
        }

        session()->flash('success', __('academic_structure.subject_saved'));
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(Subject $subject): void
    {
        if ($subject->classes()->exists()) {
            session()->flash('error', __('academic_structure.subject_has_classes'));
            return;
        }

        $subject->delete();
        session()->flash('success', __('academic_structure.subject_deleted'));
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->name_bangla = null;
        $this->code = null;
        $this->is_optional = false;
        $this->resetValidation();
    }
}
