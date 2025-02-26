<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const TYPE_BUYER = 'buyer';
    const TYPE_SELLER = 'seller';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'wallet_balance',
        'is_admin',
        'type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'wallet_balance' => 'decimal:2',
        'is_admin' => 'boolean',
    ];

    /**
     * Valid user roles.
     *
     * @var array<string>
     */
    public static $roles = [
        'buyer' => 'buyer',
        'seller' => 'seller',
        'admin' => 'admin',
    ];

    /**
     * Check if the user is a buyer
     */
    public function isBuyer(): bool
    {
        return $this->type === self::TYPE_BUYER;
    }

    /**
     * Check if the user is a seller
     */
    public function isSeller(): bool
    {
        return $this->type === self::TYPE_SELLER;
    }

    /**
     * Check if the user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Get the products that belong to the user.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Transactions made by this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Purchased products (as buyer)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function purchasedProducts()
    {
        return $this->hasManyThrough(
            Product::class,
            Transaction::class,
            'user_id',
            'id',
            'id',
            'product_id'
        )->where('transactions.type', 'purchase');
    }
}
