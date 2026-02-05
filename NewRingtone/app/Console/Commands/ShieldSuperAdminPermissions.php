<?php

namespace App\Console\Commands;

use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldSuperAdminPermissions extends Command
{
    protected $signature = 'shield:super-admin-permissions
                            {--panel=admin : Panel ID}';

    protected $description = 'Выдать роли super_admin все права (если в админке не видно разделов — выполните сначала: php artisan shield:generate --all --panel=admin)';

    public function handle(): int
    {
        $panelId = $this->option('panel');
        Filament::setCurrentPanel(Filament::getPanel($panelId));
        $guard = Utils::getFilamentAuthGuard() ?: 'web';

        $roleName = Utils::getSuperAdminName();
        $role = Role::firstOrCreate(
            ['name' => $roleName, 'guard_name' => $guard],
            ['name' => $roleName, 'guard_name' => $guard]
        );

        $permissions = Permission::where('guard_name', $guard)->pluck('id');
        if ($permissions->isEmpty()) {
            $this->warn('Прав пока нет. Сначала выполните: php artisan shield:generate --all --panel=admin');
            return self::FAILURE;
        }

        $role->syncPermissions($permissions);
        $this->info('Роли «' . $roleName . '» выданы все права (' . $permissions->count() . '). Обновите страницу админки.');
        return self::SUCCESS;
    }
}
