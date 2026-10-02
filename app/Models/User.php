<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ReturnStatus;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function wallet(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(Wallet::class);
}
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function defaultAddress(): ?Address
    {
        return $this->addresses()->where('is_default', true)->first()
            ?? $this->addresses()->latest()->first();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function savedCoupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'user_coupons')->withTimestamps();
    }

    public function orderReturns(): HasManyThrough
    {
        return $this->hasManyThrough(OrderReturn::class, Order::class);
    }

    public function openReturnsCount(): int
    {
        return $this->orderReturns()
            ->whereIn('order_returns.status', [ReturnStatus::Pending, ReturnStatus::Approved])
            ->count();
    }

    public function hasPurchasedProduct(int $productId): bool
    {
        return $this->orders()
            ->where('status', OrderStatus::Delivered)
            ->whereHas('items', fn ($q) => $q->where('product_id', $productId))
            ->exists();
    }

    public function hasReviewedProduct(int $productId): bool
    {
        return $this->reviews()->where('product_id', $productId)->exists();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    public function canReviewProduct(int $productId): bool
    {
        return $this->hasPurchasedProduct($productId) && ! $this->hasReviewedProduct($productId);
    }

    /** @return Collection<int, \App\Models\Product> */
    public function productsAwaitingReview(): Collection
    {
        $reviewedIds = $this->reviews()->pluck('product_id');

        $productIds = $this->orders()
            ->where('status', OrderStatus::Delivered)
            ->with('items')
            ->get()
            ->flatMap(fn ($order) => $order->items->pluck('product_id'))
            ->filter()
            ->unique()
            ->diff($reviewedIds);

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get();
    }
}
