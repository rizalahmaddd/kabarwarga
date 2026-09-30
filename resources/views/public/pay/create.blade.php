<x-layouts.public title="Bayar Iuran" description="Bayar iuran warga lewat transfer, lalu kirim bukti untuk dikonfirmasi pengurus.">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"/>
                <line x1="2" x2="22" y1="10" y2="10"/>
            </svg>
            Tanpa perlu login
        </div>
        <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Bayar Iuran</h1>
        <p class="mt-1.5 text-sm text-slate-600 max-w-2xl leading-relaxed">
            Pilih rumah dan iuran, transfer ke rekening pengurus, lalu kirim foto bukti transfernya. Setelah dicek pengurus, iuran Anda otomatis tercatat lunas.
        </p>
    </div>

    @if ($accounts->isEmpty())
        <div class="sheet p-8 text-center">
            <h2 class="font-bold text-slate-800">Pembayaran online belum dibuka</h2>
            <p class="text-sm text-slate-500 mt-1">Pengurus belum mengisi rekening pembayaran. Untuk sementara silakan bayar langsung ke bendahara.</p>
            @if (setting('treasurer_contact'))
                <p class="text-sm text-slate-700 mt-3">Kontak bendahara: <strong>{{ setting('treasurer_contact') }}</strong></p>
            @endif
        </div>
    @elseif ($households->isEmpty() || $types->isEmpty())
        <div class="sheet p-8 text-center">
            <h2 class="font-bold text-slate-800">Belum bisa menerima pembayaran</h2>
            <p class="text-sm text-slate-500 mt-1">Pengurus belum mengisi daftar rumah atau jenis iuran.</p>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="space-y-5 min-w-0">
                @php
                    $householdOptions = $households->mapWithKeys(fn ($h) => [$h->id => $h->label()])->all();
                    $typeOptions = $types->mapWithKeys(fn ($t) => [$t->id => $t->name.' · '.rupiah($t->amount).($t->isMonthly() ? '/bulan' : '')])->all();
                @endphp

                <section class="sheet p-5" aria-labelledby="langkah-1">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="size-7 rounded-full bg-slate-900 text-white text-sm font-bold flex items-center justify-center">1</span>
                        <h2 id="langkah-1" class="font-bold text-base text-slate-900">Rumah & jenis iuran</h2>
                    </div>
                    <form method="GET" action="{{ route('pay.create') }}" class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="rumah" class="field-label">Rumah Anda</label>
                            <x-dropdown name="rumah" :value="$household?->id" :options="$householdOptions" placeholder="Pilih nomor rumah" searchable autosubmit required />
                        </div>
                        <div>
                            <label for="jenis" class="field-label">Iuran yang dibayar</label>
                            <x-dropdown name="jenis" :value="$type?->id" :options="$typeOptions" placeholder="Pilih jenis iuran" autosubmit required />
                        </div>
                        <noscript>
                            <button class="btn btn-quiet">Lanjutkan</button>
                        </noscript>
                    </form>
                </section>

                @if ($household && $type)
                    @php
                        $alreadyDone = ! $type->isMonthly() && ($paid->has('') || in_array('', $pending, true));
                    @endphp

                    @if ($alreadyDone)
                        <div class="sheet p-5 {{ $paid->has('') ? 'border-emerald-300 bg-emerald-50' : 'border-amber-300 bg-amber-50' }}">
                            @if ($paid->has(''))
                                <p class="font-bold text-emerald-900">✓ {{ $type->name }} rumah {{ $household->number }} sudah lunas</p>
                                <p class="text-sm text-emerald-800 mt-1">Tercatat pada {{ $paid->get('')->paid_on->translatedFormat('j F Y') }}. Tidak perlu membayar lagi.</p>
                            @else
                                <p class="font-bold text-amber-900">Bukti pembayaran sedang dicek pengurus</p>
                                <p class="text-sm text-amber-800 mt-1">{{ $type->name }} rumah {{ $household->number }} sudah dikirim dan menunggu konfirmasi. Tidak perlu dikirim ulang.</p>
                            @endif
                        </div>
                    @else
                        <form method="POST" action="{{ route('pay.store') }}" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <input type="hidden" name="household_id" value="{{ $household->id }}">
                            <input type="hidden" name="dues_type_id" value="{{ $type->id }}">

                            @if ($errors->any())
                                <div role="alert" class="rounded-2xl border border-terakota/40 bg-terakota-soft/50 p-4 text-sm text-terakota font-semibold">
                                    Ada yang perlu diperbaiki di bawah.
                                    @unless ($errors->has('proof'))
                                        File bukti perlu dipilih ulang.
                                    @endunless
                                </div>
                            @endif

                            <section class="sheet p-5" aria-labelledby="langkah-2">
                                @if ($type->isMonthly())
                                    <fieldset id="payment-month-picker" data-unit-amount="{{ $type->amount }}" data-current-period="{{ now()->format('Y-m') }}">
                                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="size-7 rounded-full bg-slate-900 text-white text-sm font-bold flex items-center justify-center">2</span>
                                                <legend id="langkah-2" class="font-bold text-base text-slate-900">Bulan yang dibayar</legend>
                                            </div>
                                            <nav class="flex items-center gap-1 text-sm" aria-label="Pilih tahun">
                                                @foreach (range(now()->year - 1, now()->year + 1) as $y)
                                                    <a href="{{ route('pay.create', ['rumah' => $household->id, 'jenis' => $type->id, 'tahun' => $y]) }}"
                                                       @if ($y === $year) aria-current="page" @endif
                                                       class="px-3 py-1.5 rounded-lg font-bold no-underline {{ $y === $year ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $y }}</a>
                                                @endforeach
                                            </nav>
                                        </div>

                                        <div class="flex flex-wrap gap-2 mb-3">
                                            <button type="button" data-period-action="current" class="btn btn-sm btn-quiet text-xs">Bulan ini saja</button>
                                            <button type="button" data-period-action="until-current" class="btn btn-sm btn-quiet text-xs">Semua yang belum sampai bulan ini</button>
                                            <button type="button" data-period-action="clear" class="btn btn-sm btn-quiet text-xs text-slate-500">Kosongkan</button>
                                        </div>

                                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                                            @foreach ($months as $month)
                                                @php
                                                    $period = $month->format('Y-m');
                                                    $isCurrent = $month->equalTo(now()->startOfMonth());
                                                @endphp
                                                @if ($paid->has($period))
                                                    <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-2.5 text-center min-h-16 flex flex-col justify-center">
                                                        <span class="text-sm font-bold text-slate-800">{{ $month->translatedFormat('M') }}</span>
                                                        <span class="text-[11px] font-bold text-daun-dark mt-0.5">✓ Lunas</span>
                                                    </div>
                                                @elseif (in_array($period, $pending, true))
                                                    <div class="rounded-xl border border-amber-300 bg-amber-50 p-2.5 text-center min-h-16 flex flex-col justify-center">
                                                        <span class="text-sm font-bold text-slate-800">{{ $month->translatedFormat('M') }}</span>
                                                        <span class="text-[11px] font-bold text-amber-800 mt-0.5">Dicek</span>
                                                    </div>
                                                @elseif (! $type->appliesToMonth($month))
                                                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-2.5 text-center min-h-16 flex flex-col justify-center text-slate-400">
                                                        <span class="text-sm font-bold">{{ $month->translatedFormat('M') }}</span>
                                                        <span class="text-[10px]">Belum berlaku</span>
                                                    </div>
                                                @else
                                                    <label class="flex flex-col items-center justify-center p-2.5 min-h-16 rounded-xl border border-slate-200 bg-white cursor-pointer select-none transition-all hover:border-slate-300 has-[:checked]:border-daun has-[:checked]:bg-emerald-50 {{ $isCurrent ? 'ring-2 ring-emerald-400/40' : '' }}">
                                                        <span class="text-sm font-bold text-slate-900">{{ $month->translatedFormat('M') }}</span>
                                                        <input type="checkbox" name="periods[]" value="{{ $period }}" class="mt-1 size-5 accent-daun cursor-pointer"
                                                               @checked(in_array($period, old('periods', [])))
                                                               aria-label="{{ $month->translatedFormat('F Y') }}">
                                                    </label>
                                                @endif
                                            @endforeach
                                        </div>
                                        <p class="field-hint mt-2">Bulan berbingkai hijau = bulan ini. Bulan yang sudah lunas atau sedang dicek tidak bisa dipilih lagi.</p>
                                        @error('periods') <span class="field-error">{{ $message }}</span> @enderror
                                        @error('periods.*') <span class="field-error">Ada bulan yang tidak valid, coba pilih ulang.</span> @enderror
                                    </fieldset>
                                @else
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="size-7 rounded-full bg-slate-900 text-white text-sm font-bold flex items-center justify-center">2</span>
                                        <h2 id="langkah-2" class="font-bold text-base text-slate-900">{{ $type->name }}</h2>
                                    </div>
                                    <p class="text-sm text-slate-600">Iuran sekali bayar untuk rumah {{ $household->number }}.</p>
                                    @if ($type->due_on)
                                        <p class="text-sm text-amber-700 font-semibold mt-1">Batas bayar: {{ $type->due_on->translatedFormat('j F Y') }}</p>
                                    @endif
                                    @error('periods') <span class="field-error">{{ $message }}</span> @enderror
                                @endif
                            </section>

                            <section class="sheet p-5" aria-labelledby="langkah-3">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="size-7 rounded-full bg-slate-900 text-white text-sm font-bold flex items-center justify-center">3</span>
                                    <h2 id="langkah-3" class="font-bold text-base text-slate-900">Transfer ke rekening pengurus</h2>
                                </div>

                                <div class="rounded-2xl bg-slate-900 text-white p-4 mb-4 flex flex-wrap items-end justify-between gap-2">
                                    <div>
                                        <span class="block text-xs text-slate-300">Jumlah yang ditransfer</span>
                                        <strong id="selected-months-total" class="block text-2xl font-extrabold tabular-nums">{{ rupiah($type->isMonthly() ? 0 : $type->amount) }}</strong>
                                    </div>
                                    @if ($type->isMonthly())
                                        <span class="text-xs text-slate-300"><span id="selected-months-count">0</span> bulan × {{ rupiah($type->amount) }}</span>
                                    @endif
                                </div>

                                <p class="field-label">Rekening tujuan yang Anda pakai</p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach ($accounts as $account)
                                        <label class="block p-4 rounded-xl border border-slate-200 bg-white cursor-pointer transition-all hover:border-slate-300 has-[:checked]:border-daun has-[:checked]:bg-emerald-50/60">
                                            <span class="flex items-start gap-3">
                                                <input type="radio" name="bank_account_id" value="{{ $account->id }}" class="mt-1 size-5 accent-daun shrink-0"
                                                       @checked((string) old('bank_account_id', $accounts->count() === 1 ? $account->id : '') === (string) $account->id)>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block font-bold text-slate-900">{{ $account->bank_name }}</span>
                                                    @if ($account->account_number)
                                                        <span class="flex items-center gap-2 mt-1">
                                                            <span class="font-mono text-lg font-bold tracking-wide text-slate-900 break-all">{{ $account->account_number }}</span>
                                                            <button type="button" class="btn btn-sm btn-quiet text-xs shrink-0" data-copy="{{ preg_replace('/\s+/', '', $account->account_number) }}">Salin</button>
                                                        </span>
                                                    @endif
                                                    @if ($account->account_name)
                                                        <span class="block text-sm text-slate-600 mt-0.5">a.n. {{ $account->account_name }}</span>
                                                    @endif
                                                    @if ($account->qrisUrl())
                                                        <a href="{{ $account->qrisUrl() }}" target="_blank" rel="noopener" class="block mt-3">
                                                            <img src="{{ $account->qrisUrl() }}" alt="QRIS {{ $account->bank_name }}" class="w-full max-w-56 rounded-lg border border-slate-200 bg-white" loading="lazy">
                                                            <span class="block text-xs font-semibold mt-1">Buka QRIS ukuran penuh</span>
                                                        </a>
                                                    @endif
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('bank_account_id') <span class="field-error">{{ $message }}</span> @enderror

                                @if (setting('payment_info'))
                                    <p class="mt-4 text-sm text-slate-600 whitespace-pre-line bg-slate-50 border border-slate-200 rounded-xl p-3">{{ setting('payment_info') }}</p>
                                @endif
                            </section>

                            <section class="sheet p-5 space-y-4" aria-labelledby="langkah-4">
                                <div class="flex items-center gap-2">
                                    <span class="size-7 rounded-full bg-slate-900 text-white text-sm font-bold flex items-center justify-center">4</span>
                                    <h2 id="langkah-4" class="font-bold text-base text-slate-900">Kirim bukti transfer</h2>
                                </div>

                                <div>
                                    <label for="proof" class="field-label">Foto / screenshot bukti transfer</label>
                                    <label for="proof" class="flex flex-col items-center justify-center gap-2 p-5 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 text-center cursor-pointer hover:border-daun hover:bg-emerald-50/40 transition-colors">
                                        <img data-proof-preview alt="Pratinjau bukti" class="hidden max-h-64 rounded-lg border border-slate-200">
                                        <span data-proof-name class="text-sm font-bold text-slate-700">Ketuk untuk pilih foto</span>
                                        <span class="text-xs text-slate-500">JPG, PNG, atau PDF · maksimal 5 MB</span>
                                    </label>
                                    <input id="proof" name="proof" type="file" accept="image/*,application/pdf" required class="sr-only" data-proof-input @error('proof') aria-invalid="true" @enderror>
                                    @error('proof') <span class="field-error">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="payer_name" class="field-label">Nama pengirim <span class="font-normal text-slate-400">(opsional)</span></label>
                                        <input id="payer_name" name="payer_name" type="text" maxlength="100" class="input" value="{{ old('payer_name') }}" placeholder="Nama di rekening pengirim">
                                    </div>
                                    <div>
                                        <label for="phone" class="field-label">No. WhatsApp <span class="font-normal text-slate-400">(opsional)</span></label>
                                        <input id="phone" name="phone" type="tel" maxlength="30" class="input" value="{{ old('phone') }}" placeholder="Supaya pengurus bisa menghubungi">
                                    </div>
                                </div>
                                <div>
                                    <label for="note" class="field-label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                                    <input id="note" name="note" type="text" maxlength="255" class="input" value="{{ old('note') }}" placeholder="Mis. ditransfer oleh anak saya">
                                </div>

                                <button class="btn btn-primary w-full text-base" data-busy="Mengirim...">Kirim Bukti Pembayaran</button>
                                <p class="text-xs text-slate-500 text-center">Iuran tercatat lunas setelah pengurus mengecek bukti Anda.</p>
                            </section>
                        </form>
                    @endif
                @endif
            </div>

            <aside class="space-y-4">
                @if ($history->isNotEmpty())
                    <section class="sheet p-5" aria-labelledby="riwayat-kirim">
                        <h2 id="riwayat-kirim" class="font-bold text-base text-slate-900">Kiriman rumah {{ $household->number }}</h2>
                        <ul class="mt-2 divide-y divide-slate-100">
                            @foreach ($history as $item)
                                <li class="py-2.5">
                                    <a href="{{ route('pay.show', $item->code) }}" class="flex items-start justify-between gap-2 no-underline">
                                        <span class="min-w-0">
                                            <span class="block text-sm font-bold text-slate-800 truncate">{{ $item->duesType->name }}</span>
                                            <span class="block text-xs text-slate-500">{{ $item->periodsLabel() }}</span>
                                        </span>
                                        @include('public.pay.status-badge', ['submission' => $item])
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section class="sheet p-5 text-sm text-slate-600 space-y-2">
                    <h2 class="font-bold text-base text-slate-900">Cara kerjanya</h2>
                    <p>1. Transfer sesuai jumlah yang tertera.</p>
                    <p>2. Kirim foto bukti transfernya di sini.</p>
                    <p>3. Pengurus mengecek. Kalau cocok, iuran langsung tercatat lunas di halaman <a href="{{ route('dues.index') }}">Cek Iuran</a>.</p>
                    @if (setting('treasurer_contact'))
                        <p class="pt-2 border-t border-slate-100">Ada kendala? Hubungi bendahara: <strong class="text-slate-800">{{ setting('treasurer_contact') }}</strong></p>
                    @endif
                </section>
            </aside>
        </div>
    @endif
</x-layouts.public>
