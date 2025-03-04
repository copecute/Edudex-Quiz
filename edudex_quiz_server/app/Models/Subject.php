<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $fillable = [
        'code',
        'name',
        'credits',
        'major_id',
        'description'
    ];

    // Relationship với ngành học
    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    // Relationship với môn thi trong kỳ thi
    public function examPeriodSubjects(): HasMany
    {
        return $this->hasMany(ExamPeriodSubject::class);
    }

    // Scope để tìm kiếm
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
        }
        return $query;
    }

    // Scope để lọc theo ngành
    public function scopeByMajor($query, $majorId = null)
    {
        if ($majorId) {
            return $query->where('major_id', $majorId);
        }
        return $query;
    }
} 