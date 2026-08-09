<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_name',
        'fcm_token',
        'api_key',
        'is_active',
    ];
}