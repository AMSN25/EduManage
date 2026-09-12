<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToInstitute
{
    protected static function bootBelongsToInstitute(): void
    {
        static::creating(function (Model $model) {
            if (is_null($model->institute_id) && auth()->check()) {
                $model->institute_id = auth()->user()->institute_id;
            }
        });

        static::addGlobalScope('institute', function (Builder $builder) {
            if (auth()->check() && !auth()->user()->hasRole('super-admin')) {
                $builder->where('institute_id', auth()->user()->institute_id);
            }
        });
    }

    public function institute()
    {
        return $this->belongsTo(\App\Models\Institute::class);
    }
}
