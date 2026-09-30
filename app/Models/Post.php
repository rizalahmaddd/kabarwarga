<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'category', 'body', 'image_path', 'popup_image_path', 'event_starts_at', 'event_location', 'is_pinned', 'is_popup', 'published_at', 'author_id'])]
class Post extends Model
{
    public const CATEGORIES = [
        'pengumuman' => 'Pengumuman',
        'berita' => 'Berita',
        'kegiatan' => 'Kegiatan',
        'iuran' => 'Iuran',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_popup' => 'boolean',
            'published_at' => 'immutable_datetime',
            'event_starts_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function popupImageUrl(): ?string
    {
        return $this->popup_image_path ? Storage::disk('public')->url($this->popup_image_path) : null;
    }

    public function bodyHtml(): HtmlString
    {
        return new HtmlString(Str::markdown($this->body, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]));
    }

    public function excerpt(int $limit = 160): string
    {
        // Hapus tag script, style, dan seluruh tag HTML
        $clean = preg_replace('/<(script|style)\b[^>]*>(.*?)<\/\1>/is', '', $this->body);
        $clean = strip_tags($clean);
        // Hapus entitas HTML jika ada
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = strip_tags($clean);
        // Bersihkan tanda markdown di awal baris seperti #, *, -, >
        $clean = preg_replace('/^[#*>-]\s+/m', '', $clean);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        return Str::limit($clean, $limit);
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'kabar';
        $slug = $base;
        $i = 2;

        while (self::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
