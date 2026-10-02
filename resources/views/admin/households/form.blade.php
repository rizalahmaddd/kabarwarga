<x-layouts.admin :title="$household->exists ? 'Ubah Rumah' : 'Tambah Rumah'">
    <div class="mb-6">
        <a href="{{ route('admin.households.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition mb-3">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Daftar Rumah
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
            {{ $household->exists ? 'Ubah Data Rumah '.$household->number : 'Tambah Data Rumah Warga' }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Data rumah digunakan sebagai unit penagihan iuran warga lingkungan.
        </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-12 items-start">
        <div class="lg:col-span-8">
            <form method="POST" action="{{ $household->exists ? route('admin.households.update', $household) : route('admin.households.store') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-7 space-y-5">
                @csrf
                @if ($household->exists) @method('PUT') @endif

                <div class="grid gap-4 sm:grid-cols-[10rem_1fr]">
                    <div>
                        <label for="number" class="field-label">Nomor / Blok Rumah</label>
                        <input id="number" name="number" type="text" maxlength="30" class="input font-bold" required value="{{ old('number', $household->number) }}" placeholder="Mis. A-12" @error('number') aria-invalid="true" @enderror>
                        @error('number') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="head_name" class="field-label">Nama Kepala Keluarga / Penghuni</label>
                        <input id="head_name" name="head_name" type="text" maxlength="100" class="input font-medium" required value="{{ old('head_name', $household->head_name) }}" placeholder="Mis. Bpk. Bambang Sutrisno" @error('head_name') aria-invalid="true" @enderror>
                        @error('head_name') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="occupancy_status" class="field-label">Status Warga / Hunian</label>
                        <select id="occupancy_status" name="occupancy_status" class="input font-semibold text-sm">
                            <option value="pemilik" @selected(old('occupancy_status', $household->occupancy_status ?? 'pemilik') === 'pemilik')>Pemilik Rumah</option>
                            <option value="kontrak" @selected(old('occupancy_status', $household->occupancy_status) === 'kontrak')>Kontrak / Sewa</option>
                        </select>
                    </div>
                    <div>
                        <label for="phone" class="field-label">Nomor HP / WA <span class="font-normal text-slate-400">(Pengurus)</span></label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm pointer-events-none">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            </span>
                            <input id="phone" name="phone" type="tel" maxlength="30" class="input pl-10" value="{{ old('phone', $household->phone) }}" placeholder="Mis. 081234567890">
                        </div>
                    </div>
                    <div>
                        <label for="kk_number" class="field-label">Nomor KK (Opsional)</label>
                        <input id="kk_number" name="kk_number" type="text" maxlength="30" class="input font-mono" value="{{ old('kk_number', $household->kk_number) }}" placeholder="16 digit nomor KK" @error('kk_number') aria-invalid="true" @enderror>
                        @error('kk_number') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="note" class="field-label">Catatan Tambahan <span class="font-normal text-slate-400">(Hanya pengurus yang bisa lihat)</span></label>
                    <textarea id="note" name="note" rows="3" class="input" placeholder="Mis. Rumah kontrakan, sering dinas luar kota">{{ old('note', $household->note) }}</textarea>
                </div>

                <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 size-5 accent-emerald-600 rounded" @checked(old('is_active', $household->is_active ?? true))>
                    <div>
                        <span class="font-bold text-slate-900 text-sm block">Status Aktif Ditagih Iuran</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Matikan bila rumah kosong atau warga sudah pindah. Riwayat tagihan dan pembayaran lamanya tetap utuh.</span>
                    </div>
                </label>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" class="btn btn-primary flex-1 sm:flex-none justify-center" data-busy="Menyimpan...">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        <span>Simpan Data Rumah</span>
                    </button>
                    <a href="{{ route('admin.households.index') }}" class="btn btn-quiet">Batal</a>
                </div>
            </form>

            @if ($household->exists)
                <div class="mt-6 p-5 bg-white rounded-2xl border border-rose-100 shadow-2xs">
                    <h3 class="font-bold text-slate-900 text-sm">Hapus Rumah</h3>
                    <p class="text-xs text-slate-500 mt-0.5 mb-3">Hanya dapat dihapus bila rumah ini belum memiliki riwayat catatan iuran sama sekali.</p>
                    <form method="POST" action="{{ route('admin.households.destroy', $household) }}" data-confirm="Hapus rumah {{ $household->number }} dari daftar?">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-600 border border-rose-200 bg-rose-50/50 hover:bg-rose-100/70 transition active:scale-95" data-busy="Menghapus...">
                            Hapus Rumah Ini
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="lg:col-span-4 space-y-4">
            @if ($household->exists)
                <div class="p-5 rounded-2xl bg-indigo-50/80 border border-indigo-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-xs text-indigo-950 flex items-center gap-1.5 uppercase tracking-wider">
                            <svg class="size-4 text-indigo-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            Penghuni Rumah
                        </span>
                        <span class="px-2 py-0.5 text-xs font-bold bg-indigo-200/60 text-indigo-900 rounded-full">
                            {{ $household->members()->count() }} Jiwa
                        </span>
                    </div>
                    <p class="text-xs text-indigo-900/80 leading-relaxed">
                        Data anggota keluarga dan penghuni rumah ini dapat diinput manual atau di-scan otomatis via Kartu Keluarga (OCR).
                    </p>
                    <a href="{{ route('admin.households.members.index', $household) }}" class="btn btn-sm btn-primary w-full justify-center text-xs font-bold">
                        Buka Data Penghuni & OCR KK →
                    </a>
                </div>
            @endif

            <div class="p-5 rounded-2xl bg-emerald-50/60 border border-emerald-200/60 space-y-2">
                <span class="font-bold text-xs text-emerald-950 flex items-center gap-1.5">
                    <svg class="size-4 text-daun shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    </svg>
                    Panduan Data Rumah
                </span>
                <p class="text-xs text-emerald-900/80 leading-relaxed">
                    Nomor rumah adalah kode pengenal unik untuk pencatatan buku kas dan iuran warga. Pastikan format penomoran konsisten (mis. A-01, B-12).
                </p>
            </div>
        </div>
    </div>
</x-layouts.admin>
