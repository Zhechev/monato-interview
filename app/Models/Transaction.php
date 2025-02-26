<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'type',
        'amount',
        'balance_after',
        'status',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    // User who made the transaction
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Product involved in the transaction (if any)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scope for different transaction types
    public function scopeTopUps($query)
    {
        return $query->where('type', 'top_up');
    }

    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', 'withdrawal');
    }

    public function scopeCommissions($query)
    {
        return $query->where('type', 'commission');
    }

    public static function getTotalCommissions()
    {
        return static::where('type', 'commission')->sum('amount');
    }

    public static function getTotalCommissionsForPeriod($startDate, $endDate)
    {
        return static::where('type', 'commission')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
    }
}
