<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseholdMemberRequest;
use App\Http\Requests\HouseholdMemberSyncRequest;
use App\Models\Household;
use App\Models\HouseholdMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HouseholdMemberController extends Controller
{
    public function index(Household $household): View
    {
        $household->load('members');

        return view('admin.households.members.index', compact('household'));
    }

    public function store(HouseholdMemberRequest $request, Household $household): RedirectResponse
    {
        $data = $request->validated();
        $member = $household->members()->create($data);

        return redirect()
            ->route('admin.households.members.index', $household)
            ->with('status', "Anggota keluarga {$member->name} berhasil ditambahkan.");
    }

    public function update(HouseholdMemberRequest $request, Household $household, HouseholdMember $member): RedirectResponse
    {
        abort_unless($member->household_id === $household->id, 404);

        $member->update($request->validated());

        return redirect()
            ->route('admin.households.members.index', $household)
            ->with('status', "Data {$member->name} berhasil diperbarui.");
    }

    public function destroy(Household $household, HouseholdMember $member): RedirectResponse
    {
        abort_unless($member->household_id === $household->id, 404);

        $name = $member->name;
        $member->delete();

        return redirect()
            ->route('admin.households.members.index', $household)
            ->with('status', "Anggota keluarga {$name} berhasil dihapus.");
    }

    /**
     * Bulk save / review save for household members & KK document.
     */
    public function sync(HouseholdMemberSyncRequest $request, Household $household): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $household) {
            if (array_key_exists('kk_number', $validated)) {
                $household->kk_number = $validated['kk_number'] ?: null;
            }

            if (! empty($validated['occupancy_status'])) {
                $household->occupancy_status = $validated['occupancy_status'];
            }

            if ($request->hasFile('kk_image')) {
                if ($household->kk_image_path) {
                    Storage::disk('public')->delete($household->kk_image_path);
                }
                $path = $request->file('kk_image')->store('kk', 'public');
                $household->kk_image_path = $path;
            }

            $household->save();

            $household->members()->delete();

            $headName = null;
            foreach ($validated['members'] as $memberData) {
                unset($memberData['id']);

                if (empty($memberData['name'])) {
                    continue;
                }

                $household->members()->create($memberData);

                if (($memberData['family_relation'] ?? '') === 'Kepala Keluarga' && ! $headName) {
                    $headName = $memberData['name'];
                }
            }

            if ($request->boolean('sync_head_name') && $headName) {
                $household->update(['head_name' => $headName]);
            }
        });

        $count = $household->members()->count();

        return redirect()
            ->route('admin.households.members.index', $household)
            ->with('status', "Berhasil menyimpan data KK dan {$count} anggota keluarga.");
    }
}
