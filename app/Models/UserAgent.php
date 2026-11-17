<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserAgent extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'custom_name',
        'domain',
        'tone',
        'profile_photo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function identity(): HasOne
    {
        return $this->hasOne(UserAgentIdentity::class);
    }

    public function identityImages(): HasMany
    {
        return $this->hasMany(UserAgentIdentityImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function inspiringAccounts(): HasMany
    {
        return $this->hasMany(UserAgentInspiringAccount::class)->latest();
    }

    public function calendarEntries(): HasMany
    {
        return $this->hasMany(UserAgentCalendarEntry::class)->orderBy('published_at');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(UserAgentContact::class)->latest();
    }

    public function websites(): HasMany
    {
        return $this->hasMany(UserAgentWebsite::class)->latest();
    }
}