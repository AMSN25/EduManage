<?php

namespace App\Http\Livewire;

use App\Models\Group;
use Livewire\Component;
use Livewire\WithPagination;

class Groups extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public string $name = '';
    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function render()
    {
        return view('livewire.groups', [
            'groups' => Group::orderBy('name')->paginate(15),
        ]);
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Group $group): void
    {
        $this->editingId = $group->id;
        $this->name = $group->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = ['name' => $this->name];

        if ($this->editingId) {
            Group::findOrFail($this->editingId)->update($data);
        } else {
            Group::create($data);
        }

        session()->flash('success', __('academic_structure.group_saved'));
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(Group $group): void
    {
        $group->delete();
        session()->flash('success', __('academic_structure.group_deleted'));
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->resetValidation();
    }
}
