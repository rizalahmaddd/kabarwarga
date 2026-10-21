<x-layouts.public :title="'Rumah '.$household->number">
    @php $letterErrors = $errors->getBag('letter'); @endphp
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Halaman rumah</p>
            <h1 class="font-bold text-2xl sm:text-3xl text-slate-900">Rumah {{ $household->number }} · {{ $household->head_name }}</h1>
            <p class="text-sm text-slate-600 mt-1">Link ini khusus untuk keluarga Anda. Mohon tidak dibagikan ke orang lain.</p>
        </div>

        <section class="sheet p-5 sm:p-6" aria-labelledby="tagihan">
            <h2 id="tagihan" class="font-bold text-lg text-slate-900">Tagihan iuran</h2>
            @if ($arrears)
                <p class="text-sm text-slate-600 mt-1">Belum tercatat lunas sampai {{ now()->translatedFormat('F Y') }}:</p>
                <ul class="mt-3 divide-y divide-slate-100">
                    @foreach ($arrears['items'] as $item)
                        <li class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $item['type']->name }}</p>
                                <p class="text-sm text-slate-600">{{ \App\Support\Arrears::periodsLabel($item['periods']) }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold tabular-nums">{{ rupiah($item['amount']) }}</span>
                                <a href="{{ route('pay.create', ['rumah' => $household->id, 'jenis' => $item['type']->id, 'tahun' => $item['periods'][0] !== '' ? substr($item['periods'][0], 0, 4) : null]) }}" class="btn btn-sm btn-primary text-xs">Bayar online</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 pt-3 border-t border-slate-200 flex justify-between font-bold">
                    <span>Total</span><span class="tabular-nums text-terakota">{{ rupiah($arrears['total']) }}</span>
                </p>
            @else
                <p class="mt-2 text-sm text-daun-dark font-semibold">✓ Semua iuran sudah lunas sampai bulan ini. Terima kasih!</p>
            @endif

            @if ($submissions->isNotEmpty())
                <h3 class="mt-5 font-semibold text-sm text-slate-700">Bukti bayar yang dikirim</h3>
                <ul class="mt-2 space-y-1.5 text-sm">
                    @foreach ($submissions as $submission)
                        <li class="flex items-center justify-between gap-3">
                            <a href="{{ route('pay.show', $submission->code) }}" class="text-slate-800 hover:text-daun">
                                {{ $submission->duesType->name }} · {{ $submission->periodsLabel() }}
                            </a>
                            @include('public.pay.status-badge')
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="sheet p-5 sm:p-6" aria-labelledby="riwayat">
            <h2 id="riwayat" class="font-bold text-lg text-slate-900">Riwayat pembayaran</h2>
            @if ($payments->isEmpty())
                <p class="mt-2 text-sm text-slate-500">Belum ada pembayaran tercatat.</p>
            @else
                <ul class="mt-3 divide-y divide-slate-100">
                    @foreach ($payments as $payment)
                        <li class="py-2.5 flex items-center justify-between gap-3 text-sm">
                            <span class="min-w-0">
                                <span class="block font-semibold text-slate-900">{{ $payment->duesType->name }} · {{ $payment->periodLabel() }}</span>
                                <span class="block text-xs text-slate-500">Dibayar {{ $payment->paid_on->translatedFormat('j M Y') }} · {{ \App\Models\Payment::METHODS[$payment->method] ?? $payment->method }}</span>
                            </span>
                            <span class="shrink-0 flex items-center gap-3">
                                <span class="tabular-nums font-semibold">{{ rupiah($payment->amount) }}</span>
                                <a href="{{ route('receipt.show', $payment) }}" class="text-xs font-semibold text-daun hover:underline">Kuitansi</a>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section id="surat" class="sheet p-5 sm:p-6 scroll-mt-20" aria-labelledby="surat-judul">
            <h2 id="surat-judul" class="font-bold text-lg text-slate-900">Surat pengantar RT</h2>
            <p class="text-sm text-slate-600 mt-1">Ajukan dari sini, pengurus memeriksa, lalu surat bernomor bisa dicetak sendiri.</p>

            @if (session('letter_status'))
                <p role="status" class="mt-3 text-sm rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-emerald-900 font-semibold">{{ session('letter_status') }}</p>
            @elseif (session('letter_warning'))
                <p role="status" class="mt-3 text-sm rounded-xl bg-amber-50 border border-amber-200 p-3 text-amber-900 font-semibold">{{ session('letter_warning') }}</p>
            @elseif ($letterErrors->any())
                <p role="alert" class="mt-3 text-sm rounded-xl bg-terakota-soft/60 border border-terakota/30 p-3 text-slate-900">Periksa lagi isian di bawah.</p>
            @endif

            @if ($household->members->isEmpty())
                <p class="mt-3 text-sm rounded-xl bg-amber-50 border border-amber-200 p-3 text-amber-900">
                    Data anggota keluarga rumah ini belum didata. Hubungi pengurus untuk mendata Kartu Keluarga terlebih dulu.
                </p>
            @else
                <form method="POST" action="{{ route('portal.letters.store', $token) }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label for="household_member_id" class="field-label">Untuk siapa</label>
                        <select id="household_member_id" name="household_member_id" class="input" required @if ($letterErrors->has('household_member_id')) aria-invalid="true" @endif>
                            <option value="">Pilih anggota keluarga</option>
                            @foreach ($household->members as $member)
                                <option value="{{ $member->id }}" @selected((int) old('household_member_id') === $member->id)>{{ $member->name }}{{ $member->family_relation ? " ({$member->family_relation})" : '' }}</option>
                            @endforeach
                        </select>
                        @if ($letterErrors->has('household_member_id')) <span class="field-error">{{ $letterErrors->first('household_member_id') }}</span> @endif
                    </div>
                    <div>
                        <label for="type" class="field-label">Jenis surat</label>
                        <select id="type" name="type" class="input" required>
                            @foreach ($letterTypes as $key => $type)
                                <option value="{{ $key }}" @selected(old('type') === $key)>{{ $type['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="purpose" class="field-label">Keperluan</label>
                        <input id="purpose" name="purpose" type="text" maxlength="255" required class="input" value="{{ old('purpose') }}"
                               placeholder="Mis. melamar pekerjaan, membuka rekening bank, mengurus KTP" @if ($letterErrors->has('purpose')) aria-invalid="true" @endif>
                        @if ($letterErrors->has('purpose')) <span class="field-error">{{ $letterErrors->first('purpose') }}</span> @endif
                    </div>
                    <div>
                        <label for="phone" class="field-label">Nomor WA <span class="font-normal text-slate-500">(untuk dikabari)</span></label>
                        <input id="phone" name="phone" type="tel" maxlength="30" class="input" value="{{ old('phone', $household->phone) }}" inputmode="tel">
                    </div>
                    <div class="sm:self-end">
                        <button class="btn btn-primary w-full" data-busy="Mengirim...">Ajukan surat</button>
                    </div>
                </form>
            @endif

            @if ($letters->isNotEmpty())
                <ul class="mt-5 divide-y divide-slate-100 border-t border-slate-100">
                    @foreach ($letters as $letter)
                        <li class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm">
                            <span class="min-w-0">
                                <span class="block font-semibold text-slate-900">{{ $letter->typeTitle() }} · {{ $letter->person['name'] }}</span>
                                <span class="block text-xs text-slate-500">{{ $letter->purpose }} · diajukan {{ $letter->created_at->translatedFormat('j M Y') }}</span>
                                @if ($letter->status === \App\Models\Letter::REJECTED && $letter->reject_reason)
                                    <span class="block text-xs text-terakota mt-0.5">Alasan ditolak: {{ $letter->reject_reason }}</span>
                                @endif
                            </span>
                            <span class="shrink-0 flex items-center gap-2">
                                @if ($letter->isApproved())
                                    <span class="badge badge-success">Disetujui</span>
                                    <a href="{{ route('portal.letters.show', [$token, $letter]) }}" class="btn btn-sm btn-quiet text-xs">Cetak</a>
                                @elseif ($letter->isPending())
                                    <span class="badge badge-warning">Menunggu</span>
                                @else
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="sheet p-5 sm:p-6" aria-labelledby="keluarga">
            <h2 id="keluarga" class="font-bold text-lg text-slate-900">Data keluarga</h2>
            @if ($household->members->isEmpty())
                <p class="mt-2 text-sm text-slate-500">Belum didata.</p>
            @else
                <ul class="mt-3 divide-y divide-slate-100 text-sm">
                    @foreach ($household->members as $member)
                        <li class="py-2.5 flex items-center justify-between gap-3">
                            <span>
                                <span class="block font-semibold text-slate-900">{{ $member->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $member->family_relation ?: 'Anggota keluarga' }}</span>
                            </span>
                            <span class="font-mono text-xs text-slate-600">{{ $member->nik ? substr($member->nik, 0, 4).str_repeat('•', max(strlen($member->nik) - 8, 0)).substr($member->nik, -4) : 'NIK belum ada' }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-slate-500">Ada data yang keliru? Kabari pengurus supaya diperbaiki sebelum mengajukan surat.</p>
            @endif
        </section>
    </div>
</x-layouts.public>
