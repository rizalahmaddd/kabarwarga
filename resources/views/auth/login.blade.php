<x-layouts.public title="Masuk Pengurus">
    <div class="max-w-md mx-auto py-6 sm:py-10">
        <div class="text-center mb-6">
            <div class="size-14 rounded-2xl bg-gradient-to-br from-daun to-emerald-700 text-white flex items-center justify-center mx-auto shadow-md mb-3">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <h1 class="font-extrabold text-2xl sm:text-3xl text-slate-900 tracking-tight">Masuk Khusus Pengurus</h1>
            <p class="mt-1.5 text-xs sm:text-sm text-slate-500 max-w-sm mx-auto">
                Khusus pengurus yang bertugas mencatat iuran dan memasang kabar. <strong>Warga umum tidak perlu masuk</strong>.
            </p>
        </div>

        <div class="sheet p-6 sm:p-8 bg-white shadow-md">
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="field-label">Email Pengurus</label>
                    <div class="relative">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="input pl-10" required autofocus autocomplete="username"
                               placeholder="email@rt-rw.id" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                        </div>
                    </div>
                    @error('email') <span id="email-error" class="field-error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="field-label">Kata Sandi</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" class="input pl-10" required autocomplete="current-password"
                               placeholder="••••••••" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                    </div>
                    @error('password') <span id="password-error" class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="pt-1">
                    <label class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" class="size-4.5 rounded border-slate-300 accent-daun">
                        Ingat saya di perangkat HP ini
                    </label>
                </div>

                <button class="btn btn-primary w-full min-h-12 text-sm font-bold shadow-sm mt-2" data-busy="Memeriksa...">
                    Masuk ke Panel Pengurus
                </button>
            </form>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-xs font-bold text-slate-500 hover:text-daun no-underline inline-flex items-center gap-1">
                ← Kembali ke Halaman Utama Warga
            </a>
        </div>
    </div>
</x-layouts.public>

