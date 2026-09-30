<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'category' => $this->category,
            'category_label' => $this->categoryLabel(),
            'excerpt' => $this->excerpt(),
            'body' => $this->body,
            'body_html' => $this->bodyHtml()->toHtml(),
            'image_url' => $this->imageUrl(),
            'popup_image_url' => $this->popupImageUrl(),
            'event_starts_at' => $this->event_starts_at?->toIso8601String(),
            'event_location' => $this->event_location,
            'is_pinned' => $this->is_pinned,
            'is_popup' => $this->is_popup,
            'is_published' => $this->isPublished(),
            'published_at' => $this->published_at?->toIso8601String(),
            'author' => new UserResource($this->whenLoaded('author')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
