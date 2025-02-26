<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'commission_rate',
        'status',
        'user_id'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'commission_rate' => 'decimal:2'
    ];

    /**
     * Product status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Get the seller of the product
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all purchase transactions for this product
     */
    public function purchaseTransactions()
    {
        return $this->hasMany(Transaction::class)->where('type', 'purchase');
    }

    /**
     * Get all transactions for this product
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the user who owns this product
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the product is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the product is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Get total number of sales
     */
    public function getTotalSalesAttribute(): int
    {
        return $this->purchaseTransactions()->count();
    }

    /**
     * Get total revenue from this product
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->purchaseTransactions()->sum('amount');
    }

    /**
     * Calculate final price based on commission
     */
    public static function calculateFinalPrice($basePrice)
    {
        $commission = CommissionSetting::where('is_active', true)->first();

        if (!$commission) {
            return $basePrice;
        }

        if ($commission->type === 'percentage') {
            return $basePrice * (1 + $commission->value / 100);
        }

        return $basePrice + $commission->value;
    }

    /**
     * Get the final price (base price + commission)
     */
    public function getFinalPriceAttribute(): float
    {
        $commission = CommissionSetting::getActive();

        if (!$commission) {
            return $this->base_price;
        }

        if ($commission->type === 'percentage') {
            return $this->base_price * (1 + $commission->value / 100);
        }

        return $this->base_price + $commission->value;
    }
}
