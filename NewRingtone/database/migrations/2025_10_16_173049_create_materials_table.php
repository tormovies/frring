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
        Schema::create('materials', static function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('type_id')
                ->nullable()
                ->constrained('types')
                ->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('img')->nullable();
            $table->string('title');
            $table->string('description', 500);
            $table->text('content')->nullable();
            $table->text('copyright')->nullable();

            $table->string('mp4')->nullable();
            $table->integer('mp4_bitrate')->nullable();
            $table->integer('mp4_duration')->nullable();

            $table->string('m4r30')->nullable();
            $table->integer('m4r30_bitrate')->nullable();
            $table->integer('m4r30_duration')->nullable();

            $table->string('m4r40')->nullable();
            $table->integer('m4r40_bitrate')->nullable();
            $table->integer('m4r40_duration')->nullable();

            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('downloads')->default(0);

            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('material_author', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete();
            $table->unique(['material_id', 'author_id']);
            $table->timestamps();
        });

        Schema::create('material_category', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->unique(['material_id', 'category_id']);
            $table->timestamps();
        });

        Schema::create('material_tag', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->unique(['material_id', 'tag_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_tag');
        Schema::dropIfExists('material_category');
        Schema::dropIfExists('material_author');
        Schema::dropIfExists('materials');
    }
};
