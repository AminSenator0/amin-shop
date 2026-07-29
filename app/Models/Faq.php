<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    public const HOMEPAGE_LIMIT = 5;

    protected $fillable = [
        'question',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function active()
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }

    public static function activeCount(): int
    {
        return static::where('is_active', true)->count();
    }

    public static function hasMoreForHomepage(): bool
    {
        return static::activeCount() > self::HOMEPAGE_LIMIT;
    }

    public static function activeForHomepage(?int $limit = null)
    {
        $limit ??= self::HOMEPAGE_LIMIT;

        if (static::activeCount() <= $limit) {
            return static::active();
        }

        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->take($limit)
            ->get();
    }
}
