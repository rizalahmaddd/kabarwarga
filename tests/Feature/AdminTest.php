<?php

namespace Tests\Feature;

use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Household;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
    }

    public function test_admin_area_requires_login(): void
    {
        $this->get('/admin')->assertRedirect('/masuk');
        $this->post('/masuk', ['email' => 'b@example.com', 'password' => 'rahasia123'])->assertRedirect('/admin');
        $this->post('/keluar')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_every_admin_page_renders(): void
    {
        $this->actingAs($this->admin);
        foreach (['/admin', '/admin/buku-iuran', '/admin/pembayaran', '/admin/kabar', '/admin/kabar/create', '/admin/rumah', '/admin/rumah/create',
            '/admin/jenis-iuran', '/admin/jenis-iuran/create', '/admin/pengeluaran', '/admin/pengeluaran/create', '/admin/pengaturan', '/admin/pengurus', '/admin/konfirmasi', '/admin/rekening', '/admin/rekening/create'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_recording_several_months_skips_already_paid(): void
    {
        $this->actingAs($this->admin);
        $house = Household::create(['number' => 'C-3', 'head_name' => 'Kepala C3']);
        $type = DuesType::create(['name' => 'Keamanan', 'amount' => 25000, 'frequency' => DuesType::MONTHLY]);

        $payload = ['household_id' => $house->id, 'dues_type_id' => $type->id, 'amount' => 25000, 'paid_on' => now()->toDateString(), 'method' => 'tunai'];

        $this->post('/admin/pembayaran', $payload + ['periods' => ['2026-01', '2026-02']])->assertSessionHas('status');
        $this->post('/admin/pembayaran', $payload + ['periods' => ['2026-02', '2026-03']])->assertSessionHas('status');
        $this->assertSame(3, Payment::count());

        $this->post('/admin/pembayaran', ['amount' => 50000, 'periods' => ['2026-05', '2026-06']] + $payload);
        $this->assertSame([25000, 25000], Payment::whereIn('period', ['2026-05', '2026-06'])->pluck('amount')->all());
        $this->post('/admin/pembayaran', ['amount' => 50001, 'periods' => ['2026-07', '2026-08', '2026-09']] + $payload);
        $this->assertSame(50001, (int) Payment::whereIn('period', ['2026-07', '2026-08', '2026-09'])->sum('amount'));

        $this->post('/admin/pembayaran', $payload)->assertSessionHasErrors('periods');
        $this->post('/admin/pembayaran', ['periods' => ['2026-13']] + $payload)->assertSessionHasErrors('periods.0');
    }

    public function test_one_time_dues_cannot_be_paid_twice(): void
    {
        $this->actingAs($this->admin);
        $house = Household::create(['number' => 'D-1', 'head_name' => 'Kepala D1']);
        $type = DuesType::create(['name' => '17-an', 'amount' => 50000, 'frequency' => DuesType::ONCE]);
        $payload = ['household_id' => $house->id, 'dues_type_id' => $type->id, 'amount' => 50000, 'paid_on' => now()->toDateString(), 'method' => 'transfer'];

        $this->post('/admin/pembayaran', $payload)->assertSessionHas('status');
        $this->post('/admin/pembayaran', $payload)->assertSessionHas('warning');
        $this->assertSame(1, Payment::count());
    }

    public function test_post_lifecycle(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $this->post('/admin/kabar', ['title' => 'Kerja bakti', 'category' => 'kegiatan', 'body' => 'Isi', 'action' => 'publish'])
            ->assertSessionHasErrors('event_starts_at');

        $this->post('/admin/kabar', [
            'title' => 'Kerja bakti', 'category' => 'kegiatan', 'body' => 'Isi', 'action' => 'draft',
            'event_starts_at' => '2026-10-10T07:00', 'image' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertRedirect('/admin/kabar');

        $post = Post::first();
        $this->assertNull($post->published_at);
        Storage::disk('public')->assertExists($post->image_path);

        $this->put("/admin/kabar/{$post->slug}", [
            'title' => 'Kerja bakti',
            'category' => 'kegiatan',
            'body' => 'Isi',
            'action' => 'publish',
            'event_starts_at' => '2026-10-10T07:00',
            'is_pinned' => 1,
            'is_popup' => 1,
            'popup_image' => UploadedFile::fake()->image('popup_poster.jpg'),
        ]);
        $this->assertTrue($post->fresh()->isPublished());
        $this->assertTrue($post->fresh()->is_pinned);
        $this->assertTrue($post->fresh()->is_popup);
        $this->assertNotNull($post->fresh()->popup_image_path);
        Storage::disk('public')->assertExists($post->fresh()->popup_image_path);

        $popupPath = $post->fresh()->popup_image_path;

        $this->delete("/admin/kabar/{$post->slug}");
        $this->assertSame(0, Post::count());
        Storage::disk('public')->assertMissing($post->image_path);
        Storage::disk('public')->assertMissing($popupPath);
    }

    public function test_household_and_dues_type_with_payments_are_protected_from_delete(): void
    {
        $this->actingAs($this->admin);
        $this->post('/admin/rumah', ['number' => 'E-5', 'head_name' => 'Kepala E5', 'is_active' => 1])->assertSessionHas('status');
        $this->post('/admin/rumah', ['number' => 'E-5', 'head_name' => 'Lain'])->assertSessionHasErrors('number');
        $this->post('/admin/jenis-iuran', ['name' => 'Kas', 'amount' => 10000, 'frequency' => 'bulanan', 'starts_on' => '2026-03', 'is_active' => 1])->assertSessionHas('status');

        $house = Household::first();
        $type = DuesType::first();
        $this->assertSame('2026-03-01', $type->starts_on->toDateString());
        Payment::create(['household_id' => $house->id, 'dues_type_id' => $type->id, 'period' => '2026-03', 'amount' => 10000, 'paid_on' => now()]);

        $this->delete("/admin/rumah/{$house->id}")->assertSessionHas('warning');
        $this->delete("/admin/jenis-iuran/{$type->id}")->assertSessionHas('warning');
        $this->assertSame(1, Household::count());
        $this->assertSame(1, DuesType::count());
    }

    public function test_expenses_settings_users_and_password(): void
    {
        $this->actingAs($this->admin);

        $this->post('/admin/pengeluaran', ['description' => 'Lampu jalan', 'amount' => 75000, 'spent_on' => now()->toDateString()])->assertRedirect('/admin/pengeluaran');
        $this->get('/kas')->assertSee('Lampu jalan');
        $this->put('/admin/pengeluaran/'.Expense::first()->id, ['description' => 'Lampu jalan gang 2', 'amount' => 80000, 'spent_on' => now()->toDateString()]);
        $this->assertSame(80000, Expense::first()->amount);

        $this->put('/admin/pengaturan', ['site_name' => 'RT 04 Uji', 'payment_info' => 'Tunai ke bendahara'])->assertSessionHas('status');
        $this->assertSame('RT 04 Uji', Setting::read('site_name'));
        $this->get('/')->assertSee('RT 04 Uji');

        $this->post('/admin/pengurus', ['name' => 'Sekretaris', 'email' => 's@example.com', 'password' => 'sandi-awal-1'])->assertSessionHas('status');
        $this->delete("/admin/pengurus/{$this->admin->id}")->assertSessionHas('warning');

        $this->put('/admin/pengaturan/sandi', ['current_password' => 'salah', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertSessionHasErrorsIn('password', 'current_password');
        $this->put('/admin/pengaturan/sandi', ['current_password' => 'rahasia123', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertSessionHas('status');
        $this->assertTrue(Hash::check('baru12345', $this->admin->fresh()->password));
    }
}
