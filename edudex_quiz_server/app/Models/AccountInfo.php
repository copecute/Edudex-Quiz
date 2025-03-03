<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountInfo extends Model
{
    protected $fillable = [
        'account_id',
        'fullName',
        'birthday',
        'avatar',
        'gender',
        'phoneNumber',
        'address',
    ];

    protected $casts = [
        'birthday' => 'date',
        'gender' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
} 