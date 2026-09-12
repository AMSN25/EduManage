<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstituteSetting extends Model
{
    use HasFactory, BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'default_language',
        'currency',
        'current_academic_year',
        'sms_provider',
        'sms_api_url',
        'sms_api_key',
        'sms_sender_id',
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
