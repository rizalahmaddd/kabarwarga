<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\DuesType;
use App\Models\Household;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class OnlinePaymentTest extends TestCase
{
    use RefreshDatabase;

    private Household $house;

    private DuesType $monthly;

    private BankAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->house = Household::create(['number' => 'A-7', 'head_name' => 'Pak Budi']);
        $this->monthly = DuesType::create(['name' => 'Keamanan', 'amount' => 25000, 'frequency' => DuesType::MONTHLY]);
        $this->account = BankAccount::create(['bank_name' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'Kas RT']);
    }

    private function submit(array $overrides = []): TestResponse
    {
        return $this->post('/bayar', $overrides + [
            'household_id' => $this->house->id,
            'dues_type_id' => $this->monthly->id,
            'periods' => ['2026-01', '2026-02'],
            'bank_account_id' => $this->account->id,
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
            'payer_name' => 'Budi',
        ]);
    }

    public function test_pay_page_is_closed_without_active_account(): void
    {
        $this->account->update(['is_active' => false]);

        $this->get('/bayar')->assertOk()->assertSee('Pembayaran online belum dibuka');
    }

    public function test_resident_submits_proof_without_login(): void
    {
        $this->get("/bayar?rumah={$this->house->id}&jenis={$this->monthly->id}&tahun=2026")
            ->assertOk()
            ->assertSee('1234567890')
            ->assertSee('Kirim Bukti Pembayaran');

        $response = $this->submit();

        $submission = PaymentSubmission::first();
        $response->assertRedirect("/bayar/cek/{$submission->code}");
        $this->assertSame(['2026-01', '2026-02'], $submission->periods);
        $this->assertSame(50000, $submission->total());
        $this->assertTrue($submission->isPending());
        Storage::disk('local')->assertExists($submission->proof_path);
        $this->assertSame(0, Payment::count());

        $this->get("/bayar/cek/{$submission->code}")->assertOk()->assertSee('Menunggu dicek pengurus')->assertSee('Rp50.000');
        $this->get("/iuran?jenis={$this->monthly->id}&tahun=2026")->assertSee('dicek');
        $this->get("/admin/konfirmasi/{$submission->id}/bukti")->assertRedirect('/masuk');
    }

    public function test_paid_pending_and_inapplicable_months_are_refused(): void
    {
        Payment::create(['household_id' => $this->house->id, 'dues_type_id' => $this->monthly->id, 'period' => '2026-03', 'amount' => 25000, 'paid_on' => now()]);
        $this->submit()->assertSessionHasNoErrors();

        $this->submit(['periods' => ['2026-02', '2026-04']])->assertSessionHasErrors('periods');
        $this->submit(['periods' => ['2026-03']])->assertSessionHasErrors('periods');
        $this->submit(['periods' => []])->assertSessionHasErrors('periods');
        $this->submit(['proof' => null, 'periods' => ['2026-05']])->assertSessionHasErrors('proof');
        $this->submit(['proof' => UploadedFile::fake()->create('virus.exe', 10), 'periods' => ['2026-05']])->assertSessionHasErrors('proof');

        $this->monthly->update(['starts_on' => '2026-06-01']);
        $this->submit(['periods' => ['2026-05']])->assertSessionHasErrors('periods');

        $this->account->update(['is_active' => false]);
        $this->submit(['periods' => ['2026-07']])->assertSessionHasErrors('bank_account_id');

        $this->assertSame(1, PaymentSubmission::count());
    }

    public function test_admin_approves_and_months_become_paid(): void
    {
        $admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
        $this->submit(['periods' => ['2026-01', '2026-02', '2026-03']]);
        $submission = PaymentSubmission::first();

        // Cash for March was recorded meanwhile, so approving must not double count it.
        Payment::create(['household_id' => $this->house->id, 'dues_type_id' => $this->monthly->id, 'period' => '2026-03', 'amount' => 25000, 'paid_on' => now()]);

        $this->actingAs($admin);
        $this->get('/admin')->assertSee('1 bukti bayar dari warga menunggu konfirmasi');
        $this->get('/admin/konfirmasi')->assertOk()->assertSee('A-7')->assertSee('Rp75.000')->assertSee('Sudah tercatat lunas sebelumnya');
        $this->get("/admin/konfirmasi/{$submission->id}/bukti")->assertOk();

        $this->post("/admin/konfirmasi/{$submission->id}/terima", ['paid_on' => now()->toDateString(), 'periods' => ['2026-01', '2026-02', '2026-03']])
            ->assertSessionHas('warning');

        $this->assertSame(PaymentSubmission::APPROVED, $submission->fresh()->status);
        $this->assertSame($admin->id, $submission->fresh()->reviewed_by);
        $this->assertSame(3, Payment::count());
        $jan = Payment::where('period', '2026-01')->first();
        $this->assertSame(25000, $jan->amount);
        $this->assertSame('transfer', $jan->method);
        $this->assertStringContainsString($submission->code, $jan->note);

        $this->post("/admin/konfirmasi/{$submission->id}/terima", ['paid_on' => now()->toDateString(), 'periods' => ['2026-01']])->assertSessionHas('warning');
        $this->assertSame(3, Payment::count());

        $this->get("/bayar/cek/{$submission->code}")->assertSee('sudah tercatat lunas');
    }

    public function test_admin_approves_only_some_months(): void
    {
        $admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
        $this->submit(['periods' => ['2026-01', '2026-02', '2026-03']]);
        $submission = PaymentSubmission::first();
        $this->actingAs($admin);
        $url = "/admin/konfirmasi/{$submission->id}/terima";

        $this->post($url, ['paid_on' => now()->toDateString(), 'periods' => []])->assertSessionHasErrorsIn("approve{$submission->id}", 'periods');
        $this->post($url, ['paid_on' => now()->toDateString(), 'periods' => ['2026-01', '2026-02']])->assertSessionHasErrorsIn("approve{$submission->id}", 'reject_reason');
        $this->post($url, ['paid_on' => now()->toDateString(), 'periods' => ['2026-01', '2026-09']])->assertSessionHasErrorsIn("approve{$submission->id}", 'periods.1');
        $this->assertTrue($submission->fresh()->isPending());

        $this->post($url, ['paid_on' => now()->toDateString(), 'periods' => ['2026-01', '2026-02'], 'reject_reason' => 'Transfer hanya Rp50.000'])
            ->assertSessionHas('status');

        $submission->refresh();
        $this->assertSame(['2026-01', '2026-02'], $submission->acceptedPeriods());
        $this->assertSame(['2026-03'], $submission->declinedPeriods());
        $this->assertSame(50000, $submission->acceptedTotal());
        $this->assertEqualsCanonicalizing(['2026-01', '2026-02'], Payment::pluck('period')->all());

        $this->get('/admin/konfirmasi?status=diterima')->assertSee('Diterima sebagian');

        auth()->logout();
        $this->get("/bayar/cek/{$submission->code}")->assertSee('diterima sebagian')->assertSee('Mar 2026')->assertSee('Transfer hanya Rp50.000');
        $this->submit(['periods' => ['2026-03']])->assertSessionHasNoErrors();
    }

    public function test_admin_can_undo_an_approval(): void
    {
        $admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
        $this->submit();
        $submission = PaymentSubmission::first();
        Payment::create(['household_id' => $this->house->id, 'dues_type_id' => $this->monthly->id, 'period' => '2026-05', 'amount' => 25000, 'paid_on' => now()]);

        $this->actingAs($admin);
        $this->post("/admin/konfirmasi/{$submission->id}/terima", ['paid_on' => now()->toDateString(), 'periods' => ['2026-01', '2026-02']]);
        $this->assertSame(3, Payment::count());

        $this->post("/admin/konfirmasi/{$submission->id}/batal")->assertSessionHas('status');

        $this->assertTrue($submission->fresh()->isPending());
        $this->assertNull($submission->fresh()->approved_periods);
        $this->assertSame(['2026-05'], Payment::pluck('period')->all());
        $this->post("/admin/konfirmasi/{$submission->id}/batal")->assertNotFound();
    }

    public function test_pending_submission_blocks_deleting_house_and_dues_type(): void
    {
        $admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
        $this->submit();
        $this->actingAs($admin);

        $this->delete("/admin/rumah/{$this->house->id}")->assertSessionHas('warning');
        $this->delete("/admin/jenis-iuran/{$this->monthly->id}")->assertSessionHas('warning');
        $this->assertSame(1, PaymentSubmission::count());
    }

    public function test_years_far_outside_range_are_refused(): void
    {
        $this->submit(['periods' => ['2099-01']])->assertSessionHasErrors('periods');
        $this->submit(['periods' => ['1999-01']])->assertSessionHasErrors('periods');
        $this->assertSame(0, PaymentSubmission::count());
    }

    public function test_admin_rejects_and_resident_can_resend(): void
    {
        $admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
        $this->submit();
        $submission = PaymentSubmission::first();

        $this->actingAs($admin);
        $this->post("/admin/konfirmasi/{$submission->id}/tolak", [])->assertSessionHasErrorsIn("reject{$submission->id}", 'reject_reason');
        $this->post("/admin/konfirmasi/{$submission->id}/tolak", ['reject_reason' => 'Dana belum masuk'])->assertSessionHas('status');
        $this->assertSame(PaymentSubmission::REJECTED, $submission->fresh()->status);
        $this->assertSame(0, Payment::count());

        auth()->logout();
        $this->get("/bayar/cek/{$submission->code}")->assertSee('Dana belum masuk')->assertSee('Kirim ulang bukti');
        $this->submit()->assertSessionHasNoErrors();
        $this->assertSame(2, PaymentSubmission::count());
    }

    public function test_one_time_dues_submission(): void
    {
        $once = DuesType::create(['name' => '17-an', 'amount' => 50000, 'frequency' => DuesType::ONCE]);

        $this->submit(['dues_type_id' => $once->id, 'periods' => null])->assertSessionHasNoErrors();
        $this->assertSame([''], PaymentSubmission::first()->periods);
        $this->assertSame(50000, PaymentSubmission::first()->total());

        $this->submit(['dues_type_id' => $once->id])->assertSessionHasErrors('periods');
        $this->get("/bayar?rumah={$this->house->id}&jenis={$once->id}")->assertSee('sedang dicek pengurus');
    }

    public function test_admin_manages_bank_accounts(): void
    {
        $admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
        $this->actingAs($admin);

        foreach (['/admin/rekening', '/admin/rekening/create', "/admin/rekening/{$this->account->id}/edit", '/admin/konfirmasi', '/admin/konfirmasi?status=ditolak'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->post('/admin/rekening', ['bank_name' => 'DANA', 'is_active' => 1])->assertSessionHasErrors('account_number');
        $this->post('/admin/rekening', ['bank_name' => 'QRIS', 'qris' => UploadedFile::fake()->image('qris.png'), 'is_active' => 1])
            ->assertRedirect('/admin/rekening');

        $qris = BankAccount::where('bank_name', 'QRIS')->first();
        Storage::disk('public')->assertExists($qris->qris_path);

        $this->put("/admin/rekening/{$this->account->id}", ['bank_name' => 'BCA', 'account_number' => '999', 'is_active' => 0]);
        $this->assertFalse($this->account->fresh()->is_active);

        $path = $qris->qris_path;
        $this->delete("/admin/rekening/{$qris->id}");
        Storage::disk('public')->assertMissing($path);
        $this->assertSame(1, BankAccount::count());
    }
}
