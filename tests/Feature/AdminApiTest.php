<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Household;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
        Sanctum::actingAs($this->admin);
    }

    private function pendingSubmission(Household $house, DuesType $type, array $periods): PaymentSubmission
    {
        $account = BankAccount::firstOrCreate(['bank_name' => 'BCA'], ['account_number' => '123']);
        Storage::disk('local')->put('bukti-bayar/a.jpg', 'img');

        return PaymentSubmission::create([
            'code' => PaymentSubmission::newCode(), 'household_id' => $house->id, 'dues_type_id' => $type->id, 'periods' => $periods,
            'unit_amount' => $type->amount, 'bank_account_id' => $account->id, 'proof_path' => 'bukti-bayar/a.jpg',
        ])->refresh();
    }

    public function test_household_and_dues_type_crud_with_delete_protection(): void
    {
        $id = $this->postJson('/api/v1/admin/households', ['number' => 'E-5', 'head_name' => 'Kepala E5', 'phone' => '0812', 'is_active' => true])
            ->assertCreated()->assertJsonPath('data.phone', '0812')->json('data.id');
        $this->postJson('/api/v1/admin/households', ['number' => 'E-5', 'head_name' => 'Lain'])->assertUnprocessable()->assertJsonValidationErrors('number');
        $this->putJson("/api/v1/admin/households/{$id}", ['number' => 'E-5', 'head_name' => 'Baru', 'is_active' => false])
            ->assertOk()->assertJsonPath('data.is_active', false);

        $typeId = $this->postJson('/api/v1/admin/dues-types', ['name' => 'Kas', 'amount' => 10000, 'frequency' => 'bulanan', 'starts_on' => '2026-03', 'is_active' => 1])
            ->assertCreated()->assertJsonPath('data.starts_on', '2026-03')->json('data.id');

        Payment::create(['household_id' => $id, 'dues_type_id' => $typeId, 'period' => '2026-03', 'amount' => 10000, 'paid_on' => now()]);

        $this->getJson('/api/v1/admin/dues-types')->assertJsonPath('data.0.payments_count', 1)->assertJsonPath('data.0.payments_sum_amount', 10000);
        $this->deleteJson("/api/v1/admin/households/{$id}")->assertConflict();
        $this->deleteJson("/api/v1/admin/dues-types/{$typeId}")->assertConflict();
        $this->assertSame(1, Household::count());
    }

    public function test_recording_payments_splits_amount_and_skips_paid(): void
    {
        $house = Household::create(['number' => 'C-3', 'head_name' => 'Kepala C3']);
        $type = DuesType::create(['name' => 'Keamanan', 'amount' => 25000, 'frequency' => DuesType::MONTHLY]);
        $payload = ['household_id' => $house->id, 'dues_type_id' => $type->id, 'paid_on' => now()->toDateString(), 'method' => 'tunai'];

        $this->postJson('/api/v1/admin/payments', $payload + ['amount' => 50001, 'periods' => ['2026-01', '2026-02', '2026-03']])
            ->assertCreated()->assertJsonCount(3, 'created');
        $this->assertSame(50001, (int) Payment::sum('amount'));

        $this->postJson('/api/v1/admin/payments', $payload + ['amount' => 25000, 'periods' => ['2026-03']])
            ->assertOk()->assertJsonCount(0, 'created')->assertJsonCount(1, 'skipped');
        $this->postJson('/api/v1/admin/payments', $payload + ['amount' => 25000])->assertJsonValidationErrors('periods');
        $this->postJson('/api/v1/admin/payments', ['amount' => 25000, 'dues_type_id' => 999] + $payload)->assertJsonValidationErrors('dues_type_id');

        $this->getJson("/api/v1/admin/payments?household_id={$house->id}")->assertOk()->assertJsonCount(3, 'data')->assertJsonPath('data.0.household.number', 'C-3');

        $this->deleteJson('/api/v1/admin/payments/'.Payment::first()->id)->assertOk();
        $this->assertSame(2, Payment::count());
    }

    public function test_submission_review_flow(): void
    {
        $house = Household::create(['number' => 'A-7', 'head_name' => 'Pak Budi']);
        $type = DuesType::create(['name' => 'Keamanan', 'amount' => 25000, 'frequency' => DuesType::MONTHLY]);
        $submission = $this->pendingSubmission($house, $type, ['2026-01', '2026-02', '2026-03']);
        $url = "/api/v1/admin/payment-submissions/{$submission->id}";

        $this->getJson('/api/v1/admin/payment-submissions')->assertOk()
            ->assertJsonPath('data.0.code', $submission->code)
            ->assertJsonPath('counts.menunggu', 1)
            ->assertJsonPath('counts.diterima', 0);
        $this->getJson($url)->assertOk()->assertJsonPath('data.proof_url', route('api.v1.admin.payment-submissions.proof', $submission));
        $this->get("{$url}/proof")->assertOk();

        $this->postJson("{$url}/approve", ['paid_on' => now()->toDateString(), 'periods' => []])->assertJsonValidationErrors('periods');
        $this->postJson("{$url}/approve", ['paid_on' => now()->toDateString(), 'periods' => ['2026-01']])->assertJsonValidationErrors('reject_reason');

        $this->postJson("{$url}/approve", ['paid_on' => now()->toDateString(), 'periods' => ['2026-01', '2026-02'], 'reject_reason' => 'Kurang'])
            ->assertOk()
            ->assertJsonPath('data.status', PaymentSubmission::APPROVED)
            ->assertJsonPath('data.declined_periods', ['2026-03'])
            ->assertJsonCount(2, 'created');
        $this->assertSame(2, Payment::count());

        $this->postJson("{$url}/approve", ['paid_on' => now()->toDateString(), 'periods' => ['2026-01']])->assertConflict();
        $this->postJson("{$url}/reopen")->assertConflict();

        $this->postJson("{$url}/cancel")->assertOk()->assertJsonPath('removed_payments', 2)->assertJsonPath('data.status', PaymentSubmission::PENDING);
        $this->assertSame(0, Payment::count());

        $this->postJson("{$url}/reject", [])->assertJsonValidationErrors('reject_reason');
        $this->postJson("{$url}/reject", ['reject_reason' => 'Dana belum masuk'])->assertOk()->assertJsonPath('data.status', PaymentSubmission::REJECTED);
        $this->postJson("{$url}/reopen")->assertOk()->assertJsonPath('data.status', PaymentSubmission::PENDING);
    }

    public function test_one_time_submission_approval(): void
    {
        $house = Household::create(['number' => 'D-1', 'head_name' => 'Kepala D1']);
        $once = DuesType::create(['name' => '17-an', 'amount' => 50000, 'frequency' => DuesType::ONCE]);
        $submission = $this->pendingSubmission($house, $once, ['']);

        $this->getJson("/api/v1/admin/payment-submissions/{$submission->id}")->assertJsonPath('data.is_one_time', true)->assertJsonPath('data.periods', []);
        $this->postJson("/api/v1/admin/payment-submissions/{$submission->id}/approve", ['paid_on' => now()->toDateString()])->assertOk();
        $this->assertSame('', Payment::first()->period);
    }

    public function test_post_lifecycle_with_images(): void
    {
        $this->postJson('/api/v1/admin/posts', ['title' => 'Kerja bakti', 'category' => 'kegiatan', 'body' => 'Isi', 'action' => 'publish'])
            ->assertJsonValidationErrors('event_starts_at');

        $id = $this->post('/api/v1/admin/posts', [
            'title' => 'Kerja bakti', 'category' => 'kegiatan', 'body' => 'Isi', 'action' => 'draft',
            'event_starts_at' => '2026-10-10T07:00', 'image' => UploadedFile::fake()->image('foto.jpg'),
        ], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('data.is_published', false)->json('data.id');

        $post = Post::find($id);
        Storage::disk('public')->assertExists($post->image_path);

        $this->post("/api/v1/admin/posts/{$id}", [
            '_method' => 'PUT', 'title' => 'Kerja bakti RT', 'category' => 'kegiatan', 'body' => 'Isi', 'action' => 'publish',
            'event_starts_at' => '2026-10-10T07:00', 'is_popup' => 1, 'popup_image' => UploadedFile::fake()->image('poster.jpg'),
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.is_published', true)->assertJsonPath('data.slug', 'kerja-bakti-rt');

        $this->getJson('/api/v1/admin/posts')->assertJsonPath('data.0.author.name', 'Bendahara');

        $popup = $post->fresh()->popup_image_path;
        $this->deleteJson("/api/v1/admin/posts/{$id}")->assertOk();
        Storage::disk('public')->assertMissing($post->image_path);
        Storage::disk('public')->assertMissing($popup);
    }

    public function test_bank_accounts_require_number_or_qris(): void
    {
        $this->postJson('/api/v1/admin/bank-accounts', ['bank_name' => 'DANA', 'is_active' => 1])->assertJsonValidationErrors('account_number');

        $id = $this->post('/api/v1/admin/bank-accounts', ['bank_name' => 'QRIS', 'qris' => UploadedFile::fake()->image('qris.png'), 'is_active' => 1], ['Accept' => 'application/json'])
            ->assertCreated()->json('data.id');
        $path = BankAccount::find($id)->qris_path;
        Storage::disk('public')->assertExists($path);

        $this->putJson("/api/v1/admin/bank-accounts/{$id}", ['bank_name' => 'QRIS', 'remove_qris' => 1])->assertJsonValidationErrors('account_number');
        $this->deleteJson("/api/v1/admin/bank-accounts/{$id}")->assertOk();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_expenses_settings_users_and_password(): void
    {
        $id = $this->postJson('/api/v1/admin/expenses', ['description' => 'Lampu jalan', 'amount' => 75000, 'spent_on' => now()->toDateString()])
            ->assertCreated()->json('data.id');
        $this->putJson("/api/v1/admin/expenses/{$id}", ['description' => 'Lampu gang 2', 'amount' => 80000, 'spent_on' => now()->toDateString()])->assertOk();
        $this->assertSame(80000, Expense::first()->amount);
        $this->getJson('/api/v1/admin/expenses')->assertJsonPath('data.0.recorder.name', 'Bendahara');

        $this->putJson('/api/v1/admin/settings', ['site_name' => 'RT 04 Uji'])->assertOk()->assertJsonPath('data.site_name', 'RT 04 Uji');
        $this->assertSame('RT 04 Uji', Setting::read('site_name'));

        $this->postJson('/api/v1/admin/users', ['name' => 'Sekretaris', 'email' => 's@example.com', 'password' => 'sandi-awal-1'])->assertCreated();
        $this->deleteJson("/api/v1/admin/users/{$this->admin->id}")->assertConflict();
        $this->deleteJson('/api/v1/admin/users/'.User::where('email', 's@example.com')->value('id'))->assertOk();

        $this->putJson('/api/v1/admin/password', ['current_password' => 'salah', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])
            ->assertJsonValidationErrors('current_password');
        $this->putJson('/api/v1/admin/password', ['current_password' => 'rahasia123', 'password' => 'baru12345', 'password_confirmation' => 'baru12345'])->assertOk();
        $this->assertTrue(Hash::check('baru12345', $this->admin->fresh()->password));
    }
}
