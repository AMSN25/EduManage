<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\InstituteSetting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstituteRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.institute-register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'institute_name' => 'required|string|max:255',
            'institute_email' => 'required|email|max:255|unique:institutes,email',
            'institute_phone' => 'nullable|string|max:20',
            'institute_address' => 'nullable|string|max:500',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();

        try {
            $slug = Str::slug($request->institute_name);
            $originalSlug = $slug;
            $counter = 1;
            while (Institute::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $institute = Institute::create([
                'name' => $request->institute_name,
                'slug' => $slug,
                'email' => $request->institute_email,
                'phone' => $request->institute_phone,
                'address' => $request->institute_address,
                'is_active' => true,
                'trial_ends_at' => now()->addDays(14),
            ]);

            InstituteSetting::create([
                'institute_id' => $institute->id,
                'default_language' => 'bn',
                'currency' => 'BDT',
                'current_academic_year' => date('Y'),
            ]);

            $admin = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'phone' => $request->admin_phone,
                'password' => $request->password,
                'institute_id' => $institute->id,
                'is_active' => true,
            ]);

            $admin->assignRole('institute-admin');

            event(new Registered($admin));

            Auth::login($admin);

            DB::commit();

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'institute_name' => __('auth.registration_failed'),
            ])->withInput();
        }
    }
}
