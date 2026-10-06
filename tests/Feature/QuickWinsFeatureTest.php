<?php

namespace Tests\Feature;

use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Household;
use App\Models\Payment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickWinsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_helpers_terbilang_and_clean_phone(): void
    {
        $this->assertSame('628123456789', clean_phone('08123456789'));
        $this->assertSame('628123456789', clean_phone('+62 812-3456-789'));
        $this->assertSame('628123456789', clean_phone('8123456789'));
        $this->assertSame('', clean_phone(null));

        $this->assertSame('nol', terbilang(0));
        $this->assertSame('lima puluh ribu', terbilang(50000));
        $this->assertSame('seratus dua puluh lima ribu', terbilang(125000));
        $this->assertSame('satu juta lima ratus ribu', terbilang(1500000));
    }

    public function test_public_can_view_payment_receipt(): void
    {
        $house = Household::create(['number' => 'A-01', 'head_name' => 'Bambang Sudiro', 'phone' => '08123456789']);
        $type = DuesType::create(['name' => 'Iuran Sampah & Keamanan', 'amount' => 50000, 'frequency' => DuesType::MONTHLY]);
        $admin = User::factory()->create(['name' => 'Pak RT']);

        $payment = Payment::create([
            'household_id' => $house->id,
            'dues_type_id' => $type->id,
            'period' => '2026-10',
            'amount' => 50000,
            'paid_on' => '2026-10-05',
            'method' => 'tunai',
            'recorded_by' => $admin->id,
        ]);

        $response = $this->get(route('receipt.show', $payment));

        $response->assertOk()
            ->assertSee($payment->receiptNumber())
            ->assertSee('Bambang Sudiro')
            ->assertSee('Rumah A-01')
            ->assertSee('Iuran Sampah & Keamanan')
            ->assertSee('Rp50.000')
            ->assertSee('Lima Puluh Ribu Rupiah')
            ->assertSee('LUNAS')
            ->assertSee('Cetak / Simpan PDF')
            ->assertSee('Bagikan ke WA');
    }

    public function test_admin_recording_payment_flashes_whatsapp_and_receipt_actions(): void
    {
        $admin = User::factory()->create();
        $house = Household::create(['number' => 'C-09', 'head_name' => 'Ibu Siti', 'phone' => '087712345678']);
        $type = DuesType::create(['name' => 'Iuran Bulanan', 'amount' => 30000, 'frequency' => DuesType::MONTHLY]);

        $response = $this->actingAs($admin)->post(route('admin.payments.store'), [
            'household_id' => $house->id,
            'dues_type_id' => $type->id,
            'amount' => 30000,
            'paid_on' => '2026-10-06',
            'method' => 'tunai',
            'periods' => ['2026-10'],
        ]);

        $response->assertRedirect(route('admin.home', ['rumah' => $house->id, 'jenis' => $type->id]))
            ->assertSessionHas('status')
            ->assertSessionHas('whatsapp_url')
            ->assertSessionHas('receipt_url');

        $whatsappUrl = session('whatsapp_url');
        $this->assertStringContainsString('6287712345678', $whatsappUrl);
        $this->assertStringContainsString('kuitansi', $whatsappUrl);
    }

    public function test_public_can_export_cashbook_csv(): void
    {
        $house = Household::create(['number' => 'B-02', 'head_name' => 'Pak Joko']);
        $type = DuesType::create(['name' => 'Iuran Warga', 'amount' => 25000, 'frequency' => DuesType::MONTHLY]);
        Payment::create([
            'household_id' => $house->id,
            'dues_type_id' => $type->id,
            'period' => '2026-05',
            'amount' => 25000,
            'paid_on' => '2026-05-10',
            'method' => 'tunai',
        ]);
        Expense::create([
            'description' => 'Beli sapu balai RT',
            'amount' => 15000,
            'spent_on' => '2026-05-12',
        ]);

        $response = $this->get(route('dues.cashbook.export', ['tahun' => 2026]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Pemasukan (Rp)', $content);
        $this->assertStringContainsString('Pengeluaran (Rp)', $content);
        $this->assertStringContainsString('Beli sapu balai RT', $content);
        $this->assertStringContainsString('Iuran Warga', $content);
    }

    public function test_guest_cannot_export_households_csv(): void
    {
        $this->get(route('admin.households.export'))->assertRedirect(route('login'));
    }

    public function test_admin_can_export_households_csv(): void
    {
        $admin = User::factory()->create();
        Household::create(['number' => 'D-12', 'head_name' => 'Pak Handoko', 'phone' => '081299998888', 'occupancy_status' => 'pemilik']);

        $response = $this->actingAs($admin)->get(route('admin.households.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Nomor Rumah', $content);
        $this->assertStringContainsString('Nama Kepala Keluarga', $content);
        $this->assertStringContainsString('D-12', $content);
        $this->assertStringContainsString('Pak Handoko', $content);
        $this->assertStringContainsString('081299998888', $content);
    }

    public function test_post_detail_page_includes_whatsapp_share_button(): void
    {
        $post = Post::create([
            'title' => 'Kerja Bakti Bersama Hari Minggu',
            'slug' => 'kerja-bakti-minggu',
            'category' => 'kegiatan',
            'body' => 'Diharapkan membawa cangkul dan sapu lidi.',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('posts.show', $post));

        $response->assertOk()
            ->assertSee('Bagikan ke WhatsApp')
            ->assertSee('Salin Tautan')
            ->assertSee('https://wa.me/?text=', false);
    }

    public function test_cashbook_page_includes_wa_copy_and_csv_export(): void
    {
        $response = $this->get(route('dues.cashbook'));

        $response->assertOk()
            ->assertSee('Salin Format WA')
            ->assertSee('Unduh CSV');
    }
}
