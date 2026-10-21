<x-layouts.admin title="Surat Pengantar">
    <div class="mb-5">
        <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Surat Pengantar</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Warga mengajukan dari halaman rumahnya masing-masing. Setelah disetujui, nomor surat dibuat otomatis dan surat siap dicetak dengan QR cek keaslian.
        </p>
        @unless (setting('chairman_name'))
            <p class="mt-2 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 inline-block">
                Nama Ketua RT belum diisi. Lengkapi di <a href="{{ route('admin.settings.edit') }}" class="font-semibold underline">Pengaturan</a> supaya tampil di tanda tangan surat.
            </p>
        @endunless
    </div>

    <nav class="inline-flex p-1 rounded-xl bg-slate-200/70 border border-slate-200 mb-5" aria-label="Status pengajuan surat">
        @foreach (\App\Models\Letter::STATUSES as $key => $label)
            <a href="{{ route('admin.letters.index', ['status' => $key]) }}"
               @if ($status === $key) aria-current="page" @endif
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold no-underline {{ $status === $key ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600' }}">
                {{ $label }}
                @if ($counts[$key] ?? 0)
                    <span class="px-1.5 rounded-full text-[11px] {{ $key === \App\Models\Letter::PENDING ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $counts[$key] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    @if ($letters->isEmpty())
        <div class="sheet p-8 text-center">
            <h3 class="font-bold text-slate-800">{{ $status === \App\Models\Letter::PENDING ? 'Tidak ada pengajuan surat yang menunggu' : 'Belum ada data' }}</h3>
            @if ($status === \App\Models\Letter::PENDING)
                <p class="text-xs text-slate-500 mt-1">Kirim link halaman rumah ke warga dari menu <a href="{{ route('admin.households.index') }}" class="font-semibold text-daun">Rumah Warga</a> (buka Ubah).</p>
            @endif
        </div>
    @else
        <div class="space-y-4">
            @foreach ($letters as $letter)
                @php $rejectErrors = $errors->getBag("reject{$letter->id}"); @endphp
                <article class="sheet p-4 sm:p-5">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $letter->typeTitle() }}</p>
                            <p class="mt-0.5 font-bold text-slate-900 text-lg">{{ $letter->person['name'] }}</p>
                            <dl class="mt-2 grid gap-x-6 gap-y-1 text-sm sm:grid-cols-2">
                                <div><dt class="inline text-slate-500">Rumah:</dt> <dd class="inline font-semibold">{{ $letter->household->label() }}</dd></div>
                                <div><dt class="inline text-slate-500">NIK:</dt> <dd class="inline font-mono">{{ $letter->person['nik'] ?: '-' }}</dd></div>
                                <div class="sm:col-span-2"><dt class="inline text-slate-500">Keperluan:</dt> <dd class="inline font-semibold">{{ $letter->purpose }}</dd></div>
                                <div><dt class="inline text-slate-500">Diajukan:</dt> <dd class="inline">{{ $letter->created_at->translatedFormat('j M Y, H:i') }}</dd></div>
                                @if ($letter->phone)
                                    <div><dt class="inline text-slate-500">WA:</dt> <dd class="inline">{{ $letter->phone }}</dd></div>
                                @endif
                            </dl>
                            @if (! $letter->person['nik'] || ! $letter->person['birth_date'])
                                <p class="mt-2 text-xs text-amber-800">Data KK anggota ini belum lengkap (NIK/tanggal lahir). Lengkapi di
                                    <a href="{{ route('admin.households.members.index', $letter->household) }}" class="font-semibold underline">data penghuni</a> sebelum menyetujui. Data terbaru otomatis dipakai di surat.</p>
                            @endif
                            @if ($letter->isApproved())
                                <p class="mt-2 text-sm">Nomor <b>{{ $letter->number }}</b> · terbit {{ $letter->issued_on->translatedFormat('j M Y') }} oleh {{ $letter->reviewer?->name ?? '-' }}</p>
                            @elseif ($letter->status === \App\Models\Letter::REJECTED)
                                <p class="mt-2 text-sm text-slate-600">Ditolak: <b>{{ $letter->reject_reason }}</b></p>
                            @endif
                        </div>

                        <div class="lg:w-72 shrink-0 space-y-2">
                            @if ($letter->isPending())
                                <form method="POST" action="{{ route('admin.letters.approve', $letter) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-primary w-full text-xs" data-busy="Menerbitkan...">Setujui & beri nomor</button>
                                </form>
                                <details @if ($rejectErrors->any()) open @endif>
                                    <summary class="cursor-pointer text-xs font-semibold text-terakota">Tolak pengajuan</summary>
                                    <form method="POST" action="{{ route('admin.letters.reject', $letter) }}" class="mt-2 space-y-2">
                                        @csrf
                                        <label for="letter_reason_{{ $letter->id }}" class="block text-[11px] font-semibold text-slate-500">Alasan (dilihat warga)</label>
                                        <input id="letter_reason_{{ $letter->id }}" name="reject_reason" type="text" maxlength="255" required class="input min-h-10 py-1.5 text-sm"
                                               placeholder="Mis. keperluan kurang jelas" value="{{ $rejectErrors->any() ? old('reject_reason') : '' }}">
                                        @if ($rejectErrors->has('reject_reason'))
                                            <span class="field-error">{{ $rejectErrors->first('reject_reason') }}</span>
                                        @endif
                                        <button class="btn btn-sm btn-danger w-full text-xs">Tolak</button>
                                    </form>
                                </details>
                            @elseif ($letter->isApproved())
                                <a href="{{ route('admin.letters.print', $letter) }}" target="_blank" class="btn btn-sm btn-quiet w-full text-xs">Cetak surat</a>
                                @if ($letter->phone)
                                    @php
                                        $waText = "Halo, surat {$letter->typeTitle()} untuk {$letter->person['name']} sudah disetujui (No. {$letter->number}). Bisa dicetak dari halaman rumah: ".$letter->household->portalUrl()."\n- Pengurus ".setting('site_name');
                                    @endphp
                                    <a href="https://wa.me/{{ clean_phone($letter->phone) }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener noreferrer"
                                       class="btn btn-sm bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 w-full text-xs">Kabari via WA</a>
                                @endif
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($letters->hasPages())
            <div class="mt-6">{{ $letters->links() }}</div>
        @endif
    @endif
</x-layouts.admin>
