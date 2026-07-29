<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StoreSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    protected static function booted(): void
    {
        static::saved(fn (self $setting) => Cache::forget("store_setting.{$setting->key}"));
        static::deleted(fn (self $setting) => Cache::forget("store_setting.{$setting->key}"));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("store_setting.{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        static::updateOrCreate(['key' => $key], compact('value', 'group', 'type'));
        Cache::forget("store_setting.{$key}");
    }
}
