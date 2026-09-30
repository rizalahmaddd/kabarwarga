<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'category' => ['nullable', Rule::in(array_keys(Post::CATEGORIES))],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $posts = Post::with('author')
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->orderByRaw('published_at is null desc')
            ->latest('published_at')
            ->latest('id')
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return PostResource::collection($posts);
    }

    public function store(PostRequest $request): JsonResponse
    {
        $data = $request->payload();
        $data['slug'] = Post::uniqueSlug($data['title']);
        $data['author_id'] = $request->user()->id;

        $post = Post::create($data);

        return (new PostResource($post->load('author')))
            ->additional(['message' => $this->savedMessage($post)])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post->load('author'));
    }

    public function update(PostRequest $request, Post $post): PostResource
    {
        $data = $request->payload();

        if ($data['title'] !== $post->title) {
            $data['slug'] = Post::uniqueSlug($data['title'], $post->id);
        }

        $post->update($data);

        return (new PostResource($post->load('author')))->additional(['message' => $this->savedMessage($post)]);
    }

    public function destroy(Post $post): JsonResponse
    {
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }
        if ($post->popup_image_path) {
            Storage::disk('public')->delete($post->popup_image_path);
        }
        $post->delete();

        return response()->json(['message' => "Kabar \"{$post->title}\" dihapus."]);
    }

    private function savedMessage(Post $post): string
    {
        return $post->isPublished()
            ? "\"{$post->title}\" sudah tampil di halaman warga."
            : "\"{$post->title}\" disimpan sebagai draf (belum tampil untuk warga).";
    }
}
