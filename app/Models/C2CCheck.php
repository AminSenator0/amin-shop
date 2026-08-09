<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class C2CCheck extends Model
{
    // ===== اضافه کردن نام جدول =====
    protected $table = 'c2c_checks';

    protected $fillable = [
        'c2c_payment_id', 'status', 'amount',
        'tracking_code', 'check_from', 'result', 'resolved_at'
    ];

    protected $casts = [
        'check_from' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function c2cPayment(): BelongsTo
    {
        return $this->belongsTo(C2CPayment::class);
    }
}