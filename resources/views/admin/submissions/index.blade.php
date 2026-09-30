<x-layouts.admin title="Konfirmasi Bayar">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Bayar Online Warga
            </div>
            <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Konfirmasi Pembayaran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Cocokkan bukti dengan mutasi rekening. Setelah diterima, iuran otomatis tercatat lunas.</p>
        </div>
        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-sm btn-quiet text-xs font-bold self-start sm:self-auto">Atur Rekening</a>
    </div>

    <nav class="inline-flex p-1 rounded-xl bg-slate-200/70 border border-slate-200 mb-5" aria-label="Status pengajuan">
        @foreach (\App\Models\PaymentSubmission::STATUSES as $key => $label)
            <a href="{{ route('admin.submissions.index', ['status' => $key]) }}"
               @if ($status === $key) aria-current="page" @endif
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold no-underline {{ $status === $key ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600' }}">
                {{ $key === \App\Models\PaymentSubmission::PENDING ? 'Menunggu' : $label }}
                @if ($counts[$key] ?? 0)
                    <span class="px-1.5 rounded-full text-[11px] {{ $key === \App\Models\PaymentSubmission::PENDING ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $counts[$key] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    @if ($submissions->isEmpty())
        <div class="sheet p-8 text-center">
            <h3 class="font-bold text-slate-800">
                {{ $status === \App\Models\PaymentSubmission::PENDING ? 'Tidak ada pembayaran yang perlu dicek' : 'Belum ada data' }}
            </h3>
            @if ($status === \App\Models\PaymentSubmission::PENDING)
                <p class="text-xs text-slate-500 mt-1">Warga bisa mengirim bukti lewat halaman <a href="{{ route('pay.create') }}" target="_blank">Bayar Iuran</a>.</p>
            @endif
        </div>
    @else
        <div class="space-y-4">
            @foreach ($submissions as $submission)
                @php
                    $proofExt = strtolower(pathinfo($submission->proof_path, PATHINFO_EXTENSION));
                    $isPreviewable = in_array($proofExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                    $proofUrl = route('admin.submissions.proof', $submission);
                    $alreadyPaid = $submission->isPending()
                        ? \App\Models\Payment::where('household_id', $submission->household_id)->where('dues_type_id', $submission->dues_type_id)->whereIn('period', $submission->periods)->get()
                        : collect();
                    $alreadyPaidPeriods = $alreadyPaid->pluck('period')->all();
                    $rejectErrors = $errors->getBag("reject{$submission->id}");
                    $approveErrors = $errors->getBag("approve{$submission->id}");
                    $isMonthlySubmission = $submission->periods !== [''];
                @endphp
                <article class="sheet p-4 sm:p-5 grid grid-cols-1 gap-4 md:grid-cols-[12rem_minmax(0,1fr)]">
                    <a href="{{ $proofUrl }}" target="_blank" rel="noopener" class="block rounded-xl border border-slate-200 bg-slate-50 overflow-hidden no-underline">
                        @unless ($isPreviewable)
                            <span class="flex flex-col items-center justify-center h-40 text-slate-600 text-sm font-bold uppercase">{{ $proofExt ?: 'file' }}<span class="text-xs font-semibold normal-case text-daun mt-1">Buka bukti</span></span>
                        @else
                            <img src="{{ $proofUrl }}" alt="Bukti transfer #{{ $submission->code }}" class="w-full h-48 md:h-56 object-cover object-top" loading="lazy">
                        @endunless
                    </a>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-sm px-2.5 py-0.5 rounded-lg bg-slate-900 text-white">{{ $submission->household->number }}</span>
                                    <span class="font-bold text-slate-800">{{ $submission->household->head_name }}</span>
                                </div>
                                <p class="text-sm text-slate-700 mt-1.5"><strong>{{ $submission->duesType->name }}</strong> · {{ $submission->periodsLabel() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-extrabold tabular-nums text-slate-900">{{ rupiah($submission->total()) }}</p>
                                <p class="text-xs text-slate-500">
                                    @if (count($submission->periods) > 1)
                                        {{ count($submission->periods) }} × {{ rupiah($submission->unit_amount) }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <dl class="mt-3 grid gap-x-4 gap-y-1 text-xs text-slate-600 sm:grid-cols-2">
                            <div><dt class="inline text-slate-400">Ke rekening:</dt> <dd class="inline font-semibold">{{ $submission->bankAccount?->label() ?? 'rekening sudah dihapus' }}</dd></div>
                            <div><dt class="inline text-slate-400">Dikirim:</dt> <dd class="inline font-semibold">{{ $submission->created_at->translatedFormat('j M Y, H:i') }} ({{ $submission->created_at->diffForHumans() }})</dd></div>
                            @if ($submission->payer_name)
                                <div><dt class="inline text-slate-400">Pengirim:</dt> <dd class="inline font-semibold">{{ $submission->payer_name }}</dd></div>
                            @endif
                            @if ($submission->phone)
                                @php
                                    $waPhone = preg_replace('/[^0-9]/', '', $submission->phone);
                                    if (str_starts_with($waPhone, '0')) {
                                        $waPhone = '62'.substr($waPhone, 1);
                                    }
                                @endphp
                                <div><dt class="inline text-slate-400">WA:</dt> <dd class="inline"><a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener noreferrer" class="font-semibold">{{ $submission->phone }}</a></dd></div>
                            @endif
                            <div><dt class="inline text-slate-400">Kode:</dt> <dd class="inline font-mono font-semibold">#{{ $submission->code }}</dd></div>
                        </dl>
                        @if ($submission->note)
                            <p class="mt-2 text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">"{{ $submission->note }}"</p>
                        @endif

                        @if ($alreadyPaid->isNotEmpty())
                            <p class="mt-3 text-xs font-semibold text-amber-900 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                Sudah tercatat lunas sebelumnya: {{ $alreadyPaid->map->periodLabel()->implode(', ') }}. Bulan itu tidak akan dicatat dobel; cek apakah warga membayar dua kali.
                            </p>
                        @endif

                        @if ($submission->isPending())
                            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col lg:flex-row gap-3">
                                <form method="POST" action="{{ route('admin.submissions.approve', $submission) }}" class="flex-1 space-y-3"
                                      data-partial-approve data-unit-amount="{{ $submission->unit_amount }}"
                                      data-confirm="Terima pembayaran rumah {{ $submission->household->number }} untuk bulan yang dicentang?">
                                    @csrf
                                    @if ($isMonthlySubmission)
                                        <fieldset>
                                            <legend class="block text-[11px] font-semibold text-slate-500 mb-1">Bulan yang diterima <span class="font-normal">(hapus centang kalau uangnya tidak cukup/tidak masuk)</span></legend>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach (collect($submission->periods)->sort() as $period)
                                                    @php $isAlreadyPaid = in_array($period, $alreadyPaidPeriods, true); @endphp
                                                    <label class="inline-flex items-center gap-2 px-3 min-h-10 rounded-xl border border-slate-200 bg-white text-sm font-semibold cursor-pointer has-[:checked]:border-daun has-[:checked]:bg-emerald-50">
                                                        <input type="checkbox" name="periods[]" value="{{ $period }}" class="size-4 accent-daun"
                                                               @checked($approveErrors->any() ? in_array($period, old('periods', []), true) : ! $isAlreadyPaid)>
                                                        {{ $submission->periodsLabel([$period]) }}
                                                        @if ($isAlreadyPaid)
                                                            <span class="text-[10px] font-bold text-amber-800">sudah lunas</span>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                            @if ($approveErrors->has('periods'))
                                                <span class="field-error">{{ $approveErrors->first('periods') }}</span>
                                            @endif
                                        </fieldset>

                                        <div data-partial-reason>
                                            <label for="partial_reason_{{ $submission->id }}" class="block text-[11px] font-semibold text-slate-500 mb-1">Alasan bulan yang tidak diterima (dilihat warga)</label>
                                            <input id="partial_reason_{{ $submission->id }}" name="reject_reason" type="text" maxlength="255" class="input min-h-10 py-1.5 text-sm" list="alasan-sebagian"
                                                   placeholder="Mis. transfer hanya cukup untuk 2 bulan"
                                                   value="{{ $approveErrors->any() ? old('reject_reason') : ($alreadyPaid->isNotEmpty() ? 'Sudah tercatat lunas sebelumnya: '.$alreadyPaid->map->periodLabel()->implode(', ') : '') }}">
                                            @if ($approveErrors->has('reject_reason'))
                                                <span class="field-error">{{ $approveErrors->first('reject_reason') }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="flex flex-wrap items-end gap-2">
                                        <div>
                                            <label for="paid_on_{{ $submission->id }}" class="block text-[11px] font-semibold text-slate-500 mb-1">Tanggal uang masuk</label>
                                            <input id="paid_on_{{ $submission->id }}" name="paid_on" type="date" class="input min-h-10 py-1.5 text-sm w-40" required
                                                   max="{{ now()->toDateString() }}" value="{{ $approveErrors->any() ? old('paid_on') : $submission->created_at->toDateString() }}">
                                        </div>
                                        <button class="btn btn-primary min-h-10 text-sm" data-busy="Menyimpan...">
                                            ✓ Terima <span data-partial-total>{{ rupiah($submission->unit_amount * max(1, count(array_diff($submission->periods, $alreadyPaidPeriods)))) }}</span>
                                        </button>
                                    </div>
                                    @if ($approveErrors->has('paid_on'))
                                        <span class="field-error">{{ $approveErrors->first('paid_on') }}</span>
                                    @endif
                                </form>

                                <details class="lg:w-72" @if ($rejectErrors->any()) open @endif>
                                    <summary class="btn btn-danger min-h-10 text-sm w-full list-none">Tolak semua</summary>
                                    <form method="POST" action="{{ route('admin.submissions.reject', $submission) }}" class="mt-2 space-y-2">
                                        @csrf
                                        <label for="reject_reason_{{ $submission->id }}" class="block text-[11px] font-semibold text-slate-500">Alasan (dilihat warga)</label>
                                        <input id="reject_reason_{{ $submission->id }}" name="reject_reason" type="text" maxlength="255" class="input min-h-10 py-1.5 text-sm" required
                                               list="alasan-tolak" placeholder="Mis. dana belum masuk rekening" value="{{ $rejectErrors->any() ? old('reject_reason') : '' }}">
                                        @if ($rejectErrors->has('reject_reason'))
                                            <span class="field-error">{{ $rejectErrors->first('reject_reason') }}</span>
                                        @endif
                                        <button class="btn btn-danger min-h-10 text-sm w-full" data-busy="Menyimpan...">Tolak pengajuan</button>
                                    </form>
                                </details>
                            </div>
                        @else
                            <div class="mt-4 pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-500">
                                @if ($submission->isPartial())
                                    <p class="font-semibold text-amber-900 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                        Diterima sebagian: {{ $submission->periodsLabel($submission->acceptedPeriods()) }} ({{ rupiah($submission->acceptedTotal()) }}).
                                        Tidak diterima: {{ $submission->periodsLabel($submission->declinedPeriods()) }}.
                                    </p>
                                @endif
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span>
                                        {{ $submission->status === \App\Models\PaymentSubmission::APPROVED ? 'Diterima' : 'Ditolak' }}
                                        oleh <strong class="text-slate-700">{{ $submission->reviewer?->name ?? '-' }}</strong>
                                        · {{ $submission->reviewed_at?->translatedFormat('j M Y, H:i') }}
                                        @if ($submission->reject_reason)
                                            · Alasan: <strong class="text-slate-700">{{ $submission->reject_reason }}</strong>
                                        @endif
                                    </span>
                                    @if ($submission->status === \App\Models\PaymentSubmission::REJECTED)
                                        <form method="POST" action="{{ route('admin.submissions.reopen', $submission) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-quiet text-xs">Kembalikan ke menunggu</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.submissions.cancel', $submission) }}"
                                              data-confirm="Batalkan konfirmasi #{{ $submission->code }}? Catatan lunas dari kiriman ini akan dihapus dan pengajuan kembali ke daftar menunggu.">
                                            @csrf
                                            <button class="btn btn-sm btn-danger text-xs">Batalkan konfirmasi</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <datalist id="alasan-sebagian">
            <option value="Transfer hanya cukup untuk sebagian bulan">
            <option value="Jumlah transfer kurang dari tagihan">
            <option value="Sudah tercatat lunas sebelumnya">
        </datalist>

        <datalist id="alasan-tolak">
            <option value="Dana belum masuk ke rekening">
            <option value="Jumlah transfer tidak sesuai">
            <option value="Foto bukti tidak jelas, mohon kirim ulang">
            <option value="Bukti transfer bukan untuk iuran ini">
        </datalist>

        <div class="mt-6">{{ $submissions->links() }}</div>
    @endif
</x-layouts.admin>
