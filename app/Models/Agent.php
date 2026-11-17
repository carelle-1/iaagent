<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class, 'agent_subscription');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AgentCategory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}