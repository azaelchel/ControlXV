<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PublicGuestLink extends Model
{
    protected $fillable = [
        'guest_id',
        'token',
        'mode',
        'response',
        'closed_reason',
        'is_current',
        'generated_at',
        'expires_at',
        'opened_at',
        'responded_at',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
        'opened_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at instanceof Carbon
            && $this->expires_at->isPast()
            && $this->responded_at === null;
    }

    public function isLocked(): bool
    {
        return $this->responded_at !== null
            || $this->isExpired()
            || $this->closed_reason === 'replaced'
            || $this->closed_reason === 'cancelled'
            || $this->guest?->status === 'No asistirá';
    }

    public function daysRemaining(): int
    {
        if (! $this->expires_at instanceof Carbon) {
            return 0;
        }

        return max(0, now()->startOfDay()->diffInDays($this->expires_at->copy()->endOfDay(), false));
    }

    public function remainingTimeLabel(): string
    {
        if (! $this->expires_at instanceof Carbon || $this->isExpired()) {
            return 'Link vencido';
        }

        $seconds = max(0, now()->diffInSeconds($this->expires_at, false));

        if ($seconds < 60) {
            return 'menos de 1 minuto';
        }

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . ' ' . ($days === 1 ? 'día' : 'días');
        }

        if ($hours > 0) {
            $parts[] = $hours . ' ' . ($hours === 1 ? 'hora' : 'horas');
        }

        if ($days === 0 && $minutes > 0) {
            $parts[] = $minutes . ' ' . ($minutes === 1 ? 'minuto' : 'minutos');
        }

        return implode(' ', array_slice($parts, 0, 2));
    }

    public function modeLabel(): string
    {
        return match ($this->mode) {
            'invitation' => 'Registro y confirmación',
            'validation' => 'Validación final',
            'last_chance' => 'Última oportunidad',
            'access_qr' => 'QR de acceso anterior',
            'access_qr_v2' => 'QR de acceso',
            default => 'Sin definir',
        };
    }

    public function statusLabel(): string
    {
        if (in_array($this->mode, ['access_qr', 'access_qr_v2'], true)) {
            if ($this->closed_reason === 'cancelled') {
                return 'QR cancelado';
            }

            if ($this->opened_at instanceof Carbon) {
                return 'Abrió el link el '.$this->opened_at->format('d/m/Y H:i');
            }

            return 'QR enviado, sin abrir';
        }

        if ($this->response === 'confirmed' && $this->responded_at instanceof Carbon) {
            return 'Confirmó el '.$this->responded_at->format('d/m/Y H:i');
        }

        if ($this->response === 'validated' && $this->responded_at instanceof Carbon) {
            return 'Validó la información el '.$this->responded_at->format('d/m/Y H:i');
        }

        if ($this->response === 'declined' && $this->responded_at instanceof Carbon) {
            return 'Rechazó la invitación el '.$this->responded_at->format('d/m/Y H:i');
        }

        if ($this->closed_reason === 'replaced') {
            return 'Fue reemplazado por un link más reciente';
        }

        if ($this->closed_reason === 'cancelled') {
            return 'Fue cancelado manualmente';
        }

        if ($this->isExpired()) {
            return 'Venció sin respuesta';
        }

        if ($this->opened_at instanceof Carbon) {
            return 'Abrió el link el '.$this->opened_at->format('d/m/Y H:i').' y no lo terminó';
        }

        return 'Pendiente';
    }
}
