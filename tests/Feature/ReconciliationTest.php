<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\DuesType;
use App\Models\Household;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Models\User;
use App\Support\BankStatementMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private DuesType $type;

    private BankAccount $account;

    protected function setUp(): void
    {
        parent::setUp();
        Date::setTestNow('2026-10-20 09:00:00');
        $this->admin = User::create(['name' => 'Bendahara', 'email' => 'b@example.com', 'password' => 'rahasia123']);
        $this->type = DuesType::create(['name' => 'Keamanan', 'amount' => 50000, 'frequency' => DuesType::MONTHLY, 'is_active' => true]);
        $this->account = BankAccount::create(['bank_name' => 'BCA', 'account_number' => '123']);
    }

    private function submission(Household $house, array $periods, int $uniqueCode): PaymentSubmission
    {
        return PaymentSubmission::create([
            'code' => PaymentSubmission::newCode(), 'household_id' => $house->id, 'dues_type_id' => $this->type->id, 'periods' => $periods,
            'unit_amount' => 50000, 'unique_code' => $uniqueCode, 'bank_account_id' => $this->account->id, 'proof_path' => 'bukti-bayar/x.jpg',
        ])->refresh();
    }

    public function test_parser_reads_common_bank_formats_and_skips_debits(): void
    {
        $rows = (new BankStatementMatcher)->parse(implode("\n", [
            '15/10 TRSF E-BANKING CR 1510/FTSCY/WS95051 50,007.00 CR',
            '2026-10-16;Transfer masuk BUDI;"75.012,00"',
            '17/10 TARIKAN ATM DB 500,000.00',
            'saldo awal',
        ]));

        $this->assertCount(2, $rows);
        $this->assertSame('2026-10-15', $rows[0]['date']);
        $this->assertContains(50007, $rows[0]['amounts']);
        $this->assertSame('2026-10-16', $rows[1]['date']);
        $this->assertContains(75012, $rows[1]['amounts']);
    }

    public function test_matched_submissions_can_be_confirmed_in_bulk(): void
    {
        $a = Household::factory()->create(['number' => 'A-1']);
        $b = Household::factory()->create(['number' => 'A-2']);
        $c = Household::factory()->create(['number' => 'A-3']);
        $matched = $this->submission($a, ['2026-09', '2026-10'], 7);
        $missing = $this->submission($b, ['2026-10'], 12);
        $twinOne = $this->submission($c, ['2026-10'], 0);
        $twinTwo = $this->submission($b, ['2026-09'], 0);

        $statement = "15/10 TRSF CR 100,007.00\n16/10 TRSF CR 50,000.00\n18/10 TRSF CR 25,033.00";

        $response = $this->actingAs($this->admin)->post(route('admin.reconciliation.check'), ['statement' => $statement])->assertOk();
        $result = $response->viewData('result');
        $status = $result['matches']->mapWithKeys(fn ($m) => [$m['submission']->id => $m['status']]);

        $this->assertSame('matched', $status[$matched->id]);
        $this->assertSame('missing', $status[$missing->id]);
        $this->assertSame('ambiguous', $status[$twinOne->id]);
        $this->assertSame('ambiguous', $status[$twinTwo->id]);
        $this->assertCount(1, $result['unmatched']);

        $this->post(route('admin.reconciliation.approve'), ['approve' => [$matched->id], 'paid_on' => [$matched->id => '2026-10-15']])
            ->assertRedirect(route('admin.reconciliation.create'))
            ->assertSessionHas('status');

        $this->assertSame(PaymentSubmission::APPROVED, $matched->fresh()->status);
        $this->assertSame(['2026-09', '2026-10'], Payment::where('household_id', $a->id)->orderBy('period')->pluck('period')->all());
        $this->assertSame('2026-10-15', Payment::where('household_id', $a->id)->first()->paid_on->toDateString());
        $this->assertSame(PaymentSubmission::PENDING, $missing->fresh()->status);
    }
}
