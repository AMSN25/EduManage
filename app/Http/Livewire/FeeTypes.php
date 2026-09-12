<?php

namespace App\Http\Livewire;

use App\Models\FeeType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FeeTypes extends Component
{
    public $name = '';
    public $isRecurring = false;
    public $editingId = null;

    public function save(): void
    {
        if (!Auth::user()->can('manageStructures', FeeType::class)) {
            session()->flash('error', __('fees.no_permission'));
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        FeeType::updateOrCreate(
            [
                'id' => $this->editingId,
                'institute_id' => Auth::user()->institute_id,
            ],
            [
                'name' => $this->name,
                'is_recurring' => $this->isRecurring,
            ]
        );

        $this->reset(['name', 'isRecurring', 'editingId']);
        session()->flash('success', __('fees.fee_type_saved'));
    }

    public function edit(int $id): void
    {
        $type = FeeType::findOrFail($id);
        $this->editingId = $id;
        $this->name = $type->name;
        $this->isRecurring = $type->is_recurring;
    }

    public function render()
    {
        return view('livewire.fee-types', [
            'feeTypes' => FeeType::where('institute_id', Auth::user()->institute_id)->get(),
        ]);
    }
}
