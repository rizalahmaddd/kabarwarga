<x-layouts.admin :title="$account->exists ? 'Ubah Rekening' : 'Tambah Rekening'">
    <div class="mb-4">
        <a href="{{ route('admin.bank-accounts.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Daftar Rekening
        </a>
    </div>

    <div class="max-w-xl">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">{{ $account->exists ? 'Ubah Rekening' : 'Tambah Rekening Pembayaran' }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Bisa rekening bank, e-wallet (DANA, GoPay, OVO), atau QRIS.</p>

        <form method="POST" enctype="multipart/form-data"
              action="{{ $account->exists ? route('admin.bank-accounts.update', $account) : route('admin.bank-accounts.store') }}"
              class="mt-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-7 space-y-5">
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

            <div>
                <label for="qris" class="field-label">Gambar QRIS <span class="font-normal text-slate-400">(opsional)</span></label>
                @if ($account->qrisUrl())
                    <div class="flex items-center gap-3 mb-2">
                        <img src="{{ $account->qrisUrl() }}" alt="QRIS saat ini" class="size-24 rounded-lg border border-slate-200 object-cover">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="hidden" name="remove_qris" value="0">
                            <input type="checkbox" name="remove_qris" value="1" class="size-4 accent-terakota"> Hapus QRIS
                        </label>
                    </div>
                @endif
                <input id="qris" name="qris" type="file" accept="image/*" class="input py-2 text-sm" @error('qris') aria-invalid="true" @enderror>
                <span class="field-hint">Warga bisa scan atau buka gambar ini dari HP. Maksimal 3 MB.</span>
                @error('qris') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="w-32">
                <label for="position" class="field-label">Urutan</label>
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

        @if ($account->exists)
            <div class="mt-8 p-5 bg-white rounded-2xl border border-rose-100 shadow-sm">
                <h3 class="font-bold text-slate-900 text-sm">Hapus Rekening</h3>
                <p class="text-xs text-slate-500 mt-0.5 mb-3">Bukti bayar yang pernah dikirim ke rekening ini tetap tersimpan.</p>
                <form method="POST" action="{{ route('admin.bank-accounts.destroy', $account) }}" data-confirm="Hapus rekening {{ $account->label() }}?">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-600 border border-rose-200 bg-rose-50/50 hover:bg-rose-100/70 transition active:scale-95" data-busy="Menghapus...">
                        Hapus Rekening Ini
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-layouts.admin>
