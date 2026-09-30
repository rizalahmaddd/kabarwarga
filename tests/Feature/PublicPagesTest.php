<?php

namespace Tests\Feature;

use App\Models\DuesType;
use App\Models\Household;
use App\Models\Payment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_render_with_empty_database(): void
    {
        foreach (['/', '/kabar', '/iuran', '/kas', '/masuk', '/bayar'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/')->assertSee('Belum ada kabar lain');
        $this->get('/iuran')->assertSee('Belum ada jenis iuran');
        $this->get('/kabar/tidak-ada')->assertNotFound()->assertSee('Halaman ini tidak ada');
    }

    public function test_drafts_are_hidden_from_residents(): void
    {
        $draft = Post::create(['title' => 'Rahasia', 'slug' => 'rahasia', 'category' => 'berita', 'body' => 'x']);

        $this->get('/kabar')->assertDontSee('Rahasia');
        $this->get("/kabar/{$draft->slug}")->assertNotFound();
    }

    public function test_dues_grid_shows_paid_months(): void
    {
        $house = Household::create(['number' => 'B-1', 'head_name' => 'Kepala B1']);
        $type = DuesType::create(['name' => 'Kebersihan', 'amount' => 15000, 'frequency' => DuesType::MONTHLY]);
        Payment::create(['household_id' => $house->id, 'dues_type_id' => $type->id, 'period' => now()->format('Y-m'), 'amount' => 15000, 'paid_on' => now()]);

        $this->get('/iuran')->assertOk()->assertSee('B-1')->assertSee('Lunas')->assertSee('Rp15.000');
        $this->get('/')->assertSee('1 dari 1 rumah sudah bayar');
        $this->get('/kas')->assertSee('Rp15.000');
    }

    public function test_popup_announcement_renders_on_home(): void
    {
        $popup = Post::create([
            'title' => 'Kerja Bakti Akbar',
            'slug' => 'kerja-bakti-akbar',
            'category' => 'kegiatan',
            'body' => 'Diharapkan seluruh warga berkumpul di lapangan.',
            'published_at' => now(),
            'is_popup' => true,
        ]);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('home-announcement-popup');
        $response->assertSee('Kerja Bakti Akbar');
        $response->assertSee(route('posts.show', $popup));
    }

    public function test_popup_with_image_renders_full_photo_mode(): void
    {
        $popup = Post::create([
            'title' => 'Flyer Acara Warga',
            'slug' => 'flyer-acara-warga',
            'category' => 'pengumuman',
            'body' => 'Informasi flyer poster lengkap.',
            'published_at' => now(),
            'is_popup' => true,
            'popup_image_path' => 'popups/flyer.jpg',
        ]);

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('home-announcement-popup');
        $response->assertSee(route('posts.show', $popup));
        $response->assertSee('popups/flyer.jpg');
    }
}
