<x-layouts.admin title="Kelola Pengurus">
    <div class="max-w-2xl">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Kelola Akun Pengurus</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Pengurus yang terdaftar memiliki hak akses untuk mencatat iuran, posting kabar, dan mengelola kas RT.
        </p>

        <!-- List Akun Pengurus -->
        <div class="mt-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
            @foreach ($users as $user)
                <div class="p-4 sm:p-5 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="size-10 rounded-full bg-emerald-100 text-emerald-800 font-bold text-sm flex items-center justify-center shrink-0 border border-emerald-200">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 text-sm truncate flex items-center gap-2">
                                <span>{{ $user->name }}</span>
                                @if ($user->is(auth()->user()))
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Anda</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-500 truncate mt-0.5">{{ $user->email }}</p>
                        </div>
                    </div>

                    @unless ($user->is(auth()->user()))
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Cabut akses pengurus {{ $user->name }}?">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 transition active:scale-95" data-busy="Mencabut...">
                                Cabut Akses
                            </button>
                        </form>
                    @endunless
                </div>
            @endforeach
        </div>

        <!-- Form Tambah Pengurus -->
        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-8 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-7 space-y-5">
            @csrf
            <div class="flex items-center gap-2.5 pb-2 border-b border-slate-100">
                <div class="size-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                </div>
                <h2 class="font-bold text-slate-900 text-base">Tambah Akun Pengurus Baru</h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="field-label">Nama Pengurus</label>
                    <input id="name" name="name" type="text" maxlength="100" class="input font-medium" required value="{{ old('name') }}" placeholder="Mis. Bpk. Agus" @error('name') aria-invalid="true" @enderror>
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="email" class="field-label">Email untuk Masuk</label>
                    <input id="email" name="email" type="email" maxlength="160" class="input" required value="{{ old('email') }}" placeholder="pengurus@warga.id" @error('email') aria-invalid="true" @enderror>
                    @error('email') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="password" class="field-label">Kata Sandi Awal</label>
                <input id="password" name="password" type="text" minlength="8" class="input sm:max-w-xs font-mono text-sm" required autocomplete="off" aria-describedby="pw-hint" placeholder="Min. 8 karakter" @error('password') aria-invalid="true" @enderror>
                <span id="pw-hint" class="field-hint">Berikan sandi awal ini kepada pengurus baru untuk pertama kali login.</span>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary" data-busy="Menyimpan...">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Tambah Pengurus</span>
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
