<?php

namespace App\Http\Livewire;

use App\Models\InstituteSetting;
use Livewire\Component;

class InstituteSettings extends Component
{
    public string $default_language = 'bn';
    public string $currency = 'BDT';
    public string $current_academic_year = '';
    public string $sms_provider = 'log';
    public string $sms_api_url = '';
    public string $sms_api_key = '';
    public string $sms_sender_id = '';
    public ?int $settingId = null;

    public function mount(): void
    {
        $setting = InstituteSetting::where('institute_id', auth()->user()->institute_id)->first();

        if ($setting) {
            $this->settingId = $setting->id;
            $this->default_language = $setting->default_language;
            $this->currency = $setting->currency;
            $this->current_academic_year = $setting->current_academic_year ?? '';
            $this->sms_provider = $setting->sms_provider ?? 'log';
            $this->sms_api_url = $setting->sms_api_url ?? '';
            $this->sms_api_key = $setting->sms_api_key ?? '';
            $this->sms_sender_id = $setting->sms_sender_id ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'default_language' => 'required|string|in:en,bn',
            'currency' => 'required|string|max:10',
            'current_academic_year' => 'nullable|string|max:20',
            'sms_provider' => 'required|string|in:log,http_post',
            'sms_api_url' => 'nullable|url',
            'sms_api_key' => 'nullable|string|max:255',
            'sms_sender_id' => 'nullable|string|max:30',
        ]);

        $this->authorize('update', InstituteSetting::class);

        InstituteSetting::where('institute_id', auth()->user()->institute_id)->update([
            'default_language' => $this->default_language,
            'currency' => $this->currency,
            'current_academic_year' => $this->current_academic_year,
            'sms_provider' => $this->sms_provider,
            'sms_api_url' => $this->sms_api_url,
            'sms_api_key' => $this->sms_api_key,
            'sms_sender_id' => $this->sms_sender_id,
        ]);

        session()->flash('success', __('dashboard.settings_saved'));
    }

    public function render()
    {
        return view('livewire.institute-settings');
    }
}
