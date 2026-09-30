<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', Rule::in(array_keys(Post::CATEGORIES))],
            'body' => ['required', 'string', 'max:20000'],
            'image' => ['nullable', 'image', 'max:3072'],
            'remove_image' => ['boolean'],
            'popup_image' => ['nullable', 'image', 'max:4096'],
            'remove_popup_image' => ['boolean'],
            'event_starts_at' => ['nullable', 'required_if:category,kegiatan', 'date'],
            'event_location' => ['nullable', 'string', 'max:160'],
            'is_pinned' => ['boolean'],
            'is_popup' => ['boolean'],
            'action' => ['required', Rule::in(['publish', 'draft'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['event_starts_at.required_if' => 'Isi waktu kegiatan supaya warga tahu kapan datang.'];
    }

    /**
     * Validated data with images stored, replaced images removed and publish state resolved.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->validated();
        $post = $this->route('post');
        $post = $post instanceof Post ? $post : null;

        $data['is_pinned'] = $this->boolean('is_pinned');
        $data['is_popup'] = $this->boolean('is_popup');

        if ($data['action'] === 'draft') {
            $data['published_at'] = null;
        } elseif (! $post?->isPublished()) {
            $data['published_at'] = now();
        }

        if ($data['category'] !== 'kegiatan') {
            $data['event_starts_at'] = null;
            $data['event_location'] = null;
        }

        if ($this->hasFile('image') || $this->boolean('remove_image')) {
            if ($post?->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $data['image_path'] = $this->hasFile('image') ? $this->file('image')->store('kabar', 'public') : null;
        }

        if ($this->hasFile('popup_image') || $this->boolean('remove_popup_image')) {
            if ($post?->popup_image_path) {
                Storage::disk('public')->delete($post->popup_image_path);
            }
            $data['popup_image_path'] = $this->hasFile('popup_image') ? $this->file('popup_image')->store('popups', 'public') : null;
        }

        unset($data['image'], $data['remove_image'], $data['popup_image'], $data['remove_popup_image'], $data['action']);

        return $data;
    }
}
