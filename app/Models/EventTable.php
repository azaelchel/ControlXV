<?php

namespace App\Models;

use App\Models\Concerns\HasActiveFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventTable extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'name',
        'capacity',
        'table_type',
        'shape',
        'is_principal',
        'notes',
        'position_x',
        'position_y',
        'active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_principal' => 'boolean',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Asignaciones activas (el global scope de TableAssignment ya filtra active=true).
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TableAssignment::class);
    }

    public function occupiedSeats(): int
    {
        return $this->relationLoaded('assignments')
            ? $this->assignments->count()
            : $this->assignments()->count();
    }

    public function availableSeats(): int
    {
        return max(0, $this->capacity - $this->occupiedSeats());
    }
}
