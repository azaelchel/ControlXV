<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageSend extends Model
{
    protected $fillable = [
        'guest_id',
        'message_template_id',
        'public_guest_link_id',
        'user_id',
        'rendered_message',
        'phone',
        'sent_at',
        'active',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'active'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active', fn ($q) => $q->where('message_sends.active', true));
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'message_template_id');
    }

    public function publicLink(): BelongsTo
    {
        return $this->belongsTo(PublicGuestLink::class, 'public_guest_link_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
