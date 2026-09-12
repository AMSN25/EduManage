<?php

use App\Http\Controllers\Auth\InstituteRegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Livewire\AcademicClasses;
use App\Http\Livewire\AcademicYears;
use App\Http\Livewire\ClassSetupWizard;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Groups;
use App\Http\Livewire\InstituteSettings;
use App\Http\Livewire\Sections;
use App\Http\Livewire\Subjects;
use App\Http\Livewire\UserManagement;
use App\Http\Livewire\UserProfile;
use App\Http\Livewire\StudentList;
use App\Http\Livewire\StudentProfile;
use App\Http\Livewire\AttendanceMark;
use App\Http\Livewire\AttendanceReport;
use App\Http\Livewire\ExamList;
use App\Http\Livewire\ExamSetupWizard;
use App\Http\Livewire\MarkEntry;
use App\Http\Livewire\AdmitCards;
use App\Http\Livewire\SeatPlan;
use App\Http\Livewire\TabulationSheet;
use App\Http\Livewire\FeeCollection;
use App\Http\Livewire\FeeDuesReport;
use App\Http\Livewire\FeeTypes;
use App\Http\Livewire\FeeStructures;
use App\Http\Livewire\SmsDashboard;
use App\Http\Livewire\NoticeManage;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('register', [InstituteRegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [InstituteRegisterController::class, 'register'])->middleware('throttle:3,1');
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    // Password reset routes
    Route::get('forgot-password', function () {
        return view('auth.passwords.email');
    })->name('password.request');

    Route::post('forgot-password', function (\Illuminate\Http\Request $request) {
        $request->validate(['email' => 'required|email']);

        // Phase 10: SMS integration point — phone-based reset
        // For phone-based reset, check if input is a phone number and send OTP
        // For now, use standard Laravel password reset via email
        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');

    Route::get('reset-password/{token}', function (string $token, ?string $email = null) {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $email ?? request()->query('email', ''),
        ]);
    })->name('password.reset');

    Route::post('reset-password', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (\App\Models\User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->save();
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    })->name('password.store');
});

Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Dashboard
Route::middleware(['auth', 'institute.active'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/settings/institute', InstituteSettings::class)->name('institute.settings');

    // Academic Structure CRUD
    Route::get('/academic-years', AcademicYears::class)->name('academic-years.index');
    Route::get('/classes', AcademicClasses::class)->name('classes.index');
    Route::get('/sections', Sections::class)->name('sections.index');
    Route::get('/groups', Groups::class)->name('groups.index');
    Route::get('/subjects', Subjects::class)->name('subjects.index');

    // Class Setup Wizard
    Route::get('/setup-wizard', ClassSetupWizard::class)->name('setup-wizard');

    // User Management (institute-admin only)
    Route::get('/users', UserManagement::class)->name('users.index');

    // User Profile (all authenticated users)
    Route::get('/profile', UserProfile::class)->name('profile');

    // Student Management
    Route::get('/students', StudentList::class)->name('students.index');
    Route::get('/students/export', function () {
        $user = auth()->user();
        if (!$user->hasRole('institute-admin') && !$user->hasRole('super-admin')) {
            abort(403);
        }

        $query = \App\Models\Student::where('institute_id', $user->institute_id)
            ->with(['classModel', 'section', 'group']);
        if (request('class_id')) $query->where('class_id', request('class_id'));
        if (request('section_id')) $query->where('section_id', request('section_id'));
        if (request('status')) $query->where('status', request('status'));
        if (request('search')) {
            $search = '%' . str_replace(['%', '_'], ['\%', '\_'], request('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('student_id', 'like', $search);
            });
        }
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StudentExport($query->get()),
            'students_' . now()->format('Y-m-d') . '.xlsx'
        );
    })->name('students.export');
    Route::get('/students/import-template', function () {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StudentExport(collect()),
            'student_import_template.xlsx'
        );
    })->name('students.import-template');
    Route::get('/students/{studentId}', StudentProfile::class)->name('students.profile');

    // Attendance
    Route::get('/attendance', AttendanceMark::class)->name('attendance.mark');
    Route::get('/attendance/report', AttendanceReport::class)->name('attendance.report');

    // Exams & Marks
    Route::get('/exams', ExamList::class)->name('exams.index');
    Route::get('/exams/create', ExamSetupWizard::class)->name('exams.create');
    Route::get('/marks/entry', MarkEntry::class)->name('marks.entry');
    Route::get('/marks/entry/{examId}', MarkEntry::class)->name('marks.entry.exam');

    // Phase 8: Admit Cards, Seat Plan, Tabulation Sheet
    Route::get('/exams/admit-cards', AdmitCards::class)->name('exams.admit-cards');
    Route::get('/exams/seat-plan', SeatPlan::class)->name('exams.seat-plan');
    Route::get('/exams/tabulation', TabulationSheet::class)->name('exams.tabulation');

    // Phase 9: Fees
    Route::get('/fees/collect', FeeCollection::class)->name('fees.collect');
    Route::get('/fees/dues-report', FeeDuesReport::class)->name('fees.dues-report');
    Route::get('/fees/types', FeeTypes::class)->name('fees.types');
    Route::get('/fees/structures', FeeStructures::class)->name('fees.structures');
    Route::post('/fees/generate-monthly', function () {
        $user = auth()->user();
        if (!$user->can('generateMonthly', \App\Models\FeeStructure::class)) {
            abort(403);
        }
        $instituteId = $user->institute_id;
        \App\Jobs\GenerateMonthlyFees::dispatch($instituteId);
        session()->flash('success', __('fees.monthly_generation_queued'));
        return back();
    })->name('fees.generate-monthly');

    // Phase 10: SMS & Notices
    Route::get('/sms', SmsDashboard::class)->name('sms.dashboard');
    Route::get('/notices', NoticeManage::class)->name('notices.index');

    // Biometric Attendance
    Route::get('/biometric/devices', \App\Http\Livewire\DeviceManagement::class)->name('biometric.devices');
    Route::get('/biometric/mappings', \App\Http\Livewire\UserMapping::class)->name('biometric.mappings');
});

// Subscription expired page
Route::get('/subscription-expired', function () {
    return view('subscription-expired');
})->name('subscription.expired');

// Super Admin routes
Route::middleware(['auth', 'super-admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', \App\Http\Livewire\SuperAdminDashboard::class)->name('dashboard');
    Route::get('/institutes', \App\Http\Livewire\SuperAdminInstitutes::class)->name('institutes');
    Route::get('/institutes/{institute}', \App\Http\Livewire\SuperAdminInstituteDetail::class)->name('institute.detail');
    Route::get('/institutes/{institute}/payments', function (\App\Models\Institute $institute) {
        // Intentionally bypasses BelongsToInstitute scope:
        // Super admin needs to view payment history for any institute.
        $payments = \App\Models\SubscriptionPayment::where('institute_id', $institute->id)
            ->with('recordedBy')
            ->latest('paid_at')
            ->paginate(20);
        return view('super-admin.institute-payments', ['institute' => $institute, 'payments' => $payments]);
    })->name('institute.payments');
});

// Install instructions page
Route::get('/install', function () {
    return view('install');
})->name('install');

Route::get('/', function () {
    return view('welcome');
});
