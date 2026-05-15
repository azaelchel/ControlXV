<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasActiveFlag;

class Companion extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'invited_group',
        'name',
        'type',
        'sex',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
