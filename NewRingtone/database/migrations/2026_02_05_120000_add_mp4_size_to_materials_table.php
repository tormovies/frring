<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', static function (Blueprint $table) {
            $table->unsignedBigInteger('mp4_size')->nullable()->after('mp4_duration');
        });
    }

    public function down(): void
    {
        Schema::table('materials', static function (Blueprint $table) {
            $table->dropColumn('mp4_size');
        });
    }
};
