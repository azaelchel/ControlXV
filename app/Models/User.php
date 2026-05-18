<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public static function moduleLabels(): array
    {
        return [
            'dashboard' => 'Resumen',
            'guests' => 'Familias o grupos',
            'companions' => 'Invitados',
            'tables' => 'Mesas confirmadas',
            'catalogs' => 'Catálogos',
            'system_transfer' => 'Respaldo',
            'users' => 'Usuarios',
        ];
    }

    public static function defaultPermissions(): array
    {
        return collect(self::moduleLabels())
            ->mapWithKeys(fn (string $_label, string $key) => [$key => true])
            ->all();
    }

    public function normalizedPermissions(): array
    {
        $stored = is_array($this->permissions) ? $this->permissions : [];

        return collect(self::defaultPermissions())
            ->mapWithKeys(fn (bool $default, string $key) => [$key => (bool) ($stored[$key] ?? $default)])
            ->all();
    }

    public function canAccessModule(string $module): bool
    {
        return (bool) ($this->normalizedPermissions()[$module] ?? false);
    }

    public function preferredHomeRouteName(): string
    {
        foreach (array_keys(self::moduleLabels()) as $module) {
            if (! $this->canAccessModule($module)) {
                continue;
            }

            return match ($module) {
                'dashboard' => 'dashboard',
                'guests' => 'guests.index',
                'companions' => 'companions.index',
                'tables' => 'tables.index',
                'catalogs' => 'catalogs.index',
                'system_transfer' => 'system-transfer.edit',
                'users' => 'users.index',
                default => 'profile.edit',
            };
        }

        return 'profile.edit';
    }

    public function preferredHomeUrl(): string
    {
        $routeName = $this->preferredHomeRouteName();

        return Route::has($routeName) ? route($routeName, absolute: false) : route('profile.edit', absolute: false);
    }
}
