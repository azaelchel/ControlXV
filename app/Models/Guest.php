<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Concerns\HasActiveFlag;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

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
        'public_link_token',
        'public_link_mode',
        'public_link_response',
        'public_link_generated_at',
        'public_link_expires_at',
        'public_link_opened_at',
        'public_link_responded_at',
    ];

    protected $appends = [
        'total_people',
        'display_name',
    ];

    protected $casts = [
        'active' => 'boolean',
        'public_link_generated_at' => 'datetime',
        'public_link_expires_at' => 'datetime',
        'public_link_opened_at' => 'datetime',
        'public_link_responded_at' => 'datetime',
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

    public function canGeneratePublicLink(): bool
    {
        return in_array($this->status, ['Invitacion Enviada', 'Confirmado'], true);
    }

    public function publicLinks(): HasMany
    {
        return $this->hasMany(PublicGuestLink::class)->latest('generated_at');
    }

    public function currentPublicLink(): HasOne
    {
        return $this->hasOne(PublicGuestLink::class)->where('is_current', true)->latestOfMany('generated_at');
    }

    public function publicLinkIsActive(): bool
    {
        return $this->public_link_token !== null
            && $this->public_link_expires_at instanceof Carbon
            && $this->public_link_expires_at->isFuture()
            && $this->public_link_responded_at === null;
    }

    public function publicLinkIsExpired(): bool
    {
        return $this->public_link_token !== null
            && $this->public_link_expires_at instanceof Carbon
            && $this->public_link_expires_at->isPast()
            && $this->public_link_responded_at === null;
    }

    public function publicLinkIsLocked(): bool
    {
        return $this->status === 'No asistirá'
            || $this->publicLinkIsExpired()
            || $this->public_link_expires_at instanceof Carbon && $this->public_link_expires_at->isPast() && $this->public_link_responded_at === null
            || $this->public_link_responded_at !== null;
    }

    public function publicLinkDaysRemaining(): int
    {
        if (! $this->public_link_expires_at instanceof Carbon) {
            return 0;
        }

        return max(0, now()->startOfDay()->diffInDays($this->public_link_expires_at->copy()->endOfDay(), false));
    }

    public function publicLinkModeLabel(): string
    {
        return match ($this->public_link_mode) {
            'invitation' => 'Registro y confirmación',
            'validation' => 'Validación final',
            default => 'Sin definir',
        };
    }

    public function publicLinkStatusLabel(): string
    {
        if ($this->public_link_token === null) {
            return 'Sin link generado';
        }

        if ($this->public_link_response === 'confirmed' && $this->public_link_responded_at instanceof Carbon) {
            return 'Confirmó el '.$this->public_link_responded_at->format('d/m/Y H:i');
        }

        if ($this->public_link_response === 'validated' && $this->public_link_responded_at instanceof Carbon) {
            return 'Validó la información el '.$this->public_link_responded_at->format('d/m/Y H:i');
        }

        if ($this->public_link_response === 'declined' && $this->public_link_responded_at instanceof Carbon) {
            return 'Rechazó la invitación el '.$this->public_link_responded_at->format('d/m/Y H:i');
        }

        if ($this->publicLinkIsExpired()) {
            return 'Venció sin respuesta';
        }

        if ($this->public_link_opened_at instanceof Carbon) {
            return 'Abrió el link el '.$this->public_link_opened_at->format('d/m/Y H:i').' y no ha terminado';
        }

        if ($this->publicLinkIsActive()) {
            return 'Vigente por '.$this->publicLinkDaysRemaining().' día(s)';
        }

        return 'Pendiente';
    }
}
