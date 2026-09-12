<?php

namespace App\Http\Livewire;

use App\Models\ClassModel;
use App\Models\Section;
use Livewire\Component;
use Livewire\WithPagination;

class Sections extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public string $name = '';
    public ?int $class_id = null;
    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
        ];
    }

    public function render()
    {
        return view('livewire.sections', [
            'sections' => Section::with('classModel')
                ->orderBy('class_id')
                ->orderBy('name')
                ->paginate(15),
            'classes' => ClassModel::orderBy('numeric_order')->get(),
        ]);
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Section $section): void
    {
        $this->editingId = $section->id;
        $this->name = $section->name;
        $this->class_id = $section->class_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'class_id' => $this->class_id,
        ];

        if ($this->editingId) {
            Section::findOrFail($this->editingId)->update($data);
        } else {
            Section::create($data);
        }

        session()->flash('success', __('academic_structure.section_saved'));
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(Section $section): void
    {
        $section->delete();
        session()->flash('success', __('academic_structure.section_deleted'));
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->class_id = null;
        $this->resetValidation();
    }
}
