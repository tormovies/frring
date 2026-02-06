<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'type_id',
        'img',
        'title',
        'description',
        'h1',
        'long_description',
        'content',
        'copyright',
        'mp4',
        'mp4_bitrate',
        'mp4_duration',
        'mp4_size',
        'm4r30',
        'm4r30_bitrate',
        'm4r30_duration',
        'm4r40',
        'm4r40_bitrate',
        'm4r40_duration',
        'views',
        'likes',
        'downloads',
        'status',
        'moderation_status',
        'rejection_reason',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'moderation_status' => 'string',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(static function (Material $material) {
            if (empty($material->slug)) {
                $material->slug = Str::slug($material->name);
            }
        });
    }

    public function hasFile(): bool
    {
        if (!$this->mp4) {
            return false;
        }
        // Если настроен CDN — не проверяем диск (экономия I/O при выводе списков)
        if (config('services.ringtone_cdn.url')) {
            return true;
        }
        return Storage::disk('mp4')->exists(ltrim($this->mp4, '/'));
    }

    public function fileUrl(): ?string
    {
        if (!$this->mp4) {
            return null;
        }
        $path = ltrim($this->mp4, '/');
        $base = rtrim((string) config('services.ringtone_cdn.url'), '/');
        // При настроенном CDN сразу отдаём URL CDN (без проверки диска — меньше I/O в списках)
        if ($base !== '') {
            $path = str_starts_with($path, 'mp3/') || str_starts_with($path, 'm4r/') ? $path : 'mp3/' . $path;
            return $base . '/' . $path;
        }
        if (Storage::disk('mp4')->exists($path)) {
            return Storage::disk('mp4')->url($path);
        }
        return null;
    }

    /** URL для скачивания m4r (если есть на CDN). */
    public function m4rFileUrl(): ?string
    {
        if (!$this->mp4) {
            return null;
        }
        $base = rtrim((string) config('services.ringtone_cdn.url'), '/');
        if ($base === '') {
            return null;
        }
        $path = ltrim($this->mp4, '/');
        $m4r = preg_replace('/\.(mp3|m4a)$/i', '.m4r', $path);
        return $base . '/m4r/' . $m4r;
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'material_author')
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'material_category')
            ->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'material_tag')
            ->withTimestamps();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopePendingModeration($query)
    {
        return $query->where('moderation_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('moderation_status', 'rejected');
    }

    public function isPending(): bool
    {
        return $this->moderation_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->moderation_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->moderation_status === 'rejected';
    }
}
