<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Author extends Model
{
    use HasFactory;

    protected $table = 'authors';

    protected $fillable = [
        'name',
        'slug',
        'img',
        'title',
        'description',
        'h1',
        'long_description',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(static function (Author $author) {
            if (empty($author->slug)) {
                $author->slug = Str::slug($author->name);
            }
        });
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'material_author')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_author')->withTimestamps();
    }

    public function moderations(): HasMany
    {
        return $this->hasMany(AuthorModeration::class);
    }

    public function pendingModerations(): HasMany
    {
        return $this->hasMany(AuthorModeration::class)->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function hasPendingModerations(): bool
    {
        return $this->pendingModerations()->exists();
    }
}
