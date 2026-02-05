<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'email_verified_at',
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
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Суперадмин может заходить с любого email (в т.ч. для локальной разработки)
        if ($this->hasRole('super_admin')) {
            return true;
        }
        return (
                str_ends_with($this->email, '@neurozvuk.ru')
                || str_ends_with($this->email, '@ringtone.com')
            ) && $this->hasVerifiedEmail();
    }

    // Связи
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'user_author')->withTimestamps();
    }

    public function authorRequests(): HasMany
    {
        return $this->hasMany(AuthorRequest::class);
    }

    public function authorModerations(): HasMany
    {
        return $this->hasMany(AuthorModeration::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopeVerified($query)
    {
        return $query->whereIn('status', ['email_verified', 'active']);
    }

    // Проверки
    public function isActive(): bool
    {
        // Проверяем, существует ли колонка status
        if (!isset($this->status)) {
            // Если колонки нет, считаем пользователя активным, если email подтвержден
            return $this->hasVerifiedEmail();
        }
        return $this->status === 'active';
    }

    public function isBlocked(): bool
    {
        // Проверяем, существует ли колонка status
        if (!isset($this->status)) {
            return false;
        }
        return $this->status === 'blocked';
    }

    public function isInactive(): bool
    {
        // Проверяем, существует ли колонка status
        if (!isset($this->status)) {
            return false;
        }
        return $this->status === 'inactive';
    }

    public function canCreateMaterials(): bool
    {
        return $this->isActive() && $this->authors()->exists();
    }

    /**
     * Проверка, является ли пользователь админом
     * Админ - это пользователь, который имеет роль Admin через Spatie Permission
     */
    public function isAdmin(): bool
    {
        try {
            // Проверяем наличие роли Admin (через Spatie Permission)
            // Используем строгую проверку - только если роль точно существует
            // Проверяем через getRoleNames() для более надежной проверки
            $roles = $this->getRoleNames();
            
            // Если ролей нет, пользователь точно не админ
            if ($roles->isEmpty()) {
                return false;
            }
            
            // Проверяем наличие роли Admin (с учетом регистра)
            // Только точные совпадения: 'Admin', 'admin', 'super_admin'
            foreach ($roles as $role) {
                $roleName = trim($role);
                // Строгая проверка - только точные совпадения
                if ($roleName === 'Admin' || $roleName === 'admin' || $roleName === 'super_admin') {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            // Если возникла ошибка при проверке ролей, считаем что пользователь не админ
            return false;
        }
    }
}
