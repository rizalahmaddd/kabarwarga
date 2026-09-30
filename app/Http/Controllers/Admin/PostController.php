<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->when($request->query('kategori'), fn ($q, $c) => $q->where('category', $c))
            ->orderByRaw('published_at is null desc')
            ->latest('published_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create(Request $request)
    {
        $post = new Post([
            'category' => array_key_exists($request->query('kategori'), Post::CATEGORIES) ? $request->query('kategori') : 'pengumuman',
        ]);

        return view('admin.posts.form', compact('post'));
    }

    public function store(PostRequest $request)
    {
        $data = $request->payload();
        $data['slug'] = Post::uniqueSlug($data['title']);
        $data['author_id'] = $request->user()->id;

        $post = Post::create($data);

        return redirect()->route('admin.posts.index')->with('status', $this->savedMessage($post));
    }

    public function edit(Post $post)
    {
        return view('admin.posts.form', compact('post'));
    }

    public function update(PostRequest $request, Post $post)
    {
        $data = $request->payload();

        if ($data['title'] !== $post->title) {
            $data['slug'] = Post::uniqueSlug($data['title'], $post->id);
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('status', $this->savedMessage($post));
    }

    public function destroy(Post $post)
    {
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }
        if ($post->popup_image_path) {
            Storage::disk('public')->delete($post->popup_image_path);
        }
        $post->delete();

        return redirect()->route('admin.posts.index')->with('status', "Kabar \"{$post->title}\" dihapus.");
    }

    private function savedMessage(Post $post): string
    {
        return $post->isPublished()
            ? "\"{$post->title}\" sudah tampil di halaman warga."
            : "\"{$post->title}\" disimpan sebagai draf (belum tampil untuk warga).";
    }
}
