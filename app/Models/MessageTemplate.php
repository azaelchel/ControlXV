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
        'position',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'includes_link' => 'boolean',
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
            '{nombre}'         => 'Nombre del grupo o familia',
            '{link}'           => 'URL del link de revisión / confirmación',
            '{fecha_evento}'   => 'Fecha del evento (1 de agosto de 2026)',
            '{dias_vigencia}'  => 'Días de vigencia del link (7)',
        ];
    }

    public function render(Guest $guest, ?string $linkUrl = null): string
    {
        return strtr($this->content, [
            '{nombre}'        => $guest->name,
            '{link}'          => $linkUrl ?? '',
            '{fecha_evento}'  => '1 de agosto de 2026',
            '{dias_vigencia}' => '7',
        ]);
    }
}
