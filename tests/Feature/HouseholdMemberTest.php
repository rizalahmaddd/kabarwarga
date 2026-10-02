<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HouseholdMemberTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Household $household;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['name' => 'Pengurus', 'email' => 'admin@example.com', 'password' => 'password']);
        $this->household = Household::create(['number' => 'B-05', 'head_name' => 'Pak Joko', 'phone' => '08123456789']);
    }

    public function test_admin_can_view_household_members_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.households.members.index', $this->household));

        $response->assertOk();
        $response->assertSee('Data Penghuni');
        $response->assertSee('B-05');
    }

    public function test_admin_can_add_household_member_manually(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.households.members.store', $this->household), [
            'nik' => '3201123456780001',
            'name' => 'Siti Aminah',
            'gender' => 'P',
            'family_relation' => 'Istri',
            'occupancy_status' => 'kontrak',
            'birth_place' => 'Bandung',
            'birth_date' => '1990-05-12',
            'religion' => 'Islam',
            'marital_status' => 'Kawin',
            'job' => 'Guru',
            'phone' => '08198765432',
        ]);

        $response->assertRedirect(route('admin.households.members.index', $this->household));
        $this->assertDatabaseHas('household_members', [
            'household_id' => $this->household->id,
            'nik' => '3201123456780001',
            'name' => 'Siti Aminah',
            'gender' => 'P',
            'family_relation' => 'Istri',
            'occupancy_status' => 'kontrak',
        ]);
    }

    public function test_admin_can_update_household_member(): void
    {
        $this->actingAs($this->admin);

        $member = HouseholdMember::factory()->create([
            'household_id' => $this->household->id,
            'name' => 'Nama Lama',
        ]);

        $response = $this->put(route('admin.households.members.update', [$this->household, $member]), [
            'name' => 'Nama Baru',
            'gender' => 'L',
            'family_relation' => 'Anak',
        ]);

        $response->assertRedirect(route('admin.households.members.index', $this->household));
        $this->assertDatabaseHas('household_members', [
            'id' => $member->id,
            'name' => 'Nama Baru',
            'family_relation' => 'Anak',
        ]);
    }

    public function test_admin_can_delete_household_member(): void
    {
        $this->actingAs($this->admin);

        $member = HouseholdMember::factory()->create([
            'household_id' => $this->household->id,
            'name' => 'Anggota Hapus',
        ]);

        $response = $this->delete(route('admin.households.members.destroy', [$this->household, $member]));

        $response->assertRedirect(route('admin.households.members.index', $this->household));
        $this->assertDatabaseMissing('household_members', [
            'id' => $member->id,
        ]);
    }

    public function test_admin_can_sync_members_and_kk_from_review(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $file = UploadedFile::fake()->image('kartu_keluarga.jpg');

        $response = $this->post(route('admin.households.members.sync', $this->household), [
            'kk_number' => '3201999988887777',
            'occupancy_status' => 'kontrak',
            'sync_head_name' => '1',
            'kk_image' => $file,
            'members' => [
                [
                    'nik' => '3201111111110001',
                    'name' => 'Bambang Sutrisno',
                    'gender' => 'L',
                    'family_relation' => 'Kepala Keluarga',
                    'occupancy_status' => 'kontrak',
                    'birth_date' => '1985-01-01',
                    'religion' => 'Islam',
                    'marital_status' => 'Kawin',
                    'job' => 'Wiraswasta',
                ],
                [
                    'nik' => '3201111111110002',
                    'name' => 'Ratna Dewi',
                    'gender' => 'P',
                    'family_relation' => 'Istri',
                    'occupancy_status' => 'kontrak',
                    'birth_date' => '1988-03-15',
                    'religion' => 'Islam',
                    'marital_status' => 'Kawin',
                    'job' => 'Ibu Rumah Tangga',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.households.members.index', $this->household));

        $this->household->refresh();
        $this->assertSame('3201999988887777', $this->household->kk_number);
        $this->assertSame('kontrak', $this->household->occupancy_status);
        $this->assertSame('Bambang Sutrisno', $this->household->head_name);
        $this->assertNotNull($this->household->kk_image_path);
        Storage::disk('public')->assertExists($this->household->kk_image_path);

        $this->assertSame(2, $this->household->members()->count());
        $this->assertDatabaseHas('household_members', [
            'household_id' => $this->household->id,
            'nik' => '3201111111110001',
            'name' => 'Bambang Sutrisno',
        ]);
        $this->assertDatabaseHas('household_members', [
            'household_id' => $this->household->id,
            'nik' => '3201111111110002',
            'name' => 'Ratna Dewi',
        ]);
    }
}
