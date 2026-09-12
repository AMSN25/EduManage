<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceUserMapping extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'biometric_device_id',
        'device_user_id',
        'student_id',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
