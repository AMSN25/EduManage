<?php

namespace App\Http\Livewire;

use App\Models\BiometricDevice;
use App\Models\DeviceUserMapping;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class UserMapping extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public ?int $biometric_device_id = null;
    public ?int $device_user_id = null;
    public ?int $student_id = null;
    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'biometric_device_id' => 'required|exists:biometric_devices,id',
            'device_user_id' => 'required|integer|min:1',
            'student_id' => 'required|exists:students,id',
        ];
    }

    public function render()
    {
        return view('livewire.user-mapping', [
            'mappings' => DeviceUserMapping::with('device', 'student')
                ->orderByDesc('created_at')
                ->paginate(15),
            'devices' => BiometricDevice::where('status', 'active')->orderBy('name')->get(),
            'students' => Student::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(DeviceUserMapping $mapping): void
    {
        $this->editingId = $mapping->id;
        $this->biometric_device_id = $mapping->biometric_device_id;
        $this->device_user_id = $mapping->device_user_id;
        $this->student_id = $mapping->student_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'biometric_device_id' => $this->biometric_device_id,
            'device_user_id' => $this->device_user_id,
            'student_id' => $this->student_id,
        ];

        if ($this->editingId) {
            DeviceUserMapping::findOrFail($this->editingId)->update($data);
        } else {
            DeviceUserMapping::create($data);
        }

        session()->flash('success', __('Mapping saved.'));
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(DeviceUserMapping $mapping): void
    {
        $mapping->delete();
        session()->flash('success', __('Mapping deleted.'));
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->biometric_device_id = null;
        $this->device_user_id = null;
        $this->student_id = null;
        $this->resetValidation();
    }
}
