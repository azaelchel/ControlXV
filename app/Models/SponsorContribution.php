<?php

namespace App\Models;

use App\Models\Concerns\HasActiveFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorContribution extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'sponsor_support_id',
        'amount',
        'given_on',
        'notes',
        'active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'given_on' => 'date',
        'active' => 'boolean',
    ];

    public function support(): BelongsTo
    {
        return $this->belongsTo(SponsorSupport::class, 'sponsor_support_id');
    }
}
