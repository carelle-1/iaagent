<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Json;

class Subscription extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'interval',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    protected function features(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => is_array($value) ? $value : (is_string($value) ? json_decode($value, true) ?? [] : []),
        );
    }
}
