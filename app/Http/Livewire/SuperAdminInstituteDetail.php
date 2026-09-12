<?php

namespace App\Http\Livewire;

use App\Models\Institute;
use Livewire\Component;

class SuperAdminInstituteDetail extends Component
{
    public int $instituteId;
    public $institute;

    public function mount(Institute $institute): void
    {
        // Intentionally bypasses BelongsToInstitute scope:
        // Super admin needs to view any institute detail page.
        $this->instituteId = $institute->id;
        $this->institute = $institute->load(['subscriptions' => function ($q) {
            $q->whereIn('status', ['active', 'trial'])->latest('ends_at');
        }, 'subscriptions.plan', 'users', 'students']);
    }

    public function render()
    {
        return view('livewire.super-admin-institute-detail');
    }
}
