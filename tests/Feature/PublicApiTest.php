<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Household;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    private Household $house;

    private DuesType $monthly;

    private BankAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->house = Household::create(['number' => 'A-7', 'head_name' => 'Pak Budi', 'phone' => '0812']);
        $this->monthly = DuesType::create(['name' => 'Keamanan', 'amount' => 25000, 'frequency' => DuesType::MONTHLY, 'starts_on' => now()->year.'-02-01']);
        $this->account = BankAccount::create(['bank_name' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'Kas RT']);
    }

    public function test_public_endpoints_work_with_empty_content(): void
    {
        foreach (['/api/v1/home', '/api/v1/settings', '/api/v1/posts', '/api/v1/households', '/api/v1/dues-types', '/api/v1/bank-accounts', '/api/v1/dues/ledger', '/api/v1/dues/cashbook'] as $url) {
            $this->getJson($url)->assertOk();
        }

        $this->getJson('/api/v1/households')->assertJsonPath('data.0.number', 'A-7')->assertJsonMissingPath('data.0.phone');
    }

    public function test_posts_hide_drafts(): void
    {
        $published = Post::create(['title' => 'Rapat', 'slug' => 'rapat', 'category' => 'pengumuman', 'body' => '**Penting**', 'published_at' => now()->subHour()]);
        Post::create(['title' => 'Draf', 'slug' => 'draf', 'category' => 'berita', 'body' => 'x']);

        $this->getJson('/api/v1/posts')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'rapat');
        $this->getJson('/api/v1/posts?category=berita')->assertJsonCount(0, 'data');
        $this->getJson("/api/v1/posts/{$published->slug}")->assertOk()->assertJsonPath('data.body_html', "<p><strong>Penting</strong></p>\n");
        $this->getJson('/api/v1/posts/draf')->assertNotFound();
    }

    public function test_ledger_and_status_report_month_states(): void
    {
        $year = now()->year;
        Payment::create(['household_id' => $this->house->id, 'dues_type_id' => $this->monthly->id, 'period' => "{$year}-03", 'amount' => 25000, 'paid_on' => now(), 'method' => 'tunai']);
        PaymentSubmission::create([
            'code' => 'ABCDEFGH', 'household_id' => $this->house->id, 'dues_type_id' => $this->monthly->id, 'periods' => ["{$year}-04"],
            'unit_amount' => 25000, 'bank_account_id' => $this->account->id, 'proof_path' => 'x.jpg',
        ]);

        $ledger = $this->getJson("/api/v1/dues/ledger?dues_type_id={$this->monthly->id}&year={$year}")->assertOk();
        $this->assertSame(
            ['not_applicable', 'unpaid', 'paid', 'pending'],
            array_slice(array_column($ledger->json('data.rows.0.months'), 'status'), 0, 4),
        );

        $this->getJson("/api/v1/households/{$this->house->id}/dues/{$this->monthly->id}?year={$year}")
            ->assertOk()
            ->assertJsonPath('data.months.2.status', 'paid')
            ->assertJsonPath('data.months.2.payment.amount', 25000)
            ->assertJsonPath('data.months.3.status', 'pending')
            ->assertJsonPath('data.recent_submissions.0.code', 'ABCDEFGH');
    }

    public function test_cashbook_totals(): void
    {
        Payment::create(['household_id' => $this->house->id, 'dues_type_id' => $this->monthly->id, 'period' => now()->format('Y-m'), 'amount' => 50000, 'paid_on' => now(), 'method' => 'tunai']);
        Expense::create(['spent_on' => now(), 'description' => 'Lampu', 'amount' => 20000]);

        $this->getJson('/api/v1/dues/cashbook')
            ->assertOk()
            ->assertJsonPath('data.total_in', 50000)
            ->assertJsonPath('data.total_out', 20000)
            ->assertJsonPath('data.closing', 30000)
            ->assertJsonPath('data.expenses.0.description', 'Lampu');
    }

    public function test_resident_submits_proof_and_checks_status(): void
    {
        $year = now()->year;
        $response = $this->postJson('/api/v1/payment-submissions', [
            'household_id' => $this->house->id,
            'dues_type_id' => $this->monthly->id,
            'periods' => ["{$year}-02", "{$year}-03"],
            'bank_account_id' => $this->account->id,
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
            'payer_name' => 'Budi',
            'phone' => '0812',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', PaymentSubmission::PENDING)
            ->assertJsonPath('data.total', 50000)
            ->assertJsonMissingPath('data.proof_url')
            ->assertJsonMissingPath('data.phone');

        $code = $response->json('data.code');
        Storage::disk('local')->assertExists(PaymentSubmission::first()->proof_path);

        $this->getJson('/api/v1/payment-submissions/'.strtolower($code))->assertOk()->assertJsonPath('data.periods', ["{$year}-02", "{$year}-03"]);
        $this->getJson('/api/v1/payment-submissions/ZZZZZZZZ')->assertNotFound();

        $this->postJson('/api/v1/payment-submissions', [
            'household_id' => $this->house->id,
            'dues_type_id' => $this->monthly->id,
            'periods' => ["{$year}-03"],
            'bank_account_id' => $this->account->id,
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertUnprocessable()->assertJsonValidationErrors('periods');

        $this->postJson('/api/v1/payment-submissions', [
            'household_id' => $this->house->id,
            'dues_type_id' => $this->monthly->id,
            'periods' => ["{$year}-01"],
            'bank_account_id' => $this->account->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['proof']);
    }

    public function test_login_issues_token_and_logout_revokes_it(): void
    {
        User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);

        $this->postJson('/api/v1/auth/login', ['email' => 'b@example.com', 'password' => 'salah', 'device_name' => 'hp'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');

        $token = $this->postJson('/api/v1/auth/login', ['email' => 'b@example.com', 'password' => 'rahasia123', 'device_name' => 'hp'])
            ->assertOk()
            ->assertJsonPath('user.email', 'b@example.com')
            ->json('token');

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('data.name', 'Bendahara');
        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->getJson('/api/v1/admin/households')->assertUnauthorized();
    }
}
