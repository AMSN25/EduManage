<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SmsLog extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'recipient_phone',
        'message',
        'status',
        'provider_response',
        'cost',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'cost' => 'decimal:4',
    ];

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
