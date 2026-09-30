<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'category' => ['nullable', Rule::in(array_keys(Post::CATEGORIES))],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $posts = Post::published()
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return PostResource::collection($posts)->additional(['categories' => Post::CATEGORIES]);
    }

    public function show(Post $post): PostResource
    {
        abort_unless($post->isPublished(), 404);

        $related = Post::published()
            ->where('category', $post->category)
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return (new PostResource($post))->additional(['related' => PostResource::collection($related)]);
    }
}
