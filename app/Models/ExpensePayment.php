<?php

namespace App\Models;

use App\Models\Concerns\HasActiveFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpensePayment extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'expense_id',
        'amount',
        'paid_on',
        'method',
        'notes',
        'active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'date',
        'active' => 'boolean',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
