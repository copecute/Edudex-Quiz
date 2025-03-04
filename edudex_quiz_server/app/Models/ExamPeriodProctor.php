<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPeriodProctor extends Model
{
    protected $fillable = [
        'exam_period_id',
        'account_id'
    ];

    public function examPeriod()
    {
        return $this->belongsTo(ExamPeriod::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
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