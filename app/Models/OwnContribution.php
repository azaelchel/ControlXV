<?php

namespace App\Models;

use App\Models\Concerns\HasActiveFlag;
use Illuminate\Database\Eloquent\Model;

class OwnContribution extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'amount',
        'contributed_on',
        'concept',
        'notes',
        'active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'contributed_on' => 'date',
        'active' => 'boolean',
    ];
}
