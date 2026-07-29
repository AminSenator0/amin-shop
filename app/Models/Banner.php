<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'link', 'position', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public static function active(string $position = 'home')
    {
        return static::where('is_active', true)->where('position', $position)->orderBy('sort_order')->get();
    }
}
