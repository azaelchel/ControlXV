<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Concerns\HasActiveFlag;

class Guest extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'group_name',
        'prefix',
        'name',
        'category',
        'status',
        'phone',
        'adults',
        'adolescents',
        'children',
        'sponsor',
        'whatsapp_2_months',
        'whatsapp_1_month',
        'whatsapp_15_days',
        'notes',
        'active',
    ];

    protected $appends = [
        'total_people',
        'display_name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected function totalPeople(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->adults + (int) $this->adolescents + (int) $this->children,
        );
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(collect([$this->prefix, $this->name])->filter()->implode(' ')),
        );
    }
}
