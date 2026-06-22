<?php

namespace App\Support;

use App\Models\CatalogItem;
use App\Models\Companion;
use App\Models\Guest;

class CatalogOptions
{
    public const MAP = [
        'groups' => 'guest_groups',
        'prefixes' => 'prefixes',
        'categories' => 'categories',
        'statuses' => 'statuses',
        'sponsors' => 'sponsors',
        'followups' => 'followups',
        'companion_groups' => 'companion_groups',
        'table_types' => 'table_types',
        'table_shapes' => 'table_shapes',
        'expense_categories' => 'expense_categories',
    ];

    public static function all(): array
    {
        return [
            'groups' => self::values('guest_groups'),
            'prefixes' => self::values('prefixes'),
            'categories' => self::values('categories'),
            'statuses' => self::values('statuses'),
            'sponsors' => self::values('sponsors'),
            'followups' => self::values('followups'),
            'companion_groups' => self::values('companion_groups'),
            'table_types' => self::values('table_types'),
            'table_shapes' => self::values('table_shapes'),
            'expense_categories' => self::values('expense_categories'),
        ];
    }

    public static function syncDefaults(): void
    {
        foreach (self::MAP as $configKey => $type) {
            $values = match ($configKey) {
                'groups' => Guest::query()->select('group_name')->distinct()->pluck('group_name')->filter()->all(),
                'companion_groups' => Companion::query()->select('invited_group')->distinct()->pluck('invited_group')->filter()->all(),
                default => config("invitados.{$configKey}", []),
            };

            foreach (array_values(array_unique(array_filter($values))) as $index => $value) {
                $exists = CatalogItem::withoutGlobalScope('active')
                    ->where('type', $type)
                    ->where('value', $value)
                    ->exists();

                if ($exists) {
                    continue;
                }

                CatalogItem::create([
                    'type' => $type,
                    'value' => $value,
                    'position' => $index + 1,
                    'active' => true,
                ]);
            }
        }
    }

    public static function values(string $type): array
    {
        return CatalogItem::query()
            ->where('type', $type)
            ->orderBy('position')
            ->orderBy('value')
            ->pluck('value')
            ->all();
    }

    public static function labelMap(): array
    {
        return [
            'guest_groups' => 'Familias o grupos',
            'prefixes' => 'Prefijos',
            'categories' => 'Categorías',
            'statuses' => 'Estatus',
            'sponsors' => 'Padrinos',
            'followups' => 'Seguimiento',
            'companion_groups' => 'Familias o grupos vinculados',
            'table_types' => 'Tipos de mesa',
            'table_shapes' => 'Formas de mesa',
            'expense_categories' => 'Categorías de gasto',
        ];
    }
}
