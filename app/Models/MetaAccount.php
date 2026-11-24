<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaAccount extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'platform',
        'meta_id',
        'access_token',
        'picture_url',
        'status',
        'category',
        'notes',
    ];

    protected $hidden = [
        'access_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calendarEntries(): HasMany
    {
        return $this->hasMany(UserAgentCalendarEntry::class, 'meta_account_id');
    }
}
