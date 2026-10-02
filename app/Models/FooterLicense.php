<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterLicense extends Model
{
    protected $fillable = [
        'title', 'image', 'link', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public static function active()
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }
}