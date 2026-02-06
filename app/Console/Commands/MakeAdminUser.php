<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MakeAdminUser extends Command
{
    protected $signature = 'make:admin
                            {--email= : Email администратора}
                            {--password= : Пароль (если не указан — запросим)}
                            {--name=Admin : Имя}';

    protected $description = 'Создать пользователя-администратора и выдать роль super_admin. Вход: /admin или /login';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Email администратора');
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Укажите корректный email.');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $this->info('Пользователь с таким email уже есть (ID: ' . $user->id . '). Выдаём роль super_admin.');
        } else {
            $password = $this->option('password') ?: $this->secret('Пароль');
            if (strlen($password) < 8) {
                $this->error('Пароль не менее 8 символов.');
                return self::FAILURE;
            }
            $name = $this->option('name') ?: 'Admin';
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
            $this->info('Создан пользователь ID: ' . $user->id);
        }

        $role = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['name' => 'super_admin', 'guard_name' => 'web']
        );
        if (! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
            $this->info('Роль super_admin выдана.');
        } else {
            $this->info('Роль super_admin уже была выдана.');
        }

        $this->newLine();
        $this->info('Вход в админку:');
        $this->line('  URL: ' . url('/admin'));
        $this->line('  Логин: ' . $email);
        $this->line('  Пароль: (тот, что указали при создании)');
        $this->newLine();

        return self::SUCCESS;
    }
}
