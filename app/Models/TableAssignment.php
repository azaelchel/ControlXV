<?php

namespace App\Models;

use App\Models\Concerns\HasActiveFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableAssignment extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'event_table_id',
        'companion_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(EventTable::class, 'event_table_id');
    }

    public function companion(): BelongsTo
    {
        return $this->belongsTo(Companion::class);
    }
}
