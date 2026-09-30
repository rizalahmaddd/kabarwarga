<x-layouts.admin :title="$post->exists ? 'Ubah Kabar' : 'Tulis Kabar'">
    <div class="mb-4">
        <a href="{{ route('admin.posts.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Daftar Kabar
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
            {{ $post->exists ? 'Ubah Kabar' : 'Tulis Kabar Baru' }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Informasi yang diterbitkan akan langsung dapat dibaca oleh warga di halaman Kabar Warga.
        </p>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center gap-2">
            <svg class="size-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span>Mohon periksa isian yang bertanda merah di bawah ini.</span>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
          class="grid gap-8 lg:grid-cols-12 items-start">
        @csrf
        @if ($post->exists) @method('PUT') @endif

        {{-- Left Column: Content Editor & Main Media --}}
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-7 space-y-5">
                <fieldset>
                    <legend class="field-label">Kategori Kabar</legend>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-1">
                        @foreach (\App\Models\Post::CATEGORIES as $key => $label)
                            <label class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer transition hover:bg-slate-100/70 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-900 text-xs sm:text-sm font-medium">
                                <input type="radio" name="category" value="{{ $key }}" class="accent-emerald-600 size-4" @checked(old('category', $post->category) === $key)>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('category') <span class="field-error">{{ $message }}</span> @enderror
                </fieldset>

                <div>
                    <label for="title" class="field-label">Judul Kabar</label>
                    <input id="title" name="title" type="text" maxlength="160" class="input font-medium text-base" required value="{{ old('title', $post->title) }}"
                           placeholder="Mis. Kerja Bakti Bersih Lingkungan & Fogging Nyamuk" @error('title') aria-invalid="true" @enderror>
                    @error('title') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <!-- Field khusus Kegiatan -->
                <div data-show-when="category=kegiatan" class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-100/80 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="event_starts_at" class="field-label text-emerald-950">Waktu & Tanggal Kegiatan</label>
                        <input id="event_starts_at" name="event_starts_at" type="datetime-local" class="input bg-white"
                               value="{{ old('event_starts_at', $post->event_starts_at?->format('Y-m-d\TH:i')) }}" @error('event_starts_at') aria-invalid="true" @enderror>
                        @error('event_starts_at') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="event_location" class="field-label text-emerald-950">Lokasi Kegiatan</label>
                        <input id="event_location" name="event_location" type="text" maxlength="160" class="input bg-white"
                               value="{{ old('event_location', $post->event_location) }}" placeholder="Mis. Balai Warga RT 04">
                    </div>
                </div>

                <div>
                    <label for="body" class="field-label">Isi Pengumuman / Berita</label>
                    <textarea id="body" name="body" rows="12" class="input leading-relaxed text-sm sm:text-base font-normal font-sans" required aria-describedby="body-hint"
                              placeholder="Tuliskan detail pengumuman untuk warga..."
                              @error('body') aria-invalid="true" @enderror>{{ old('body', $post->body) }}</textarea>
                    <span id="body-hint" class="field-hint">Tips: Tekan Enter untuk paragraf baru. Awali baris dengan strip ("- ") untuk membuat poin-poin.</span>
                    @error('body') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Foto Utama Kabar Card --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-7 space-y-4" data-image-uploader data-has-existing="{{ $post->imageUrl() ? 'true' : 'false' }}">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm sm:text-base">
                        Foto / Lampiran Berita <span class="font-normal text-slate-500 text-xs">(Opsional, Maks. 3 MB)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Foto ini akan ditampilkan pada bagian atas artikel kabar warga dan sebagai thumbnail kartu pengumuman di beranda.
                    </p>
                </div>

                <input type="hidden" name="remove_image" data-remove-input value="0">
                <input id="image" name="image" type="file" accept="image/*" class="sr-only" @error('image') aria-invalid="true" @enderror>

                {{-- Existing Image Card --}}
                @if ($post->imageUrl())
                    <div data-existing-card class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-3.5 sm:p-4 shadow-2xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <img src="{{ $post->imageUrl() }}" alt="Foto saat ini" class="size-16 sm:size-18 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0">
                            <div class="min-w-0">
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-daun-dark text-[11px] font-bold border border-emerald-200/60 inline-block">Foto Berita Aktif</span>
                                <p class="text-xs font-semibold text-slate-700 mt-1 truncate">Foto saat ini</p>
                                <p class="text-[11px] text-slate-400">Klik "Ganti Foto" untuk langsung memilih foto pengganti.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200 justify-end">
                            <button type="button" data-change-btn class="btn btn-sm btn-quiet text-xs font-semibold">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                Ganti Foto
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
                            <span>Foto utama akan dihapus saat disimpan.</span>
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
                            <img data-preview-img src="" alt="Pratinjau Foto" class="size-16 sm:size-18 rounded-xl object-cover border border-slate-200 shadow-2xs shrink-0">
                            <div class="min-w-0">
                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-daun-dark text-[11px] font-bold inline-flex items-center gap-1">
                                    <span class="size-1.5 rounded-full bg-daun animate-pulse"></span>
                                    Foto Baru Terpilih
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
                <div data-dropzone class="{{ $post->imageUrl() ? 'hidden' : '' }}">
                    <label for="image" class="group flex flex-col items-center justify-center p-6 sm:p-8 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/50 hover:border-daun hover:bg-emerald-50/30 transition-all cursor-pointer text-center select-none">
                        <div class="size-12 rounded-2xl bg-white shadow-2xs border border-slate-200/80 text-slate-600 group-hover:bg-emerald-100 group-hover:text-daun group-hover:border-emerald-200 transition-all flex items-center justify-center mb-3">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                <circle cx="9" cy="9" r="2"/>
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                            </svg>
                        </div>
                        <span class="font-bold text-xs sm:text-sm text-slate-700 group-hover:text-daun transition-colors">
                            Ketuk untuk pilih foto berita
                        </span>
                        <span class="text-[11px] text-slate-400 mt-1">atau seret file ke area ini (JPG, PNG, WEBP maks. 3 MB)</span>
                    </label>
                </div>
                @error('image') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Right Column: Publish Actions, Pinned & Popup Controls --}}
        <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-20">
            {{-- Publish Action Box --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Status Publikasi</span>
                    @if ($post->exists)
                        @if ($post->isPublished())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                Terbit
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                <span class="size-1.5 rounded-full bg-slate-400"></span>
                                Draf
                            </span>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                            Baru
                        </span>
                    @endif
                </div>

                @if ($post->exists && $post->published_at)
                    <div class="text-xs text-slate-500 flex items-center justify-between">
                        <span>Waktu terbit:</span>
                        <span class="font-medium text-slate-700">{{ $post->published_at->format('d M Y, H:i') }}</span>
                    </div>
                @endif

                <div class="space-y-2.5 pt-1">
                    <button name="action" value="publish" class="btn btn-primary w-full justify-center text-sm py-2.5 font-bold shadow-sm" data-busy="Menyimpan...">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        <span>{{ $post->isPublished() ? 'Simpan Perubahan' : 'Terbitkan Sekarang' }}</span>
                    </button>
                    <button name="action" value="draft" class="btn btn-quiet w-full justify-center text-xs font-semibold py-2" data-busy="Menyimpan...">
                        <span>{{ $post->isPublished() ? 'Tarik Jadi Draf' : 'Simpan Sebagai Draf' }}</span>
                    </button>
                    <div class="text-center pt-1">
                        <a href="{{ route('admin.posts.index') }}" class="text-xs font-medium text-slate-400 hover:text-slate-600 hover:underline">
                            Batalkan perubahan
                        </a>
                    </div>
                </div>
            </div>

            {{-- Display Options & Modal Popup --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 block pb-1 border-b border-slate-100">Opsi Tampilan</span>

                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition">
                    <input type="checkbox" name="is_pinned" value="1" class="mt-0.5 size-4 accent-emerald-600 rounded" @checked(old('is_pinned', $post->is_pinned))>
                    <div>
                        <span class="font-bold text-slate-900 text-xs sm:text-sm block">Sematkan di Beranda</span>
                        <span class="text-[11px] text-slate-500 block mt-0.5 leading-snug">Tetap di posisi paling atas agar langsung dilihat warga.</span>
                    </div>
                </label>

                {{-- Opsi Popup Beranda --}}
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-3.5 space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_popup" id="is_popup_checkbox" value="1" class="mt-0.5 size-4 accent-emerald-600 rounded" @checked(old('is_popup', $post->is_popup))>
                        <div>
                            <span class="font-bold text-slate-900 text-xs sm:text-sm flex items-center gap-1.5">
                                <span>Popup Otomatis Beranda</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Modal</span>
                            </span>
                            <span class="text-[11px] text-slate-500 block mt-0.5 leading-snug">Muncul sebagai popup saat warga membuka web pertama kali.</span>
                        </div>
                    </label>

                    {{-- Popup Upload Section --}}
                    <div id="popup-upload-section" class="pt-3 border-t border-slate-200 space-y-3" data-image-uploader data-has-existing="{{ $post->popup_image_path ? 'true' : 'false' }}">
                        <div>
                            <span class="block font-bold text-slate-800 text-xs">
                                Flyer / Poster Khusus Popup <span class="font-normal text-slate-400">(Opsional)</span>
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                Jika diunggah, popup akan tampil full flyer. Jika kosong, popup memakai teks judul dan ringkasan.
                            </p>
                        </div>

                        <input type="hidden" name="remove_popup_image" data-remove-input value="0">
                        <input id="popup_image" name="popup_image" type="file" accept="image/*" class="sr-only" @error('popup_image') aria-invalid="true" @enderror>

                        {{-- Existing Popup Image Card --}}
                        @if ($post->popup_image_path)
                            <div data-existing-card class="bg-white rounded-xl border border-slate-200 p-3 shadow-2xs flex flex-col gap-2.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="{{ $post->popupImageUrl() }}" alt="Poster Popup" class="size-14 rounded-lg object-cover border border-slate-100 shadow-2xs shrink-0">
                                    <div class="min-w-0">
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-daun-dark text-[10px] font-bold border border-emerald-200/60">Poster Aktif</span>
                                        <p class="text-xs font-semibold text-slate-700 mt-1 truncate">Poster saat ini</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 pt-2 border-t border-slate-100 justify-end">
                                    <button type="button" data-change-btn class="btn btn-sm btn-quiet text-xs font-semibold py-1 px-2.5">Ganti</button>
                                    <button type="button" data-delete-btn class="btn btn-sm text-rose-600 hover:bg-rose-50 border border-rose-200 text-xs font-semibold py-1 px-2.5">Hapus</button>
                                </div>
                            </div>

                            <div data-delete-notice class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs flex items-center justify-between gap-2">
                                <span>Flyer akan dihapus saat simpan.</span>
                                <button type="button" data-undo-delete-btn class="font-bold text-amber-800 hover:underline cursor-pointer shrink-0">
                                    Batal
                                </button>
                            </div>
                        @endif

                        {{-- Live Preview Card --}}
                        <div data-preview-card class="hidden bg-white rounded-xl border-2 border-emerald-500/40 p-3 shadow-xs">
                            <div class="flex flex-col gap-2.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img data-preview-img src="" alt="Pratinjau Baru" class="size-14 rounded-lg object-cover border border-slate-100 shadow-2xs shrink-0">
                                    <div class="min-w-0">
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-daun-dark text-[10px] font-bold inline-flex items-center gap-1">
                                            <span class="size-1.5 rounded-full bg-daun animate-pulse"></span>
                                            Flyer Terpilih
                                        </span>
                                        <p data-preview-name class="text-xs font-semibold text-slate-800 mt-1 truncate">poster.jpg</p>
                                        <p data-preview-size class="text-[10px] text-slate-500">Siap diunggah</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 pt-2 border-t border-slate-100 justify-end">
                                    <button type="button" data-rechoose-btn class="btn btn-sm btn-quiet text-xs font-semibold py-1 px-2.5">Pilih Lain</button>
                                    <button type="button" data-cancel-new-btn class="btn btn-sm text-slate-600 hover:bg-slate-100 text-xs font-semibold py-1 px-2.5">Batal</button>
                                </div>
                            </div>
                        </div>

                        {{-- Dropzone --}}
                        <div data-dropzone class="{{ $post->popup_image_path ? 'hidden' : '' }}">
                            <label for="popup_image" class="group flex flex-col items-center justify-center p-4 rounded-xl border-2 border-dashed border-slate-300 bg-white hover:border-daun hover:bg-emerald-50/30 transition-all cursor-pointer text-center select-none">
                                <div class="size-8 rounded-lg bg-emerald-50 text-daun group-hover:scale-105 transition-all flex items-center justify-center mb-1.5">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                        <circle cx="9" cy="9" r="2"/>
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                </div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-daun transition-colors">
                                    Pilih flyer poster popup
                                </span>
                                <span class="text-[10px] text-slate-400 mt-0.5">JPG, PNG, WEBP (maks. 4 MB)</span>
                            </label>
                        </div>
                        @error('popup_image') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Tips Card --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-3">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-xs uppercase tracking-wider">
                    <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <span>Tips Penulisan</span>
                </div>
                <ul class="text-xs text-slate-600 space-y-2 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">•</span>
                        <span>Buat judul yang jelas, padat, dan menyebutkan inti pengumuman.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">•</span>
                        <span>Jika kegiatan fisik, pilih kategori <strong>Kegiatan</strong> dan isi tanggal serta lokasi berkumpul.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-600 font-bold">•</span>
                        <span>Gunakan tanda strip (<code>-</code>) di awal baris untuk membuat daftar poin agar nyaman dibaca di layar HP warga.</span>
                    </li>
                </ul>
            </div>
        </div>
    </form>
</x-layouts.admin>
