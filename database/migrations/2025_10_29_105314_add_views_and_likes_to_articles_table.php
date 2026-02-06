<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', static function (Blueprint $table) {
            $table->unsignedBigInteger('views')->default(0)->after('status');
            $table->unsignedBigInteger('likes')->default(0)->after('views');
        });
    }

    public function down(): void
    {
        Schema::table('articles', static function (Blueprint $table) {
            $table->dropColumn(['views', 'likes']);
        });
    }
};
