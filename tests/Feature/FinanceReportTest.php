<?php

namespace Tests\Feature;

use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Household;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinanceReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Date::setTestNow('2026-10-15 09:00:00');
        Storage::fake('public');
        $this->admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
    }

    public function test_expense_receipt_is_stored_replaced_and_removed(): void
    {
        $this->actingAs($this->admin)->post(route('admin.expenses.store'), [
            'description' => 'Beli kantong sampah', 'category' => 'kebersihan', 'amount' => 45000, 'spent_on' => '2026-10-10',
            'receipt' => UploadedFile::fake()->image('nota.jpg'),
        ])->assertRedirect(route('admin.expenses.index'));

        $expense = Expense::sole();
        $first = $expense->receipt_path;
        Storage::disk('public')->assertExists($first);
        $this->get(route('dues.cashbook'))->assertSee('Kebersihan &amp; sampah', false)->assertSee('Lihat nota');

        $this->put(route('admin.expenses.update', $expense), [
            'description' => 'Beli kantong sampah', 'category' => 'kebersihan', 'amount' => 45000, 'spent_on' => '2026-10-10',
            'receipt' => UploadedFile::fake()->image('nota-baru.jpg'),
        ]);
        Storage::disk('public')->assertMissing($first);
        $second = $expense->fresh()->receipt_path;

        $this->put(route('admin.expenses.update', $expense), [
            'description' => 'Beli kantong sampah', 'category' => 'kebersihan', 'amount' => 45000, 'spent_on' => '2026-10-10', 'remove_receipt' => '1',
        ]);
        Storage::disk('public')->assertMissing($second);
        $this->assertNull($expense->fresh()->receipt_path);

        $this->post(route('admin.expenses.store'), ['description' => 'X', 'category' => 'ngawur', 'amount' => 1, 'spent_on' => '2026-10-10'])
            ->assertSessionHasErrors('category');
    }

    public function test_api_expense_defaults_category_and_exposes_receipt_url(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/v1/admin/expenses', ['description' => 'Honor satpam', 'amount' => 500000, 'spent_on' => '2026-10-01'])
            ->assertCreated()->assertJsonPath('data.category', 'lainnya')->assertJsonPath('data.receipt_url', null);
    }

    public function test_cash_report_for_a_month(): void
    {
        Setting::write(['chairman_name' => 'Pak Suparman', 'treasurer_name' => 'Bu Hendra']);
        $house = Household::factory()->create();
        $type = DuesType::create(['name' => 'Keamanan', 'amount' => 30000, 'frequency' => DuesType::MONTHLY, 'is_active' => true]);
        Payment::create(['household_id' => $house->id, 'dues_type_id' => $type->id, 'period' => '2026-09', 'amount' => 30000, 'paid_on' => '2026-09-05']);
        Payment::create(['household_id' => $house->id, 'dues_type_id' => $type->id, 'period' => '2026-10', 'amount' => 30000, 'paid_on' => '2026-10-05']);
        Expense::create(['description' => 'Lampu jalan', 'category' => 'perbaikan', 'amount' => 12500, 'spent_on' => '2026-10-07']);
        Expense::create(['description' => 'Konsumsi rapat', 'category' => 'kegiatan', 'amount' => 7500, 'spent_on' => '2026-10-31']);

        $this->actingAs($this->admin)->get(route('admin.reports.show', ['tahun' => 2026, 'bulan' => 10]))
            ->assertOk()
            ->assertSeeInOrder(['Saldo awal', 'Rp30.000', 'Total pemasukan', 'Rp30.000', 'Total pengeluaran', 'Rp20.000', 'Saldo akhir', 'Rp40.000'])
            ->assertSee('Perbaikan fasilitas')
            ->assertSee('Pak Suparman')
            ->assertSee('Bu Hendra');
    }
}
