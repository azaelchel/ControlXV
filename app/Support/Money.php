<?php

namespace App\Support;

class Money
{
    /** Formatea un monto como pesos con centavos: $12,500.00 */
    public static function fmt($value): string
    {
        return '$' . number_format((float) $value, 2);
    }
}
