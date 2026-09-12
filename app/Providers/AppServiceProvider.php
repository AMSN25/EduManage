<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Institute;
use App\Models\InstituteSetting;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\ExamPolicy;
use App\Policies\FeePolicy;
use App\Policies\InstituteSettingPolicy;
use App\Policies\PlanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(InstituteSetting::class, InstituteSettingPolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(ExamSubject::class, ExamPolicy::class);
        Gate::policy(FeeStructure::class, FeePolicy::class);
        Gate::policy(FeeType::class, FeePolicy::class);

        // Plan enforcement gates
        Gate::define('plan.can-add-student', function (User $user, Institute $institute) {
            $policy = new PlanPolicy();
            return $policy->canAddStudent($user, $institute);
        });

        Gate::define('plan.can-access-feature', function (User $user, Institute $institute, string $feature) {
            $policy = new PlanPolicy();
            return $policy->canAccessFeature($user, $institute, $feature);
        });
    }
}
