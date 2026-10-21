<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Letter;
use App\Models\PaymentSubmission;
use App\Support\Arrears;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PortalController extends Controller
{
    public function show(string $token, Arrears $arrears)
    {
        $household = $this->household($token);

        return view('public.portal.show', [
            'token' => $token,
            'household' => $household->load('members'),
            'arrears' => $arrears->all($household)->first(),
            'payments' => $household->payments()->with('duesType')->latest('paid_on')->latest('id')->take(12)->get(),
            'submissions' => PaymentSubmission::with('duesType')->where('household_id', $household->id)->latest()->take(5)->get(),
            'letters' => Letter::where('household_id', $household->id)->latest()->take(10)->get(),
            'letterTypes' => Letter::TYPES,
        ]);
    }

    public function storeLetter(Request $request, string $token)
    {
        $household = $this->household($token);

        $validator = Validator::make($request->all(), [
            'household_member_id' => ['required', Rule::exists('household_members', 'id')->where('household_id', $household->id)],
            'type' => ['required', Rule::in(array_keys(Letter::TYPES))],
            'purpose' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ], [
            'household_member_id.required' => 'Pilih anggota keluarga yang dibuatkan surat.',
            'purpose.required' => 'Tulis keperluan surat, mis. melamar kerja atau membuka rekening.',
        ]);

        if ($validator->fails()) {
            return redirect()->to(route('portal.show', $token).'#surat')->withErrors($validator, 'letter')->withInput();
        }
        $data = $validator->validated();

        $member = $household->members()->findOrFail($data['household_member_id']);

        $alreadyWaiting = Letter::pending()
            ->where('household_member_id', $member->id)
            ->where('type', $data['type'])
            ->exists();
        if ($alreadyWaiting) {
            return redirect()->route('portal.show', $token)->withFragment('surat')
                ->with('letter_warning', 'Surat yang sama untuk '.$member->name.' masih menunggu diperiksa pengurus. Tidak perlu diajukan lagi.');
        }

        Letter::create([
            'code' => Letter::newCode(),
            'household_id' => $household->id,
            'household_member_id' => $member->id,
            'type' => $data['type'],
            'purpose' => $data['purpose'],
            'person' => Letter::personFrom($member),
            'phone' => $data['phone'] ?? $household->phone,
        ]);

        return redirect()->route('portal.show', $token)->withFragment('surat')
            ->with('letter_status', "Pengajuan surat untuk {$member->name} terkirim. Pengurus akan memeriksanya, cek lagi halaman ini nanti.");
    }

    public function letter(string $token, Letter $letter)
    {
        $household = $this->household($token);
        abort_unless($letter->household_id === $household->id && $letter->isApproved(), 404);

        return view('letters.print', ['letter' => $letter->load('household')]);
    }

    private function household(string $token): Household
    {
        // Moved-out families keep their old link, so an inactive house must stop opening.
        return Household::active()->where('access_token', $token)->firstOrFail();
    }
}
