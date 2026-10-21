<x-layouts.public :title="'Rumah '.$household->number">
    @php
        $letterErrors = $errors->getBag('letter');
        $pendingLetters = $letters->where('status', \App\Models\Letter::PENDING)->count();
        $tabs = [
            'tagihan' => 'Tagihan',
            'surat' => 'Surat',
            'riwayat' => 'Riwayat',
            'keluarga' => 'Keluarga',
        ];
    @endphp
    <div class="max-w-3xl mx-auto space-y-4" data-tabs>
        <div class="sheet p-5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Halaman rumah</p>
            <h1 class="font-bold text-2xl sm:text-3xl text-slate-900">Rumah {{ $household->number }} · {{ $household->head_name }}</h1>
            <div class="mt-3 flex flex-wrap gap-2">
                @if ($arrears)
                    <span class="badge badge-danger">Belum lunas · {{ rupiah($arrears['total']) }}</span>
                @else
                    <span class="badge badge-success">✓ Iuran lunas</span>
                @endif
                @if ($pendingLetters)
                    <span class="badge badge-warning">{{ $pendingLetters }} surat menunggu</span>
                @endif
                <span class="badge badge-neutral">{{ $household->members->count() }} anggota keluarga</span>
            </div>
            <p class="text-xs text-slate-500 mt-3">Link ini khusus untuk keluarga Anda. Mohon tidak dibagikan ke orang lain.</p>
        </div>

        {{-- Hidden until JS runs, so without JS every panel simply stays visible --}}
        <div class="sticky top-16 z-20 -mx-4 px-4 py-2 bg-slate-50/95 backdrop-blur-md" data-tablist-wrap hidden>
            <div role="tablist" aria-label="Bagian halaman rumah" class="grid grid-cols-4 gap-1 bg-slate-200/70 p-1 rounded-xl">
                @foreach ($tabs as $key => $label)
                    <button type="button" role="tab" id="tab-{{ $key }}" aria-controls="{{ $key }}" data-tab="{{ $key }}"
                            class="inline-flex items-center justify-center gap-1.5 min-h-10 px-1 rounded-lg text-sm font-semibold text-slate-600 transition-colors aria-selected:bg-white aria-selected:text-daun-dark aria-selected:shadow-xs">
                        {{ $label }}
                        @if ($key === 'tagihan' && $arrears)
                            <span class="size-2 rounded-full bg-terakota" aria-label="ada tunggakan"></span>
                        @elseif ($key === 'surat' && $pendingLetters)
                            <span class="min-w-5 h-5 px-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold leading-5">{{ $pendingLetters }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <section id="tagihan" role="tabpanel" aria-labelledby="tab-tagihan" class="sheet p-5 sm:p-6 scroll-mt-32" data-tab-panel>
            <h2 class="font-bold text-lg text-slate-900">Tagihan iuran</h2>
            @if ($arrears)
                <p class="text-sm text-slate-600 mt-1">Belum tercatat lunas sampai {{ now()->translatedFormat('F Y') }}:</p>
                <ul class="mt-3 divide-y divide-slate-100">
                    @foreach ($arrears['items'] as $item)
                        <li class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $item['type']->name }}</p>
                                <p class="text-sm text-slate-600">{{ \App\Support\Arrears::periodsLabel($item['periods']) }}</p>
                            </div>
                            <div class="flex items-center justify-between sm:justify-end gap-3">
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
                <p class="mt-3 text-sm rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-emerald-900 font-semibold">✓ Semua iuran sudah lunas sampai bulan ini. Terima kasih!</p>
            @endif

            @if ($submissions->isNotEmpty())
                <h3 class="mt-6 font-semibold text-sm text-slate-700">Bukti bayar yang dikirim</h3>
                <ul class="mt-2 divide-y divide-slate-100 text-sm">
                    @foreach ($submissions as $submission)
                        <li class="py-2.5 flex items-start justify-between gap-3">
                            <a href="{{ route('pay.show', $submission->code) }}" class="min-w-0 no-underline text-slate-900 hover:text-daun">
                                <span class="block font-semibold">{{ $submission->duesType->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $submission->periodsLabel() }} · dikirim {{ $submission->created_at->translatedFormat('j M Y') }}</span>
                            </a>
                            @include('public.pay.status-badge')
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div id="surat" role="tabpanel" aria-labelledby="tab-surat" class="space-y-4 scroll-mt-32" data-tab-panel>
            @if (session('letter_status'))
                <p role="status" class="text-sm rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-emerald-900 font-semibold">{{ session('letter_status') }}</p>
            @elseif (session('letter_warning'))
                <p role="status" class="text-sm rounded-xl bg-amber-50 border border-amber-200 p-3 text-amber-900 font-semibold">{{ session('letter_warning') }}</p>
            @endif

            @if ($letters->isNotEmpty())
                <section class="sheet p-5 sm:p-6" aria-labelledby="surat-saya">
                    <h2 id="surat-saya" class="font-bold text-lg text-slate-900">Surat saya</h2>
                    <ul class="mt-2 divide-y divide-slate-100">
                        @foreach ($letters as $letter)
                            <li class="py-3 text-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-slate-900">{{ $letter->typeTitle() }}</span>
                                        <span class="block text-slate-700">{{ $letter->person['name'] }}</span>
                                        <span class="block text-xs text-slate-500 mt-0.5">{{ $letter->purpose }} · diajukan {{ $letter->created_at->translatedFormat('j M Y') }}</span>
                                    </span>
                                    @if ($letter->isApproved())
                                        <span class="badge badge-success shrink-0">Disetujui</span>
                                    @elseif ($letter->isPending())
                                        <span class="badge badge-warning shrink-0">Menunggu</span>
                                    @else
                                        <span class="badge badge-danger shrink-0">Ditolak</span>
                                    @endif
                                </div>
                                @if ($letter->status === \App\Models\Letter::REJECTED && $letter->reject_reason)
                                    <p class="mt-2 text-xs rounded-lg bg-terakota-soft/60 px-3 py-2 text-slate-800">Alasan ditolak: {{ $letter->reject_reason }}</p>
                                @elseif ($letter->isApproved())
                                    <a href="{{ route('portal.letters.show', [$token, $letter]) }}" class="btn btn-sm btn-quiet mt-2 w-full sm:w-auto">Lihat &amp; cetak surat</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="sheet p-5 sm:p-6" aria-labelledby="surat-baru">
                <h2 id="surat-baru" class="font-bold text-lg text-slate-900">Ajukan surat pengantar</h2>
                <p class="text-sm text-slate-600 mt-1">Pengurus memeriksa pengajuan, lalu surat bernomor bisa dicetak sendiri dari halaman ini.</p>

                @if ($letterErrors->any())
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
                                   placeholder="Mis. melamar kerja, buka rekening, urus KTP" @if ($letterErrors->has('purpose')) aria-invalid="true" @endif>
                            @if ($letterErrors->has('purpose')) <span class="field-error">{{ $letterErrors->first('purpose') }}</span> @endif
                        </div>
                        <div class="sm:col-span-2">
                            <label for="phone" class="field-label">Nomor WA <span class="font-normal text-slate-500">(untuk dikabari)</span></label>
                            <input id="phone" name="phone" type="tel" maxlength="30" class="input" value="{{ old('phone', $household->phone) }}" inputmode="tel">
                        </div>
                        <div class="sm:col-span-2 pt-1">
                            <button class="btn btn-primary w-full" data-busy="Mengirim...">Ajukan surat</button>
                        </div>
                    </form>
                @endif
            </section>
        </div>

        <section id="riwayat" role="tabpanel" aria-labelledby="tab-riwayat" class="sheet p-5 sm:p-6 scroll-mt-32" data-tab-panel>
            <h2 class="font-bold text-lg text-slate-900">Riwayat pembayaran</h2>
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
                            <span class="shrink-0 flex flex-col sm:flex-row items-end sm:items-center gap-1 sm:gap-3">
                                <span class="tabular-nums font-semibold">{{ rupiah($payment->amount) }}</span>
                                <a href="{{ route('receipt.show', $payment) }}" class="text-xs font-semibold text-daun hover:underline">Kuitansi</a>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section id="keluarga" role="tabpanel" aria-labelledby="tab-keluarga" class="sheet p-5 sm:p-6 scroll-mt-32" data-tab-panel>
            <h2 class="font-bold text-lg text-slate-900">Data keluarga</h2>
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
