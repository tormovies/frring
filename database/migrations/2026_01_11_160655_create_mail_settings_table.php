<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mailer')->default('smtp');
            $table->string('host')->nullable();
            $table->integer('port')->default(2525);
            $table->string('encryption')->nullable(); // null, 'ssl', 'tls'
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->timestamps();
        });
        
        // Создаем одну запись по умолчанию
        \DB::table('mail_settings')->insert([
            'mailer' => 'log',
            'host' => '127.0.0.1',
            'port' => 2525,
            'encryption' => null,
            'username' => null,
            'password' => null,
            'from_address' => 'hello@example.com',
            'from_name' => 'Example',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
