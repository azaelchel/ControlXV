<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasActiveFlag;

class ConfirmedTable extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'table_number',
        'guest_group',
        'phone',
        'total_people',
        'assigned_seats',
        'available_seats',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
