<?php

namespace App\Models;

use App\Models\Concerns\HasActiveFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageTemplate extends Model
{
    use HasActiveFlag;

    protected $fillable = [
        'name',
        'description',
        'content',
        'kicker',
        'includes_link',
        'link_mode',
        'link_validity_days',
        'position',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'includes_link' => 'boolean',
        'link_validity_days' => 'integer',
    ];

    public function sends(): HasMany
    {
        return $this->hasMany(MessageSend::class);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->withoutGlobalScope('active')
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }

    public static function placeholders(): array
    {
        return [
            '{prefijo}'       => 'Prefijo del registro (Sr., Sra., etc.) — vacío si no tiene',
            '{nombre}'        => 'Nombre del grupo o familia',
            '{link}'          => 'URL del link de revisión / confirmación',
            '{evento}'        => 'Nombre del evento (desde Configuración)',
            '{fecha_evento}'  => 'Fecha del evento (desde Configuración)',
            '{hora_evento}'   => 'Hora de la recepción (desde Configuración)',
            '{equipo}'        => 'Nombre del equipo o planner (desde Configuración)',
            '{dias_vigencia}' => 'Días de vigencia del link (desde Configuración)',
            '{accesos}'       => 'Nota sobre accesos / QR (desde Configuración)',
        ];
    }

    public function hasLinkPlaceholder(): bool
    {
        return str_contains($this->content, '{link}');
    }

    public function render(Guest $guest, ?string $linkUrl = null): string
    {
        $prefix = trim((string) $guest->prefix);

        $rendered = strtr($this->content, [
            '{prefijo}'       => $prefix,
            '{nombre}'        => $guest->name,
            '{link}'          => $linkUrl ?? '',
            '{evento}'        => Setting::get('event_name', 'XV años de Zugeily'),
            '{fecha_evento}'  => Setting::get('event_date', '1 de agosto de 2026'),
            '{hora_evento}'   => Setting::get('event_time', '3:30 PM'),
            '{equipo}'        => Setting::get('team_name', 'Event Planner'),
            '{dias_vigencia}' => Setting::get('link_validity_days', '7'),
            '{accesos}'       => Setting::get('access_info_text', ''),
        ]);

        return preg_replace('/[ ]{2,}/', ' ', $rendered);
    }
}
