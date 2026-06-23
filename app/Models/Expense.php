<?php

namespace App\Models;

use App\Models\Concerns\HasActiveFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Expense extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'name',
        'category',
        'provider',
        'total_amount',
        'due_date',
        'notes',
        'active',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'active' => 'boolean',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(ExpensePayment::class)->orderBy('paid_on')->orderBy('id');
    }

    public function paidAmount(): float
    {
        return (float) ($this->relationLoaded('payments')
            ? $this->payments->sum('amount')
            : $this->payments()->sum('amount'));
    }

    public function remaining(): float
    {
        return max(0, (float) $this->total_amount - $this->paidAmount());
    }

    public function status(): string
    {
        if ($this->remaining() <= 0 && (float) $this->total_amount > 0) {
            return 'Pagado';
        }

        if ($this->due_date instanceof Carbon && $this->due_date->isPast()) {
            return 'Vencido';
        }

        if ($this->paidAmount() > 0) {
            return 'Parcial';
        }

        return 'Pendiente';
    }
}
