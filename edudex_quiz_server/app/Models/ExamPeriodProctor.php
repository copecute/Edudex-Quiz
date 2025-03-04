<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExamPeriodProctor extends Model
{
    protected $fillable = [
        'exam_period_id',
        'account_id'
    ];

    public function examPeriod(): BelongsTo
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(ExamPeriodRoom::class, 'exam_shift_room_proctors', 'exam_period_proctor_id', 'exam_shift_room_id')
                    ->withTimestamps();
    }

    // Scope để tìm kiếm
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->whereHas('account', function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhereHas('accountInfo', function($q) use ($search) {
                      $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        return $query;
    }
} 