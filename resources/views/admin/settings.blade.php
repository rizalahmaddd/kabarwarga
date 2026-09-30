<x-layouts.admin title="Pengaturan">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Pengaturan Lingkungan</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Sesuaikan identitas RT/RW dan informasi kontak pengurus yang tampil di halaman warga.
        </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-12 items-start">
        <!-- Form Identitas Lingkungan (Left 8 cols) -->
        <div class="lg:col-span-8">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-7 space-y-5">
                @csrf @method('PUT')
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <div class="size-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900 text-base">Identitas & Informasi Warga</h2>
                        <p class="text-xs text-slate-400">Data utama yang ditampilkan pada portal publik warga</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="site_name" class="field-label">Nama Lingkungan / RT / RW</label>
                        <input id="site_name" name="site_name" type="text" maxlength="80" class="input font-medium" required value="{{ old('site_name', $settings['site_name']) }}" placeholder="Mis. RT 04 / RW 07 Griya Asri" @error('site_name') aria-invalid="true" @enderror>
                        @error('site_name') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="site_tagline" class="field-label">Slogan / Keterangan Singkat <span class="font-normal text-slate-400">(Opsional)</span></label>
                        <input id="site_tagline" name="site_tagline" type="text" maxlength="160" class="input" value="{{ old('site_tagline', $settings['site_tagline']) }}" placeholder="Mis. Guyub rukun, transparan, dan amanah">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="field-label">Alamat / Wilayah <span class="font-normal text-slate-400">(Opsional)</span></label>
                        <input id="address" name="address" type="text" maxlength="255" class="input" value="{{ old('address', $settings['address']) }}" placeholder="Mis. Kelurahan Sukamaju, Kecamatan Medan Baru">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="treasurer_contact" class="field-label">Kontak WhatsApp Bendahara / Pengurus <span class="font-normal text-slate-400">(Tampil di halaman warga)</span></label>
                        <input id="treasurer_contact" name="treasurer_contact" type="text" maxlength="160" class="input" value="{{ old('treasurer_contact', $settings['treasurer_contact']) }}" placeholder="Mis. Bpk. Hendra (081234567890)">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="payment_info" class="field-label">Petunjuk Pembayaran <span class="font-normal text-slate-400">(Tampil di halaman iuran & bayar)</span></label>
                        <textarea id="payment_info" name="payment_info" rows="3" maxlength="500" class="input" placeholder="Mis. Bisa juga setor tunai saat penagihan door-to-door tiap tanggal 5.">{{ old('payment_info', $settings['payment_info']) }}</textarea>
                        <span class="field-hint">Nomor rekening & QRIS diatur di menu <a href="{{ route('admin.bank-accounts.index') }}" class="font-semibold text-daun">Rekening</a>.</span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary" data-busy="Menyimpan...">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Column (Right 4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Form Ganti Password -->
            <form method="POST" action="{{ route('admin.settings.password') }}" class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs p-5 sm:p-6 space-y-4">
                @csrf @method('PUT')
                <div class="flex items-center gap-2.5 pb-2.5 border-b border-slate-100">
                    <div class="size-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold shrink-0">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900 text-sm">Ganti Kata Sandi</h2>
                        <p class="text-[11px] text-slate-400">Akun pengurus yang aktif saat ini</p>
                    </div>
                </div>

                <div>
                    <label for="current_password" class="field-label text-xs">Kata Sandi Saat Ini</label>
                    <input id="current_password" name="current_password" type="password" class="input py-2 text-sm" required autocomplete="current-password" @error('current_password', 'password') aria-invalid="true" @enderror>
                    @error('current_password', 'password') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="field-label text-xs">Kata Sandi Baru</label>
                    <input id="password" name="password" type="password" class="input py-2 text-sm" required minlength="8" autocomplete="new-password" @error('password', 'password') aria-invalid="true" @enderror>
                    @error('password', 'password') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="field-label text-xs">Ulangi Kata Sandi Baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input py-2 text-sm" required autocomplete="new-password">
                </div>

                <div class="pt-1">
                    <button type="submit" class="btn btn-quiet w-full text-xs font-bold" data-busy="Menyimpan...">Perbarui Sandi</button>
                </div>
            </form>

            <!-- Card Bantuan / Tautan Cepat -->
            <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/60 space-y-2">
                <span class="font-bold text-xs text-emerald-950 flex items-center gap-1.5">
                    <svg class="size-4 text-daun shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                    </svg>
                    Informasi Publik Warga
                </span>
                <p class="text-[11px] text-emerald-900/80 leading-relaxed">
                    Data nama RT, slogan, dan kontak bendahara langsung muncul di footer dan halaman beranda portal warga.
                </p>
                <div class="pt-1">
                    <a href="{{ route('home') }}" target="_blank" class="text-xs font-bold text-daun-dark hover:underline inline-flex items-center gap-1">
                        Cek tampilan di Web Warga &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
