<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Agent extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class, 'agent_subscription');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}