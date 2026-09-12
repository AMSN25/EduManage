<?php

namespace App\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsTemplate extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'key',
        'body_bangla',
        'body_english',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function render(string $language = 'en', array $vars = []): string
    {
        $body = $language === 'bn' ? $this->body_bangla : $this->body_english;

        foreach ($vars as $key => $value) {
            $body = str_replace(':' . $key, $value, $body);
        }

        return $body;
    }
}
