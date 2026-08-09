<?php
// app/Models/C2CPayment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class C2CPayment extends Model
{
    use HasFactory;
    protected $table = 'c2c_payments'; // ← این خط اضافه شد

    protected $fillable = [
        'order_id', 'exact_rial', 'expires_at', 
        'status', 'receipt_path', 'verified_at'
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class); // یا نام مدل سفارش شما
    }

// app/Models/C2CPayment.php
public function checks()
{
    return $this->hasMany(\App\Models\C2CCheck::class, 'c2c_payment_id');
}

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}