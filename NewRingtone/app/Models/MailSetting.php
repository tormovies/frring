<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
    ];

    protected $casts = [
        'port' => 'integer',
    ];

    /**
     * Получить единственную запись настроек (singleton)
     */
    public static function instance(): self
    {
        try {
            return static::firstOrCreate(
                ['id' => 1],
                [
                    'mailer' => 'log',
                    'host' => '127.0.0.1',
                    'port' => 2525,
                    'encryption' => null,
                    'username' => null,
                    'password' => null,
                    'from_address' => 'hello@example.com',
                    'from_name' => 'Example',
                ]
            );
        } catch (\Exception $e) {
            // Если таблица не существует, создаем новый экземпляр с дефолтными значениями
            $instance = new static();
            $instance->id = 1;
            $instance->mailer = 'log';
            $instance->host = '127.0.0.1';
            $instance->port = 2525;
            $instance->encryption = null;
            $instance->username = null;
            $instance->password = null;
            $instance->from_address = 'hello@example.com';
            $instance->from_name = 'Example';
            return $instance;
        }
    }
}
