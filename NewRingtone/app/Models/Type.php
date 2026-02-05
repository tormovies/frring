<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Type extends Model
{
    use HasFactory;

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

        static::saving(static function (Type $type) {
            if (empty($type->slug)) {
                $type->slug = Str::slug($type->name);
            }
        });
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
