<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public ?int $editingUserId = null;
    public bool $showCreateModal = false;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $phone = '';
    public string $selectedRole = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'nullable|string|max:20',
        'selectedRole' => 'required|exists:roles,name',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function createUser(): void
    {
        $validated = $this->validate();

        // Prevent assigning super-admin role to institute users
        if ($validated['selectedRole'] === 'super-admin') {
            session()->flash('error', __('user_management.cannot_assign_super_admin'));
            return;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?: null,
            'institute_id' => auth()->user()->institute_id,
            'is_active' => true,
        ]);

        $user->assignRole($validated['selectedRole']);

        session()->flash('success', __('user_management.user_created'));
        $this->closeCreateModal();
    }

    public function editUser(User $user): void
    {
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->selectedRole = $user->getFirstRole()->name ?? '';
        $this->password = '';
        $this->showCreateModal = true;
    }

    public function updateUser(): void
    {
        $user = User::findOrFail($this->editingUserId);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'selectedRole' => 'required|exists:roles,name',
        ];

        if ($this->password) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $this->validate($rules);

        if ($validated['selectedRole'] === 'super-admin') {
            session()->flash('error', __('user_management.cannot_assign_super_admin'));
            return;
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            ...($this->password ? ['password' => Hash::make($this->password)] : []),
        ]);

        $user->syncRoles([$validated['selectedRole']]);

        session()->flash('success', __('user_management.user_updated'));
        $this->closeCreateModal();
        $this->resetForm();
    }

    public function toggleActive(User $user): void
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', __('user_management.cannot_deactivate_self'));
            return;
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        session()->flash('success', __('user_management.user_' . $status));
    }

    public function render()
    {
        $instituteId = auth()->user()->institute_id;

        $query = User::where('institute_id', $instituteId)
            ->with('roles');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->roleFilter) {
            $query->role($this->roleFilter);
        }

        $users = $query->orderBy('name')->paginate(15);
        $roles = Role::where('name', '!=', 'super-admin')->get();

        return view('livewire.user-management', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->phone = '';
        $this->selectedRole = '';
        $this->editingUserId = null;
    }
}
