<x-layouts.admin :title="$expense->exists ? 'Ubah Pengeluaran' : 'Catat Pengeluaran'">
    <div class="mb-4">
        <a href="{{ route('admin.expenses.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Daftar Pengeluaran
        </a>
    </div>

    <div class="max-w-xl">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
            {{ $expense->exists ? 'Ubah Pengeluaran' : 'Catat Pengeluaran Baru' }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Pengeluaran ini akan langsung tercatat dan dapat dipantau seluruh warga di Buku Kas.
        </p>

        <form method="POST" action="{{ $expense->exists ? route('admin.expenses.update', $expense) : route('admin.expenses.store') }}" class="mt-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-7 space-y-5">
            @csrf
            @if ($expense->exists) @method('PUT') @endif

            <div>
                <label for="description" class="field-label">Keperluan Pengeluaran</label>
                <input id="description" name="description" type="text" maxlength="255" class="input" required value="{{ old('description', $expense->description) }}" placeholder="Mis. Beli lampu penerangan jalan gang 3" @error('description') aria-invalid="true" @enderror>
                <span class="field-hint">Tuliskan keterangan yang mudah dipahami warga umum.</span>
                @error('description') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="amount" class="field-label">Nominal Pengeluaran (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm pointer-events-none">Rp</span>
                        <input id="amount" name="amount" type="number" inputmode="numeric" min="1" class="input pl-10 font-bold tabular-nums" required value="{{ old('amount', $expense->amount) }}" placeholder="0" @error('amount') aria-invalid="true" @enderror>
                    </div>
                    @error('amount') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="spent_on" class="field-label">Tanggal Pengeluaran</label>
                    <input id="spent_on" name="spent_on" type="date" class="input" required max="{{ now()->toDateString() }}" value="{{ old('spent_on', $expense->spent_on?->format('Y-m-d') ?? now()->toDateString()) }}" @error('spent_on') aria-invalid="true" @enderror>
                    @error('spent_on') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="btn btn-primary flex-1 sm:flex-none justify-center" data-busy="Menyimpan...">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    <span>{{ $expense->exists ? 'Simpan Perubahan' : 'Catat Pengeluaran' }}</span>
                </button>
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-quiet">Batal</a>
            </div>
        </form>
    </div>
</x-layouts.admin>
