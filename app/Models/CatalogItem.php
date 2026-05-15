<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasActiveFlag;

class CatalogItem extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'type',
        'value',
        'color',
        'position',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
