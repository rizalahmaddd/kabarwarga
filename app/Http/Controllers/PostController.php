<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $category = array_key_exists($request->query('kategori'), Post::CATEGORIES) ? $request->query('kategori') : null;

        $posts = Post::published()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('public.posts.index', compact('posts', 'category'));
    }

    public function show(Post $post)
    {
        abort_unless($post->isPublished() || auth()->check(), 404);

        $related = Post::published()
            ->where('category', $post->category)
            ->whereKeyNot($post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('public.posts.show', compact('post', 'related'));
    }
}
