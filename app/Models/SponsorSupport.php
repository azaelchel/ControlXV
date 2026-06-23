<?php

namespace App\Models;

use App\Models\Concerns\HasActiveFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SponsorSupport extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'guest_id',
        'concept',
        'pledged_amount',
        'notes',
        'active',
    ];

    protected $casts = [
        'pledged_amount' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(SponsorContribution::class)->orderBy('given_on')->orderBy('id');
    }

    public function givenAmount(): float
    {
        return (float) ($this->relationLoaded('contributions')
            ? $this->contributions->sum('amount')
            : $this->contributions()->sum('amount'));
    }

    public function remaining(): float
    {
        return max(0, (float) $this->pledged_amount - $this->givenAmount());
    }

    public function status(): string
    {
        if ($this->remaining() <= 0 && (float) $this->pledged_amount > 0) {
            return 'Completado';
        }

        return $this->givenAmount() > 0 ? 'Parcial' : 'Pendiente';
    }
}
