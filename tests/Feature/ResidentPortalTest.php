<?php

namespace Tests\Feature;

use App\Models\DuesType;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Letter;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ResidentPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Date::setTestNow('2026-10-15 09:00:00');
        $this->admin = User::create(['name' => 'Ketua', 'email' => 'ketua@example.com', 'password' => 'rahasia123']);
    }

    public function test_arrears_count_unpaid_months_since_the_dues_started(): void
    {
        $house = Household::factory()->create(['number' => 'A-1']);
        Household::factory()->create(['number' => 'A-2', 'is_active' => false]);
        $type = DuesType::create(['name' => 'Kebersihan', 'amount' => 20000, 'frequency' => DuesType::MONTHLY, 'starts_on' => '2026-08-01', 'is_active' => true]);
        DuesType::create(['name' => '17-an', 'amount' => 50000, 'frequency' => DuesType::ONCE, 'is_active' => true]);
        Payment::create(['household_id' => $house->id, 'dues_type_id' => $type->id, 'period' => '2026-08', 'amount' => 20000, 'paid_on' => now()]);

        $this->actingAs($this->admin)->get(route('admin.arrears.index'))
            ->assertOk()
            ->assertSee('Rumah A-1')
            ->assertDontSee('Rumah A-2')
            ->assertSee('2 bulan: Sep 2026, Okt 2026')
            ->assertSee('Rp90.000');

        $this->get(route('admin.arrears.index', ['jenis' => $type->id]))->assertSee('Rp40.000')->assertDontSee('sekali bayar');
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Rumah menunggak');
    }

    public function test_portal_needs_the_household_token_and_shows_its_own_bills(): void
    {
        $house = Household::factory()->create(['number' => 'B-7']);
        DuesType::create(['name' => 'Keamanan', 'amount' => 30000, 'frequency' => DuesType::MONTHLY, 'starts_on' => '2026-10-01', 'is_active' => true]);

        $this->get('/warga/token-ngawur')->assertNotFound();

        $url = $house->portalUrl();
        $this->get($url)->assertOk()->assertSee('Rumah B-7')->assertSee('Keamanan')->assertSee('Rp30.000');

        $oldUrl = $url;
        $this->actingAs($this->admin)->post(route('admin.households.portal-reset', $house))->assertSessionHas('status');
        $this->get($oldUrl)->assertNotFound();
    }

    public function test_letter_request_approval_numbering_and_verification(): void
    {
        $house = Household::factory()->create();
        $member = HouseholdMember::factory()->create(['household_id' => $house->id, 'name' => 'Rina Kartika', 'nik' => '3201123456789012']);
        $stranger = HouseholdMember::factory()->create();
        $token = basename($house->portalUrl());

        $this->post(route('portal.letters.store', $token), ['household_member_id' => $stranger->id, 'type' => 'domisili', 'purpose' => 'Buka rekening'])
            ->assertSessionHasErrors('household_member_id', null, 'letter');

        $this->post(route('portal.letters.store', $token), ['household_member_id' => $member->id, 'type' => 'skck', 'purpose' => 'Melamar kerja'])
            ->assertRedirect();
        $letter = Letter::sole();
        $this->assertSame('Rina Kartika', $letter->person['name']);
        $this->get(route('portal.letters.show', [$token, $letter]))->assertNotFound();

        $this->actingAs($this->admin)->get(route('admin.letters.index'))->assertOk()->assertSee('Rina Kartika');
        $this->post(route('admin.letters.approve', $letter))->assertSessionHas('status');
        $this->post(route('admin.letters.approve', $letter))->assertSessionHas('warning');

        $letter->refresh();
        $this->assertSame('001/RT/X/2026', $letter->number);

        $second = Letter::factory()->create(['household_id' => $house->id]);
        $second->approve($this->admin);
        $this->assertSame('002/RT/X/2026', $second->number);

        $this->get(route('portal.letters.show', [$token, $letter]))->assertOk()->assertSee('Surat Pengantar SKCK')->assertSee('3201123456789012');
        $this->get(route('portal.letters.show', [$token, Letter::factory()->create()]))->assertNotFound();

        $this->get(route('letters.verify', strtolower($letter->code)))
            ->assertOk()->assertSee('001/RT/X/2026')->assertSee('3201••••••••9012')->assertDontSee('3201123456789012');
    }

    public function test_rejected_letter_is_not_verifiable(): void
    {
        $letter = Letter::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.letters.reject', $letter), [])->assertSessionHasErrors('reject_reason', null, "reject{$letter->id}");
        $this->post(route('admin.letters.reject', $letter), ['reject_reason' => 'Keperluan kurang jelas'])->assertSessionHas('status');

        $this->get(route('letters.verify', $letter->code))->assertNotFound();
    }

    public function test_inactive_household_portal_is_closed(): void
    {
        $house = Household::factory()->create();
        $url = $house->portalUrl();
        $house->update(['is_active' => false]);

        $this->get($url)->assertNotFound();
    }

    public function test_duplicate_pending_letter_is_refused_and_approval_uses_latest_kk_data(): void
    {
        $house = Household::factory()->create();
        $member = HouseholdMember::factory()->create(['household_id' => $house->id, 'nik' => null]);
        $token = basename($house->portalUrl());
        $payload = ['household_member_id' => $member->id, 'type' => 'domisili', 'purpose' => 'Buka rekening'];

        $this->post(route('portal.letters.store', $token), $payload)->assertSessionHas('letter_status');
        $this->post(route('portal.letters.store', $token), $payload)->assertSessionHas('letter_warning');
        $this->assertSame(1, Letter::count());

        $this->post(route('portal.letters.store', $token), ['type' => 'domisili'])
            ->assertRedirect(route('portal.show', $token).'#surat')
            ->assertSessionHasErrors(['household_member_id', 'purpose'], null, 'letter');

        $member->update(['nik' => '3201000011112222']);
        Letter::sole()->approve($this->admin);
        $this->assertSame('3201000011112222', Letter::sole()->person['nik']);
    }

    public function test_old_arrears_stay_payable_online(): void
    {
        $house = Household::factory()->create();
        $type = DuesType::create(['name' => 'Kas', 'amount' => 10000, 'frequency' => DuesType::MONTHLY, 'starts_on' => '2023-01-01', 'is_active' => true]);

        $this->get(route('pay.create', ['rumah' => $house->id, 'jenis' => $type->id, 'tahun' => 2023]))
            ->assertOk()
            ->assertViewHas('year', 2023)
            ->assertViewHas('years', range(2023, 2027));
        $this->get(route('pay.create', ['rumah' => $house->id, 'jenis' => $type->id, 'tahun' => 2019]))->assertViewHas('year', 2026);
    }
}
