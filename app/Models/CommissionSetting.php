<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Get the current active commission setting
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }

    // Calculate commission amount for a given price
    public function calculateCommission($price)
    {
        if ($this->type === 'percentage') {
            return $price * ($this->value / 100);
        }

        return $this->value;
    }

    // When activating a commission setting, deactivate all others
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_active) {
                static::where('id', '!=', $model->id)->update(['is_active' => false]);
            }
        });
    }
}
