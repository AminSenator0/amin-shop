<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'is_published',
        'published_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public static function published(int $limit = 3)
    {
        return static::where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->take($limit)
            ->get();
    }

    public function imageUrl(): string
    {
        if ($this->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->image)) {
            return asset('storage/'.$this->image);
        }

        $fallback = 'blog/'.\App\Support\BlogImages::storageBasename($this->title).'.webp';

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($fallback)) {
            return asset('storage/'.$fallback);
        }

        return asset('images/placeholder.svg');
    }
}
