<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveFlag
{
    protected static function bootHasActiveFlag(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where($builder->qualifyColumn('active'), true);
        });
    }
}
