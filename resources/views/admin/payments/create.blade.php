<x-layouts.admin title="Catat Bayar">
    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section aria-labelledby="judul-catat">
            <div class="mb-4">
                <div class="flex items-center gap-2 text-xs font-bold text-daun uppercase tracking-wider mb-1">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="5" y2="19"/>
                        <line x1="5" x2="19" y1="12" y2="12"/>
                    </svg>
                    Pencatatan Cepat
                </div>
                <h1 id="judul-catat" class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Catat Pembayaran Iuran</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Pilih rumah dan tandai bulan yang diserahkan warga.</p>
            </div>

            @if ($households->isEmpty() || $types->isEmpty())
                <div class="sheet p-8 text-center mt-4">
                    <h3 class="font-bold text-slate-800">Belum bisa mencatat pembayaran</h3>
                    <div class="mt-2 text-xs text-slate-500 space-y-1">
                        @if ($households->isEmpty())
                            <p>Daftar rumah masih kosong. <a href="{{ route('admin.households.create') }}" class="font-bold text-daun underline">Tambah data rumah</a></p>
                        @endif
                        @if ($types->isEmpty())
                            <p>Belum ada jenis iuran aktif. <a href="{{ route('admin.dues-types.create') }}" class="font-bold text-daun underline">Buat jenis iuran</a></p>
                        @endif
                    </div>
                </div>
            @else
                {{-- Step 1: Pilih Rumah & Iuran --}}
                <div class="sheet p-5 bg-white shadow-2xs mb-6">
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b border-slate-100">
                        <span class="size-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">1</span>
                        <h2 class="font-bold text-base text-slate-900">Pilih Rumah & Jenis Iuran</h2>
                    </div>

                    @php
                        $householdOptions = [];
                        foreach ($households as $h) {
                            $householdOptions[$h->id] = $h->number . ' · ' . $h->head_name;
                        }

                        $typeOptions = [];
                        foreach ($types as $t) {
                            $typeOptions[$t->id] = $t->name . ' (' . rupiah($t->amount) . ($t->isMonthly() ? '/bln' : '') . ')';
                        }

                        $yearOptions = [];
                        foreach (range(now()->year + 1, now()->year - 3) as $y) {
                            $yearOptions[$y] = 'Tahun ' . $y;
                        }
                    @endphp

                    <form method="GET" action="{{ route('admin.home') }}" class="grid gap-4 sm:grid-cols-2">
                        {{-- Rumah Selector --}}
                        <div>
                            <label for="rumah" class="field-label">Nomor Rumah & Warga</label>
                            <x-dropdown name="rumah" :value="$household?->id" :options="$householdOptions" placeholder="-- Pilih Rumah Warga --" autosubmit required />
                        </div>

                        {{-- Jenis Iuran Selector --}}
                        <div>
                            <label for="jenis" class="field-label">Jenis Iuran</label>
                            <x-dropdown name="jenis" :value="$type?->id" :options="$typeOptions" placeholder="-- Pilih Jenis Iuran --" autosubmit required />
                        </div>

                        {{-- Year Selector (if monthly) --}}
                        @if ($type?->isMonthly())
                            <div>
                                <label for="tahun" class="field-label">Tahun Periode</label>
                                <x-dropdown name="tahun" :value="$year" :options="$yearOptions" autosubmit />
                            </div>
                        @endif

                        <noscript>
                            <button class="btn btn-quiet sm:col-span-2 justify-self-start text-xs font-bold">Lanjutkan</button>
                        </noscript>
                    </form>
                </div>

                {{-- Step 2 & 3: Pembayaran Form --}}
                @if ($household && $type)
                    <form method="POST" action="{{ route('admin.payments.store') }}" class="sheet p-5 bg-white shadow-2xs space-y-6">
                        @csrf
                        <input type="hidden" name="household_id" value="{{ $household->id }}">
                        <input type="hidden" name="dues_type_id" value="{{ $type->id }}">
                        <input type="hidden" name="year" value="{{ $year }}">

                        @if ($type->isMonthly())
                            <fieldset id="payment-month-picker"
                                      data-unit-amount="{{ $type->amount }}"
                                      data-current-period="{{ now()->format('Y-m') }}">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3 pb-2 border-b border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <span class="size-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">2</span>
                                        <legend class="font-bold text-base text-slate-900">Bulan yang Dibayar ({{ $year }})</legend>
                                    </div>
                                    {{-- Quick Action Buttons --}}
                                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                                        <button type="button" data-period-action="current" class="btn btn-sm btn-quiet text-[11px] py-1 px-3 font-semibold whitespace-nowrap">
                                            Bulan Ini
                                        </button>
                                        <button type="button" data-period-action="all-unpaid" class="btn btn-sm btn-quiet text-[11px] py-1 px-3 font-semibold whitespace-nowrap">
                                            Semua Tunggakan
                                        </button>
                                        <button type="button" data-period-action="clear" class="btn btn-sm btn-quiet text-[11px] py-1 px-3 font-semibold text-slate-500 whitespace-nowrap">
                                            Reset Pilihan
                                        </button>
                                    </div>
                                </div>

                                {{-- Live Calculation Feedback Pill --}}
                                <div class="mb-4 p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                                    <span class="text-slate-600">
                                        Terpilih: <strong id="selected-months-count" class="text-slate-900 font-bold">0</strong> bulan
                                    </span>
                                    <span class="text-slate-600">
                                        Total Iuran: <strong id="selected-months-total" class="text-daun font-extrabold text-sm">Rp 0</strong>
                                    </span>
                                </div>

                                {{-- Months Grid --}}
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2">
                                    @foreach ($months as $month)
                                        @php
                                            $period = $month->format('Y-m');
                                            $done = $existing->get($period);
                                            $applies = $type->appliesToMonth($month);
                                            $isCurrent = $month->equalTo(now()->startOfMonth());
                                        @endphp
                                        @if ($done)
                                            <div class="rounded-xl border border-emerald-300 bg-emerald-50/70 p-2.5 text-center min-h-16 flex flex-col justify-center shadow-2xs">
                                                <span class="block text-xs font-bold text-slate-800">{{ $month->translatedFormat('M') }}</span>
                                                <span class="inline-flex items-center justify-center gap-0.5 text-[10px] font-bold text-daun-dark uppercase mt-1">
                                                    ✓ Lunas
                                                </span>
                                            </div>
                                        @elseif (! $applies)
                                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-2.5 text-center min-h-16 flex flex-col justify-center text-slate-400">
                                                <span class="block text-xs font-bold">{{ $month->translatedFormat('M') }}</span>
                                                <span class="text-[9px]">Belum berlaku</span>
                                            </div>
                                        @else
                                            <label class="flex flex-col items-center justify-center p-2.5 min-h-16 rounded-xl border border-slate-200 bg-white cursor-pointer select-none transition-all hover:border-slate-300 has-[:checked]:border-daun has-[:checked]:bg-emerald-50/80 has-[:checked]:shadow-2xs shadow-2xs {{ $isCurrent ? 'ring-2 ring-emerald-400/40' : '' }}">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-bold text-slate-900">{{ $month->translatedFormat('M') }}</span>
                                                    @if ($isCurrent)
                                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                                    @endif
                                                </div>
                                                <input type="checkbox" name="periods[]" value="{{ $period }}"
                                                       class="mt-1 size-5 rounded border-slate-300 accent-daun cursor-pointer"
                                                       @checked(in_array($period, old('periods', $preselect)))
                                                       aria-label="{{ $month->translatedFormat('F Y') }}">
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                                @error('periods') <span class="field-error">{{ $message }}</span> @enderror
                                @error('periods.*') <span class="field-error">Format bulan tidak dikenali.</span> @enderror
                            </fieldset>
                        @else
                            {{-- Sekali bayar --}}
                            <div class="pb-2 border-b border-slate-100">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="size-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">2</span>
                                    <h2 class="font-bold text-base text-slate-900">Pembayaran {{ $type->name }}</h2>
                                </div>
                                @if ($existing->get(''))
                                    <div class="p-3.5 rounded-xl border border-emerald-300 bg-emerald-50 text-xs text-emerald-950 font-medium">
                                        ✓ Rumah {{ $household->number }} sudah tercatat lunas pada {{ $existing->get('')->paid_on->translatedFormat('j F Y') }}.
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($type->isMonthly() || ! $existing->get(''))
                            <div class="space-y-4 pt-2">
                                <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                                    <span class="size-6 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">3</span>
                                    <h2 class="font-bold text-base text-slate-900">Detail Transaksi</h2>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    {{-- Nominal --}}
                                    <div>
                                        <label for="amount" class="field-label">Total Nominal Diterima (Rp)</label>
                                        <input id="amount" name="amount" type="number" inputmode="numeric" min="0" step="500" class="input font-bold text-base tabular-nums" required
                                               value="{{ old('amount', $type->amount) }}" @error('amount') aria-invalid="true" @enderror>
                                        <span class="field-hint">Otomatis terhitung sesuai bulan yang dicentang. Totalnya dibagi rata ke tiap bulan.</span>
                                        @error('amount') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Tanggal Terima --}}
                                    <div>
                                        <label for="paid_on" class="field-label">Tanggal Terima Uang</label>
                                        <input id="paid_on" name="paid_on" type="date" class="input text-sm font-semibold" required max="{{ now()->toDateString() }}"
                                               value="{{ old('paid_on', now()->toDateString()) }}" @error('paid_on') aria-invalid="true" @enderror>
                                        @error('paid_on') <span class="field-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                {{-- Cara Bayar (Large Touch Pills) --}}
                                <div>
                                    <label class="field-label">Metode Pembayaran</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach (\App\Models\Payment::METHODS as $key => $label)
                                            <label class="flex items-center justify-center gap-2 min-h-12 px-3 rounded-xl border border-slate-200 bg-white font-bold text-xs cursor-pointer transition-all select-none has-[:checked]:border-daun has-[:checked]:bg-emerald-50 has-[:checked]:text-daun-dark hover:border-slate-300">
                                                <input type="radio" name="method" value="{{ $key }}" class="accent-daun size-4" @checked(old('method', 'tunai') === $key)>
                                                <span>{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Catatan --}}
                                <div>
                                    <label for="note" class="field-label">Catatan Tambahan <span class="font-normal text-slate-400">(opsional)</span></label>
                                    <input id="note" name="note" type="text" maxlength="255" class="input text-sm" value="{{ old('note') }}" placeholder="Misal: dititipkan via tetangga / transfer bank">
                                </div>

                                <button class="btn btn-primary w-full min-h-12 text-sm font-bold shadow-sm justify-center" data-busy="Menyimpan...">
                                    Simpan Catatan Pembayaran
                                </button>
                            </div>
                        @endif
                    </form>
                @endif
            @endif
        </section>

        {{-- Aside: Baru Dicatat Hari Ini --}}
        <aside aria-labelledby="baru-dicatat">
            <div class="sheet p-5 bg-white shadow-2xs sticky top-20">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <div class="size-2 rounded-full bg-emerald-500"></div>
                        <h2 id="baru-dicatat" class="font-bold text-base text-slate-900">Baru Dicatat</h2>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Terbaru</span>
                </div>

                <div class="divide-y divide-slate-100 mt-1">
                    @forelse ($recent as $payment)
                        <div class="py-3">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-xs px-2 py-0.5 rounded bg-slate-900 text-white">
                                    {{ $payment->household->number }}
                                </span>
                                <span class="font-bold text-xs text-slate-900">{{ rupiah($payment->amount) }}</span>
                            </div>
                            <p class="text-xs text-slate-700 font-medium mt-1 truncate">{{ $payment->duesType->name }} ({{ $payment->periodLabel() }})</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                {{ $payment->paid_on->translatedFormat('j M Y') }} · {{ strtoupper($payment->method) }}
                            </p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">Belum ada pembayaran yang baru dicatat.</p>
                    @endforelse
                </div>

                @if ($recent->isNotEmpty())
                    <div class="pt-3 border-t border-slate-100">
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-quiet w-full text-xs font-bold">
                            Lihat Semua Riwayat Bayar →
                        </a>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</x-layouts.admin>

