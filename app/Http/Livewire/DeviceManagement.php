<?php

namespace App\Http\Livewire;

use App\Models\BiometricDevice;
use Livewire\Component;
use Livewire\WithPagination;

class DeviceManagement extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public string $serial_number = '';
    public ?string $name = null;
    public ?string $model = null;
    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'serial_number' => 'required|string|max:255|unique:biometric_devices,serial_number' . ($this->editingId ? ",{$this->editingId}" : ''),
            'name' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
        ];
    }

    public function render()
    {
        return view('livewire.device-management', [
            'devices' => BiometricDevice::orderByDesc('created_at')->paginate(15),
        ]);
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(BiometricDevice $device): void
    {
        $this->editingId = $device->id;
        $this->serial_number = $device->serial_number;
        $this->name = $device->name;
        $this->model = $device->model;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'serial_number' => $this->serial_number,
            'name' => $this->name,
            'model' => $this->model,
        ];

        if ($this->editingId) {
            BiometricDevice::findOrFail($this->editingId)->update($data);
        } else {
            BiometricDevice::create($data + ['status' => 'pending']);
        }

        session()->flash('success', __('Device saved.'));
        $this->showModal = false;
        $this->resetForm();
    }

    public function activate(BiometricDevice $device): void
    {
        $device->update(['status' => 'active']);
        session()->flash('success', __('Device activated.'));
    }

    public function deactivate(BiometricDevice $device): void
    {
        $device->update(['status' => 'inactive']);
        session()->flash('success', __('Device deactivated.'));
    }

    public function delete(BiometricDevice $device): void
    {
        $device->delete();
        session()->flash('success', __('Device deleted.'));
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->serial_number = '';
        $this->name = null;
        $this->model = null;
        $this->resetValidation();
    }
}
