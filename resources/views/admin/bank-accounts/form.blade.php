<x-layouts.admin :title="$account->exists ? 'Ubah Rekening' : 'Tambah Rekening'">
    <div class="mb-4">
        <a href="{{ route('admin.bank-accounts.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Daftar Rekening
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">{{ $account->exists ? 'Ubah Rekening' : 'Tambah Rekening Pembayaran' }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data rekening bank, e-wallet (DANA, GoPay, OVO), atau QRIS untuk penerimaan iuran warga.</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-12 items-start">
        <div class="lg:col-span-8">
            <form method="POST" enctype="multipart/form-data"
                  action="{{ $account->exists ? route('admin.bank-accounts.update', $account) : route('admin.bank-accounts.store') }}"
                  class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-7 space-y-5">
                @csrf
                @if ($account->exists) @method('PUT') @endif

                <div>
                    <label for="bank_name" class="field-label">Nama Bank / E-wallet</label>
                    <input id="bank_name" name="bank_name" type="text" maxlength="60" class="input font-bold" required list="daftar-bank"
                           value="{{ old('bank_name', $account->bank_name) }}" placeholder="Mis. BCA, BRI, DANA, QRIS" @error('bank_name') aria-invalid="true" @enderror>
                    <datalist id="daftar-bank">
                        @foreach (['BCA', 'BRI', 'BNI', 'Mandiri', 'BSI', 'SeaBank', 'Jago', 'DANA', 'GoPay', 'OVO', 'ShopeePay', 'QRIS'] as $bank)
                            <option value="{{ $bank }}">
                        @endforeach
                    </datalist>
                    @error('bank_name') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="account_number" class="field-label">Nomor Rekening / HP</label>
                        <input id="account_number" name="account_number" type="text" inputmode="numeric" maxlength="60" class="input font-mono font-bold"
                               value="{{ old('account_number', $account->account_number) }}" placeholder="Mis. 1234567890" @error('account_number') aria-invalid="true" @enderror>
                        @error('account_number') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="account_name" class="field-label">Atas Nama</label>
                        <input id="account_name" name="account_name" type="text" maxlength="100" class="input"
                               value="{{ old('account_name', $account->account_name) }}" placeholder="Mis. Kas RT 04">
                    </div>
                </div>

                {{-- Upload QRIS Modern Card --}}
                <div class="space-y-3 pt-1" data-image-uploader data-has-existing="{{ $account->qrisUrl() ? 'true' : 'false' }}">
                    <div>
                        <label class="field-label block font-bold text-slate-900 text-sm">
                            Gambar QRIS <span class="font-normal text-slate-500 text-xs">(Opsional, Maks. 3 MB)</span>
                        </label>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Warga dapat memindai atau membuka gambar QRIS ini dari aplikasi m-banking atau e-wallet saat konfirmasi pembayaran iuran.
                        </p>
                    </div>

                    <input type="hidden" name="remove_qris" data-remove-input value="0">
                    <input id="qris" name="qris" type="file" accept="image/*" class="sr-only" @error('qris') aria-invalid="true" @enderror>

                    {{-- Existing Image Card --}}
                    @if ($account->qrisUrl())
                        <div data-existing-card class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-3.5 sm:p-4 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <img src="{{ $account->qrisUrl() }}" alt="QRIS saat ini" class="size-16 sm:size-18 rounded-xl object-contain bg-white p-1 border border-slate-200 shadow-2xs shrink-0">
                                <div class="min-w-0">
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-daun-dark text-[11px] font-bold border border-emerald-200/60 inline-block">QRIS Aktif</span>
                                    <p class="text-xs font-semibold text-slate-700 mt-1 truncate">QRIS saat ini</p>
                                    <p class="text-[11px] text-slate-400">Klik "Ganti QRIS" untuk langsung memilih gambar pengganti.</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200 justify-end">
                                <button type="button" data-change-btn class="btn btn-sm btn-quiet text-xs font-semibold">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="17 8 12 3 7 8"/>
                                        <line x1="12" y1="3" x2="12" y2="15"/>
                                    </svg>
                                    Ganti QRIS
                                </button>
                                <button type="button" data-delete-btn class="btn btn-sm text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-rose-200 text-xs font-semibold">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <div data-delete-notice class="hidden p-3.5 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <svg class="size-4 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                <span>Gambar QRIS akan dihapus saat disimpan.</span>
                            </div>
                            <button type="button" data-undo-delete-btn class="font-bold text-amber-800 hover:underline cursor-pointer shrink-0">
                                Batalkan Hapus
                            </button>
                        </div>
                    @endif

                    {{-- Live Preview Card --}}
                    <div data-preview-card class="hidden bg-white rounded-2xl border-2 border-emerald-500/40 p-3.5 sm:p-4 shadow-xs">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <img data-preview-img src="" alt="Pratinjau QRIS" class="size-16 sm:size-18 rounded-xl object-contain bg-white p-1 border border-slate-200 shadow-2xs shrink-0">
                                <div class="min-w-0">
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-daun-dark text-[11px] font-bold inline-flex items-center gap-1">
                                        <span class="size-1.5 rounded-full bg-daun animate-pulse"></span>
                                        QRIS Baru Terpilih
                                    </span>
                                    <p data-preview-name class="text-xs font-semibold text-slate-800 mt-1 truncate">filename.jpg</p>
                                    <p data-preview-size class="text-[11px] text-slate-500">120 KB · Siap diunggah</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 justify-end">
                                <button type="button" data-rechoose-btn class="btn btn-sm btn-quiet text-xs font-semibold">Pilih Lain</button>
                                <button type="button" data-cancel-new-btn class="btn btn-sm text-slate-600 hover:bg-slate-100 text-xs font-semibold">Batal</button>
                            </div>
                        </div>
                    </div>

                    {{-- Dropzone --}}
                    <div data-dropzone class="{{ $account->qrisUrl() ? 'hidden' : '' }}">
                        <label for="qris" class="group flex flex-col items-center justify-center p-6 sm:p-8 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/50 hover:border-daun hover:bg-emerald-50/30 transition-all cursor-pointer text-center select-none">
                            <div class="size-12 rounded-2xl bg-white shadow-2xs border border-slate-200/80 text-slate-600 group-hover:bg-emerald-100 group-hover:text-daun group-hover:border-emerald-200 transition-all flex items-center justify-center mb-3">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                    <circle cx="9" cy="9" r="2"/>
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                </svg>
                            </div>
                            <span class="font-bold text-xs sm:text-sm text-slate-700 group-hover:text-daun transition-colors">
                                Ketuk untuk pilih gambar QRIS
                            </span>
                            <span class="text-[11px] text-slate-400 mt-1">atau seret file ke area ini (JPG, PNG, WEBP maks. 3 MB)</span>
                        </label>
                    </div>
                    @error('qris') <span class="field-error">{{ $message }}</span> @enderror

                    @php $qrisPayload = old('qris_payload', $account->qris_payload); @endphp
                    <div data-qris-decoder class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-3.5 space-y-2">
                        <p class="text-sm font-bold text-slate-900">QRIS dinamis + kode unik</p>
                        <p data-qris-status class="text-xs text-slate-500">
                            @if ($account->hasDynamicQris())
                                Aktif. Warga akan mendapat QRIS berisi nominal iuran + kode unik rumahnya.
                            @else
                                Kode QRIS dibaca otomatis dari gambar. Kalau berhasil, warga mendapat QRIS dengan nominal yang sudah terisi.
                            @endif
                        </p>
                        <details @if ($errors->has('qris_payload')) open @endif>
                            <summary class="text-xs font-semibold text-slate-600 cursor-pointer select-none">Lihat / isi kode QRIS manual</summary>
                            <textarea id="qris_payload" name="qris_payload" rows="3" maxlength="512" data-qris-payload spellcheck="false"
                                      class="input mt-2 font-mono text-xs break-all" placeholder="00020101021126..."
                                      @error('qris_payload') aria-invalid="true" @enderror>{{ $qrisPayload }}</textarea>
                            <span class="field-hint">Teks hasil scan QRIS statis (diawali 000201). Kosongkan kalau tidak ingin nominal otomatis.</span>
                        </details>
                        @error('qris_payload') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="w-32">
                    <label for="position" class="field-label">Urutan Tampil</label>
                    <input id="position" name="position" type="number" min="0" max="999" class="input" value="{{ old('position', $account->position) }}">
                </div>

                <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 size-5 accent-emerald-600 rounded" @checked(old('is_active', $account->is_active ?? true))>
                    <div>
                        <span class="font-bold text-slate-900 text-sm block">Tampilkan ke warga</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Matikan kalau rekening ini sudah tidak dipakai. Riwayat bukti yang lama tetap tersimpan.</span>
                    </div>
                </label>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" class="btn btn-primary flex-1 sm:flex-none justify-center" data-busy="Menyimpan...">Simpan Rekening</button>
                    <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-quiet">Batal</a>
                </div>
            </form>
        </div>

        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-sm">
                    <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <span>Panduan Rekening</span>
                </div>
                <ul class="text-xs text-slate-600 space-y-2 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">•</span>
                        <span><strong>E-wallet:</strong> Masukkan nama e-wallet (DANA/OVO/GoPay) dan nomor HP aktif.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">•</span>
                        <span><strong>QRIS:</strong> Unggah gambar kode QRIS resmi kas RT agar warga cukup memindai dari aplikasi m-banking atau e-wallet mereka.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">•</span>
                        <span><strong>Urutan:</strong> Rekening dengan angka urutan terkecil (0, 1, 2) akan muncul paling atas di halaman konfirmasi iuran.</span>
                    </li>
                </ul>
            </div>

            @if ($account->exists)
                <div class="p-5 bg-white rounded-2xl border border-rose-100 shadow-sm space-y-2">
                    <h3 class="font-bold text-slate-900 text-sm">Hapus Rekening</h3>
                    <p class="text-xs text-slate-500">Bukti bayar yang pernah dikirim ke rekening ini tetap tersimpan aman.</p>
                    <div class="pt-2">
                        <form method="POST" action="{{ route('admin.bank-accounts.destroy', $account) }}" data-confirm="Hapus rekening {{ $account->label() }}?">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-600 border border-rose-200 bg-rose-50/50 hover:bg-rose-100/70 transition active:scale-95 w-full sm:w-auto" data-busy="Menghapus...">
                                Hapus Rekening Ini
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
