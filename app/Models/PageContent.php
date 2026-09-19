<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $guarded = [];

    public static function get(string $key, string $default = ''): string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function map(): array
    {
        return static::query()->pluck('value', 'key')->toArray();
    }
}
