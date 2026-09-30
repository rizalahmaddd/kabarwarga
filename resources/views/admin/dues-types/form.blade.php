<x-layouts.admin :title="$duesType->exists ? 'Ubah Jenis Iuran' : 'Tambah Jenis Iuran'">
    <div class="mb-6">
        <a href="{{ route('admin.dues-types.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition mb-3">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Daftar Jenis Iuran
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
            {{ $duesType->exists ? 'Ubah '.$duesType->name : 'Tambah Jenis Iuran Baru' }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Konfigurasikan skema penagihan iuran warga lingkungan.
        </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-12 items-start">
        <div class="lg:col-span-8">
            <form method="POST" action="{{ $duesType->exists ? route('admin.dues-types.update', $duesType) : route('admin.dues-types.store') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-7 space-y-5">
                @csrf
                @if ($duesType->exists) @method('PUT') @endif

                <div>
                    <label for="name" class="field-label">Nama Iuran</label>
                    <input id="name" name="name" type="text" maxlength="100" class="input font-medium" required value="{{ old('name', $duesType->name) }}" placeholder="Mis. Iuran Kebersihan & Sampah" @error('name') aria-invalid="true" @enderror>
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <fieldset>
                    <legend class="field-label">Frekuensi Penagihan</legend>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <label class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer transition hover:bg-slate-100/70 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-900 text-xs sm:text-sm font-medium">
                            <input type="radio" name="frequency" value="bulanan" class="accent-emerald-600 size-4" @checked(old('frequency', $duesType->frequency) === 'bulanan')>
                            <span>Setiap Bulan</span>
                        </label>
                        <label class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer transition hover:bg-slate-100/70 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-900 text-xs sm:text-sm font-medium">
                            <input type="radio" name="frequency" value="sekali" class="accent-emerald-600 size-4" @checked(old('frequency', $duesType->frequency) === 'sekali')>
                            <span>Sekali Bayar</span>
                        </label>
                    </div>
                </fieldset>

                <div>
                    <label for="amount" class="field-label">Nominal per Rumah (Rp)</label>
                    <div class="relative max-w-xs">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm pointer-events-none">Rp</span>
                        <input id="amount" name="amount" type="number" inputmode="numeric" min="0" step="500" class="input pl-10 font-bold tabular-nums" required value="{{ old('amount', $duesType->amount) }}" placeholder="0" @error('amount') aria-invalid="true" @enderror>
                    </div>
                    @error('amount') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div data-show-when="frequency=bulanan">
                    <label for="starts_on" class="field-label">Mulai Berlaku <span class="font-normal text-slate-400">(Opsional)</span></label>
                    <input id="starts_on" name="starts_on" type="month" class="input max-w-xs" value="{{ old('starts_on', $duesType->starts_on?->format('Y-m')) }}">
                    <span class="field-hint">Bulan sebelum periode ini tidak akan dihitung sebagai tunggakan warga.</span>
                </div>

                <div data-show-when="frequency=sekali">
                    <label for="due_on" class="field-label">Batas Waktu Pembayaran <span class="font-normal text-slate-400">(Opsional)</span></label>
                    <input id="due_on" name="due_on" type="date" class="input max-w-xs" value="{{ old('due_on', $duesType->due_on?->format('Y-m-d')) }}">
                </div>

                <div>
                    <label for="description" class="field-label">Keterangan untuk Warga <span class="font-normal text-slate-400">(Opsional)</span></label>
                    <textarea id="description" name="description" rows="3" class="input" placeholder="Mis. Digunakan untuk honor petugas kebersihan dan kantong sampah bulanan">{{ old('description', $duesType->description) }}</textarea>
                </div>

                <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 size-5 accent-emerald-600 rounded" @checked(old('is_active', $duesType->is_active))>
                    <div>
                        <span class="font-bold text-slate-900 text-sm block">Status Aktif Berjalan</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Matikan jika iuran sudah selesai/kadaluarsa. Catatan lama tetap aman dan bisa diaudit.</span>
                    </div>
                </label>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" class="btn btn-primary flex-1 sm:flex-none justify-center" data-busy="Menyimpan...">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        <span>Simpan Pengaturan</span>
                    </button>
                    <a href="{{ route('admin.dues-types.index') }}" class="btn btn-quiet">Batal</a>
                </div>
            </form>

            @if ($duesType->exists)
                <div class="mt-6 p-5 bg-white rounded-2xl border border-rose-100 shadow-2xs">
                    <h3 class="font-bold text-slate-900 text-sm">Hapus Jenis Iuran</h3>
                    <p class="text-xs text-slate-500 mt-0.5 mb-3">Hanya dapat dihapus bila belum ada catatan transaksi pembayaran yang terkait.</p>
                    <form method="POST" action="{{ route('admin.dues-types.destroy', $duesType) }}" data-confirm="Hapus jenis iuran {{ $duesType->name }}?">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-600 border border-rose-200 bg-rose-50/50 hover:bg-rose-100/70 transition active:scale-95" data-busy="Menghapus...">
                            Hapus Jenis Iuran Ini
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="lg:col-span-4 space-y-4">
            <div class="p-5 rounded-2xl bg-emerald-50/60 border border-emerald-200/60 space-y-2">
                <span class="font-bold text-xs text-emerald-950 flex items-center gap-1.5">
                    <svg class="size-4 text-daun shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                    </svg>
                    Skema Penagihan
                </span>
                <p class="text-xs text-emerald-900/80 leading-relaxed">
                    <strong>Iuran Bulanan</strong> akan otomatis membuat kolom setiap bulan di Buku Iuran. <strong>Sekali Bayar</strong> digunakan untuk kegiatan tertentu (misal: 17 Agustusan atau aspal jalan).
                </p>
            </div>
        </div>
    </div>
</x-layouts.admin>
