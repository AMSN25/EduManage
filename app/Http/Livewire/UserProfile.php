<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    public function updateProfile(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
        ]);

        session()->flash('success', __('user_management.profile_updated'));
    }

    public function changePassword(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            $this->addError('current_password', __('user_management.current_password_incorrect'));
            return;
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        session()->flash('success', __('user_management.password_changed'));
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}
