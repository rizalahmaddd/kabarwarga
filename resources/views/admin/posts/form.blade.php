<x-layouts.admin :title="$post->exists ? 'Ubah Kabar' : 'Tulis Kabar'">
    <div class="mb-4">
        <a href="{{ route('admin.posts.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Daftar Kabar
        </a>
    </div>

    <div class="max-w-3xl">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
            {{ $post->exists ? 'Ubah Kabar' : 'Tulis Kabar Baru' }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Informasi yang diterbitkan akan langsung dapat dibaca oleh warga di halaman Kabar Warga.
        </p>

        @if ($errors->any())
            <div role="alert" class="mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <svg class="size-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>Mohon periksa isian yang bertanda merah di bawah ini.</span>
            </div>
        @endif

        <form method="POST" enctype="multipart/form-data"
              action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}"
              class="mt-6 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-7 space-y-5">
            @csrf
            @if ($post->exists) @method('PUT') @endif

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
                <input id="title" name="title" type="text" maxlength="160" class="input font-medium" required value="{{ old('title', $post->title) }}"
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
                <textarea id="body" name="body" rows="9" class="input leading-relaxed text-sm sm:text-base font-normal" required aria-describedby="body-hint"
                          placeholder="Tuliskan detail pengumuman untuk warga..."
                          @error('body') aria-invalid="true" @enderror>{{ old('body', $post->body) }}</textarea>
                <span id="body-hint" class="field-hint">Tips: Tekan Enter untuk paragraf baru. Awali baris dengan strip ("- ") untuk membuat poin-poin.</span>
                @error('body') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80">
                <label for="image" class="field-label mb-1">Foto / Lampiran Gambar <span class="font-normal text-slate-500">(Maks. 3 MB)</span></label>
                @if ($post->imageUrl())
                    <div class="mb-3 flex items-center gap-3 p-2 bg-white rounded-lg border border-slate-200">
                        <img src="{{ $post->imageUrl() }}" alt="Foto saat ini" class="size-16 rounded-md object-cover border border-slate-200">
                        <label class="flex items-center gap-2 text-xs font-semibold text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_image" value="1" class="size-4 accent-rose-600 rounded">
                            Hapus foto saat ini
                        </label>
                    </div>
                @endif
                <input id="image" name="image" type="file" accept="image/*" class="input py-2 text-xs sm:text-sm bg-white" @error('image') aria-invalid="true" @enderror>
                @error('image') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-3">
                <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 cursor-pointer transition">
                    <input type="checkbox" name="is_pinned" value="1" class="mt-0.5 size-5 accent-emerald-600 rounded" @checked(old('is_pinned', $post->is_pinned))>
                    <div>
                        <span class="font-bold text-slate-900 text-sm block">Sematkan di Beranda Paling Atas</span>
                        <span class="text-xs text-slate-500 block mt-0.5">Kabar ini akan terus berada di urutan teratas agar tidak terlewat warga sampai status semat dimatikan.</span>
                    </div>
                </label>

                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 space-y-3">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="is_popup" id="is_popup_checkbox" value="1" class="mt-0.5 size-5 accent-emerald-600 rounded" @checked(old('is_popup', $post->is_popup))>
                        <div>
                            <span class="font-bold text-slate-900 text-sm block flex items-center gap-1.5">
                                <span>Jadikan Jendela Popup di Beranda</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Popup</span>
                            </span>
                            <span class="text-xs text-slate-500 block mt-0.5">Ketika warga pertama kali membuka website, pengumuman ini langsung muncul otomatis dalam jendela popup.</span>
                        </div>
                    </label>

                    <div class="pt-3 border-t border-slate-200/60 pl-8 space-y-2">
                        <label for="popup_image" class="block font-bold text-slate-800 text-xs">
                            Upload Foto / Flyer Khusus Popup (Opsional)
                        </label>
                        <p class="text-[11px] text-slate-500 leading-normal">
                            Jika diunggah, popup akan <strong>tampil full foto/poster saja</strong> dan ketika diklik langsung membuka halaman detail pengumuman. Jika dikosongkan, popup akan menampilkan kartu ringkasan standar.
                        </p>

                        @if ($post->popup_image_path)
                            <div class="flex items-center gap-3 p-2 bg-white rounded-xl border border-slate-200 max-w-md">
                                <img src="{{ $post->popupImageUrl() }}" alt="Poster Popup" class="h-16 w-16 object-cover rounded-lg border border-slate-100">
                                <div class="min-w-0 flex-1">
                                    <span class="text-xs font-semibold text-slate-800 block truncate">Foto popup aktif</span>
                                    <label class="mt-1 text-xs text-rose-600 font-semibold inline-flex items-center gap-1.5 cursor-pointer hover:underline">
                                        <input type="checkbox" name="remove_popup_image" value="1" class="size-4 accent-rose-600 rounded">
                                        <span>Hapus foto popup saat ini</span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <input id="popup_image" name="popup_image" type="file" accept="image/*" class="input py-2 text-xs bg-white" @error('popup_image') aria-invalid="true" @enderror>
                        @error('popup_image') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 pt-3 border-t border-slate-100">
                <button name="action" value="publish" class="btn btn-primary justify-center text-sm py-2.5" data-busy="Menyimpan...">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    <span>{{ $post->isPublished() ? 'Simpan Perubahan' : 'Terbitkan Sekarang' }}</span>
                </button>
                <button name="action" value="draft" class="btn btn-quiet justify-center text-sm py-2.5" data-busy="Menyimpan...">
                    <span>{{ $post->isPublished() ? 'Tarik Jadi Draf' : 'Simpan Sebagai Draf' }}</span>
                </button>
                <a href="{{ route('admin.posts.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-semibold text-slate-500 hover:text-slate-700">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-layouts.admin>
