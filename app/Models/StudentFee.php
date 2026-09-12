<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFee extends Model
{
    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'month',
        'amount_due',
        'amount_paid',
        'status',
        'due_date',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function getRemainingDueAttribute(): float
    {
        return (float) $this->amount_due - (float) $this->amount_paid;
    }

    public function scopeUnpaidOrPartial($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }
}
