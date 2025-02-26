<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $table = 'commission_settings';

    protected $fillable = [
        'type',
        'value',
        'is_active'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public static function getActiveCommission()
    {
        return static::where('is_active', true)->first();
    }
}
