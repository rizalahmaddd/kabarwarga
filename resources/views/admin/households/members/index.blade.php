<x-layouts.admin :title="'Penghuni Rumah '.$household->number">
    <div class="mb-6">
        <a href="{{ route('admin.households.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700 transition mb-3">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke Daftar Rumah
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center justify-center font-extrabold text-sm px-2.5 py-0.5 rounded-lg bg-slate-900 text-white">
                        {{ $household->number }}
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                        Data Penghuni & Kartu Keluarga
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm text-slate-600">
                    <span class="font-bold text-slate-800">{{ $household->head_name }}</span>
                    <span>·</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded {{ ($household->occupancy_status ?? 'pemilik') === 'kontrak' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                        Status: {{ $household->occupancyLabel() }}
                    </span>
                    <span>·</span>
                    <span class="font-mono bg-slate-200/70 text-slate-700 px-2 py-0.5 rounded text-xs font-semibold">
                        {{ $household->kk_number ? 'No. KK: '.$household->kk_number : 'Nomor KK belum didata' }}
                    </span>
                    <span>·</span>
                    <span class="text-indigo-700 font-semibold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200/60 text-xs">
                        {{ $household->members->count() }} Anggota Terdata
                    </span>
                    @if ($household->kk_image_path)
                        <span>·</span>
                        <a href="{{ Storage::url($household->kk_image_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-daun-dark font-semibold hover:underline">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                            Arsip Foto KK Tersimpan
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" id="btn-open-ocr" class="btn btn-sm btn-primary text-xs font-bold flex items-center gap-1.5 shadow-xs">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7V4h3"/><path d="M20 7V4h-3"/><path d="M4 17v3h3"/><path d="M20 17v3h-3"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>Upload KK & Scan OCR</span>
                </button>
                <button type="button" id="btn-open-manual" class="btn btn-sm btn-quiet text-xs font-bold flex items-center gap-1.5">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>+ Tambah Manual</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Review & Scan Section (Accordion / Overlay Mode) --}}
    <div id="ocr-review-container" class="hidden mb-8 rounded-2xl border-2 border-indigo-400 bg-white shadow-xl overflow-hidden transition-all duration-300">
        {{-- Header Bar --}}
        <div class="bg-indigo-900 text-white px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-white/15 text-white">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7V4h3"/><path d="M20 7V4h-3"/><path d="M4 17v3h3"/><path d="M20 17v3h-3"/>
                        <path d="M9 12h6"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-base tracking-tight text-white">Scan Kartu Keluarga (OCR Lokal) & Review</h3>
                    <p class="text-xs text-indigo-100 font-medium mt-0.5">100% diproses di browser Anda. Unggah dokumen, sistem akan membaca teks, dan Anda tinggal review sebelum simpan.</p>
                </div>
            </div>
            <button type="button" id="btn-close-ocr" class="p-1.5 rounded-lg text-white hover:bg-white/20 transition" aria-label="Tutup panel">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="p-5 sm:p-6 space-y-6">
            {{-- Upload & Progress Dropzone --}}
            <div id="ocr-upload-zone" class="space-y-4">
                <div class="border-2 border-dashed border-indigo-200 rounded-2xl p-6 sm:p-8 text-center bg-indigo-50/40 hover:bg-indigo-50/70 transition cursor-pointer relative" id="kk-dropzone">
                    <input type="file" id="kk-file-input" accept="image/jpeg,image/png,image/webp" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10">
                    <div class="flex flex-col items-center pointer-events-none">
                        <div class="size-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 mb-3 shadow-2xs">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">Pilih atau Tarik File Foto Kartu Keluarga (KK) ke sini</h4>
                        <p class="text-xs text-slate-600 font-medium mt-1">Mendukung format JPG, PNG, atau WebP (Maks. 5 MB). Foto yang tegak dan jelas memberikan hasil bacaan paling akurat.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="btn btn-sm btn-primary text-xs pointer-events-none">Pilih Dokumen Foto KK</span>
                            <button type="button" id="btn-paste-raw" class="btn btn-sm btn-quiet text-xs font-bold pointer-events-auto">Atau Tempel Teks Mentah</button>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar Container --}}
                <div id="ocr-progress-container" class="hidden p-4 rounded-xl bg-slate-900 text-white space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span id="ocr-status-text" class="flex items-center gap-2 text-white">
                            <svg class="size-4 animate-spin text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Mempersiapkan engine OCR lokal di browser...
                        </span>
                        <span id="ocr-percentage" class="font-mono text-emerald-400 font-extrabold text-sm">0%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2.5 overflow-hidden">
                        <div id="ocr-progress-bar" class="bg-emerald-500 h-2.5 rounded-full transition-all duration-300 w-0"></div>
                    </div>
                </div>

                {{-- Paste Text Input (Collapsible) --}}
                <div id="paste-text-container" class="hidden p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                    <label for="raw-ocr-text" class="field-label text-xs font-bold text-slate-800">Tempel Hasil Teks Dokumen Kartu Keluarga:</label>
                    <textarea id="raw-ocr-text" rows="5" class="input font-mono text-xs font-medium text-slate-900" placeholder="KARTU KELUARGA&#10;No. 3201...&#10;Nama Kepala Keluarga: ..."></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" id="btn-cancel-paste" class="btn btn-xs btn-quiet font-bold">Batal</button>
                        <button type="button" id="btn-process-raw" class="btn btn-xs btn-primary font-bold">Proses & Ekstrak Data</button>
                    </div>
                </div>
            </div>

            {{-- Split Review Screen: KK Preview on Left, Form Review on Right --}}
            <form id="form-sync-members" method="POST" action="{{ route('admin.households.members.sync', $household) }}" enctype="multipart/form-data" class="hidden space-y-6">
                @csrf
                <input type="file" name="kk_image" id="hidden-kk-file" class="hidden">

                {{-- Header Fields of Kartu Keluarga --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 grid gap-4 sm:grid-cols-4 items-end">
                    <div>
                        <label for="review_kk_number" class="field-label text-xs font-bold text-slate-800">Nomor Kartu Keluarga (KK)</label>
                        <input type="text" id="review_kk_number" name="kk_number" maxlength="30" class="input font-mono font-bold text-sm text-slate-900" placeholder="16 digit nomor KK">
                    </div>
                    <div>
                        <label for="review_occupancy_status" class="field-label text-xs font-bold text-slate-800">Status Warga / Hunian</label>
                        <select id="review_occupancy_status" name="occupancy_status" class="input font-bold text-xs text-slate-900">
                            <option value="pemilik" @selected(($household->occupancy_status ?? 'pemilik') === 'pemilik')>Pemilik Rumah</option>
                            <option value="kontrak" @selected(($household->occupancy_status ?? 'pemilik') === 'kontrak')>Kontrak / Sewa</option>
                        </select>
                    </div>
                    <div>
                        <label for="review_head_name" class="field-label text-xs font-bold text-slate-800">Kepala Keluarga Terdeteksi</label>
                        <input type="text" id="review_head_name" readonly class="input bg-slate-100 font-bold text-slate-900 text-sm">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-200 bg-white cursor-pointer text-xs font-bold text-slate-900">
                            <input type="checkbox" name="sync_head_name" value="1" checked class="size-4 accent-emerald-600 rounded">
                            <span>Perbarui Kepala Rumah</span>
                        </label>
                    </div>
                </div>

                {{-- Side-by-Side Review Section --}}
                <div class="grid gap-6 lg:grid-cols-12 items-start">
                    {{-- Left: KK Image Zoom/Preview & Controls --}}
                    <div class="lg:col-span-4 bg-slate-900 rounded-2xl p-3 text-white space-y-2">
                        <div class="flex items-center justify-between px-1 text-xs">
                            <span class="font-bold flex items-center gap-1.5 text-white">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                Pratinjau KK
                            </span>
                            <div class="flex items-center gap-1">
                                <button type="button" id="btn-rotate-left" class="size-6 rounded bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center font-bold text-xs" title="Putar 90° Kiri (Berlawanan Jarum Jam)">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                                </button>
                                <button type="button" id="btn-rotate-right" class="size-6 rounded bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center font-bold text-xs" title="Putar 90° Kanan (Searah Jarum Jam)">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 15 6-6m0 0-6-6m6 6H9a6 6 0 0 0 0 12h3" /></svg>
                                </button>
                                <span class="w-px h-3.5 bg-slate-700 mx-0.5"></span>
                                <button type="button" id="btn-zoom-out" class="size-6 rounded bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center font-bold text-xs" title="Perkecil">-</button>
                                <button type="button" id="btn-zoom-reset" class="px-1.5 h-6 rounded bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-bold" title="Reset">100%</button>
                                <button type="button" id="btn-zoom-in" class="size-6 rounded bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center font-bold text-xs" title="Perbesar">+</button>
                            </div>
                        </div>

                        <div class="relative overflow-auto max-h-[500px] rounded-xl bg-black/40 flex items-center justify-center min-h-[220px] p-2" id="image-scroll-box">
                            <img id="review-image-preview" src="" alt="Pratinjau Kartu Keluarga" class="max-w-none transition-transform duration-200 origin-top-left hidden">
                            <div id="review-image-placeholder" class="text-xs text-slate-200 font-medium text-center py-8">
                                Belum ada foto yang dipilih.
                            </div>
                        </div>

                        <button type="button" id="btn-rescan-rotated" class="hidden w-full py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                            <span>Scan Ulang Setelah Putar Foto</span>
                        </button>

                        <p class="text-[11px] text-slate-300 font-medium px-1 italic">Tips: Jika foto dari HP miring/menyamping, klik tombol putar lalu tekan "Scan Ulang".</p>
                    </div>

                    {{-- Right: Editable Table of Members --}}
                    <div class="lg:col-span-8 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Review & Koreksi Daftar Anggota Keluarga</h4>
                                <p class="text-xs text-slate-600 font-medium">Periksa hasil pembacaan OCR di bawah. Anda dapat langsung mengedit teks yang salah terbaca sebelum disimpan.</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" id="btn-toggle-raw-text" class="btn btn-xs btn-quiet font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300">
                                    Lihat / Edit Teks Mentah
                                </button>
                                <button type="button" id="btn-add-review-row" class="btn btn-xs btn-quiet font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200">
                                    + Tambah Baris
                                </button>
                            </div>
                        </div>

                        {{-- Collapsible Raw Text Editor in Review Section --}}
                        <div id="review-raw-text-panel" class="hidden p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/50 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-indigo-950">
                                <span>Teks Dokumen Hasil Pembacaan OCR (Bisa diedit jika ada typo):</span>
                                <span class="text-[11px] text-slate-500 font-normal">Edit teks lalu klik "Ekstrak Ulang"</span>
                            </div>
                            <textarea id="review-raw-ocr-textarea" rows="6" class="input font-mono text-xs font-medium text-slate-900 bg-white" placeholder="Teks mentah hasil OCR akan tampil di sini..."></textarea>
                            <div class="flex justify-end gap-2">
                                <button type="button" id="btn-close-raw-panel" class="btn btn-xs btn-quiet font-bold">Tutup</button>
                                <button type="button" id="btn-reextract-raw" class="btn btn-xs btn-primary font-bold">🔄 Ekstrak Ulang Data Dari Teks Ini</button>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-2xs">
                            <table class="w-full text-left text-xs" id="review-table">
                                <thead class="bg-slate-100 text-slate-900 font-extrabold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                                    <tr>
                                        <th class="p-2.5 w-8 text-center text-slate-900 font-extrabold">#</th>
                                        <th class="p-2.5 min-w-[140px] text-slate-900 font-extrabold">NIK (16 Digit)</th>
                                        <th class="p-2.5 min-w-[180px] text-slate-900 font-extrabold">Nama Lengkap *</th>
                                        <th class="p-2.5 min-w-[140px] text-slate-900 font-extrabold">Hubungan</th>
                                        <th class="p-2.5 min-w-[95px] text-slate-900 font-extrabold">Status</th>
                                        <th class="p-2.5 min-w-[70px] text-slate-900 font-extrabold">L/P</th>
                                        <th class="p-2.5 min-w-[120px] text-slate-900 font-extrabold">Tempat Lahir</th>
                                        <th class="p-2.5 min-w-[130px] text-slate-900 font-extrabold">Tgl Lahir</th>
                                        <th class="p-2.5 min-w-[100px] text-slate-900 font-extrabold">Agama</th>
                                        <th class="p-2.5 min-w-[120px] text-slate-900 font-extrabold">Pekerjaan</th>
                                        <th class="p-2.5 min-w-[110px] text-slate-900 font-extrabold">Status Nikah</th>
                                        <th class="p-2.5 min-w-[110px] text-slate-900 font-extrabold">No HP</th>
                                        <th class="p-2.5 w-10 text-center text-slate-900 font-extrabold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="review-table-body" class="divide-y divide-slate-200 bg-white">
                                    {{-- Dynamically filled via JS --}}
                                </tbody>
                            </table>
                        </div>

                        <div class="p-4 bg-emerald-50 border border-emerald-300 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="text-xs">
                                <span class="font-bold text-sm text-emerald-950 block">Siap disimpan ke database?</span>
                                <span class="text-emerald-900 font-medium">Menyimpan akan memperbarui daftar penghuni untuk Rumah {{ $household->number }}.</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" id="btn-cancel-review" class="btn btn-sm btn-quiet text-xs font-bold">Batal</button>
                                <button type="submit" id="btn-submit-sync" class="btn btn-sm btn-primary text-xs font-bold flex items-center gap-1.5">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    <span>Simpan & Terapkan Data</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Content: Existing Registered Household Members Table --}}
    <div class="sheet overflow-hidden shadow-xs">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Daftar Penghuni Saat Ini</h3>
                <p class="text-xs text-slate-600 font-medium mt-0.5">Daftar warga dan anggota keluarga yang terdaftar menghuni Rumah {{ $household->number }}.</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($household->members->isNotEmpty())
                    <button type="button" id="btn-edit-all-table" class="btn btn-xs btn-quiet text-xs font-bold">
                        Edit Mode Tabel Lengkap
                    </button>
                @endif
            </div>
        </div>

        @if ($household->members->isEmpty())
            <div class="p-12 text-center">
                <div class="size-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h4 class="font-bold text-slate-900 text-sm">Belum ada data penghuni terdaftar untuk rumah ini</h4>
                <p class="text-xs text-slate-600 font-medium mt-1 max-w-sm mx-auto">
                    Anda bisa mengunggah foto Kartu Keluarga untuk diekstrak otomatis via OCR, atau memasukkan anggota keluarga satu per satu secara manual.
                </p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <button type="button" onclick="document.getElementById('btn-open-ocr').click()" class="btn btn-sm btn-primary text-xs font-bold">
                        Upload Scan KK (OCR)
                    </button>
                    <button type="button" onclick="document.getElementById('btn-open-manual').click()" class="btn btn-sm btn-quiet text-xs font-bold">
                        Input Manual
                    </button>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-100 text-slate-900 font-extrabold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center text-slate-900 font-extrabold">#</th>
                            <th class="py-3 px-4 text-slate-900 font-extrabold">Nama Lengkap</th>
                            <th class="py-3 px-4 text-slate-900 font-extrabold">NIK</th>
                            <th class="py-3 px-4 text-slate-900 font-extrabold">Hubungan</th>
                            <th class="py-3 px-4 text-slate-900 font-extrabold">L/P</th>
                            <th class="py-3 px-4 text-slate-900 font-extrabold">Lahir / Usia</th>
                            <th class="py-3 px-4 text-slate-900 font-extrabold">Pekerjaan & Agama</th>
                            <th class="py-3 px-4 text-slate-900 font-extrabold">No. HP</th>
                            <th class="py-3 px-4 text-right text-slate-900 font-extrabold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($household->members as $index => $member)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 text-center font-bold text-slate-600">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-sm text-slate-900">{{ $member->name }}</div>
                                    <div class="text-[11px] text-slate-600 font-medium">{{ $member->marital_status ?: '-' }}</div>
                                </td>
                                <td class="py-3 px-4 font-mono font-medium text-slate-900">
                                    {{ $member->nik ?: '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $relationClass = match($member->family_relation) {
                                            'Kepala Keluarga' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
                                            'Istri' => 'bg-rose-100 text-rose-900 border-rose-300',
                                            'Anak' => 'bg-sky-100 text-sky-900 border-sky-300',
                                            'Orang Tua', 'Mertua' => 'bg-amber-100 text-amber-900 border-amber-300',
                                            default => 'bg-slate-100 text-slate-800 border-slate-300'
                                        };
                                        $occStatus = $member->occupancy_status ?? $household->occupancy_status ?? 'pemilik';
                                    @endphp
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-extrabold border {{ $relationClass }}">
                                            {{ $member->family_relation ?: 'Anggota' }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-extrabold {{ $occStatus === 'kontrak' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-100 text-blue-900 border border-blue-300' }}">
                                            {{ $occStatus === 'kontrak' ? 'Kontrak' : 'Pemilik' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    {{ $member->gender === 'L' ? 'Laki-laki' : ($member->gender === 'P' ? 'Perempuan' : '-') }}
                                </td>
                                <td class="py-3 px-4 text-slate-700">
                                    @if ($member->birth_date)
                                        <div class="font-semibold text-slate-900">{{ $member->birth_date->format('d/m/Y') }}</div>
                                        <div class="text-[11px] text-slate-600 font-medium">
                                            {{ $member->birth_place ? $member->birth_place . ', ' : '' }}
                                            {{ $member->birth_date->age }} th
                                        </div>
                                    @else
                                        <span class="font-medium text-slate-800">{{ $member->birth_place ?: '-' }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-700">
                                    <div class="font-bold text-slate-900">{{ $member->job ?: '-' }}</div>
                                    <div class="text-[11px] text-slate-600 font-medium">{{ $member->religion ?: '-' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($member->phone)
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $member->phone);
                                            if (str_starts_with($cleanPhone, '0')) {
                                                $cleanPhone = '62'.substr($cleanPhone, 1);
                                            }
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-emerald-800 font-bold hover:underline">
                                            {{ $member->phone }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 font-medium">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                class="btn btn-xs btn-quiet text-xs font-bold text-slate-800 hover:text-slate-900 btn-edit-single"
                                                data-member="{{ json_encode($member) }}"
                                                data-update-url="{{ route('admin.households.members.update', [$household, $member]) }}">
                                            Ubah
                                        </button>
                                        <form method="POST" action="{{ route('admin.households.members.destroy', [$household, $member]) }}" data-confirm="Hapus {{ $member->name }} dari daftar penghuni?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-quiet text-xs font-bold text-rose-600 hover:bg-rose-50 hover:text-rose-700">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Single Member Modal (Tambah / Edit Manual) --}}
    <div id="single-member-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 id="modal-title" class="font-extrabold text-base text-slate-900">Tambah Anggota Keluarga</h3>
                <button type="button" id="btn-close-modal" class="text-slate-500 hover:text-slate-900 p-1 rounded-lg">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="single-member-form" method="POST" action="{{ route('admin.households.members.store', $household) }}" class="p-5 space-y-4">
                @csrf
                <div id="form-method-field"></div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="m_nik" class="field-label text-xs font-bold text-slate-800">NIK (16 Digit)</label>
                        <input id="m_nik" name="nik" type="text" maxlength="20" class="input font-mono text-xs font-semibold text-slate-900" placeholder="3201...">
                    </div>
                    <div>
                        <label for="m_name" class="field-label text-xs font-bold text-slate-800">Nama Lengkap *</label>
                        <input id="m_name" name="name" type="text" required maxlength="100" class="input text-xs font-bold text-slate-900" placeholder="Nama warga">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div>
                        <label for="m_relation" class="field-label text-xs font-bold text-slate-800">Hubungan Keluarga</label>
                        <select id="m_relation" name="family_relation" class="input text-xs font-semibold text-slate-900">
                            @foreach (\App\Models\HouseholdMember::RELATIONS as $rel)
                                <option value="{{ $rel }}">{{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="m_occupancy_status" class="field-label text-xs font-bold text-slate-800">Status Warga</label>
                        <select id="m_occupancy_status" name="occupancy_status" class="input text-xs font-bold text-slate-900">
                            <option value="pemilik">Pemilik Rumah</option>
                            <option value="kontrak">Kontrak / Sewa</option>
                        </select>
                    </div>
                    <div>
                        <label for="m_gender" class="field-label text-xs font-bold text-slate-800">Jenis Kelamin</label>
                        <select id="m_gender" name="gender" class="input text-xs font-semibold text-slate-900">
                            <option value="L">Laki-laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="m_birth_place" class="field-label text-xs font-bold text-slate-800">Tempat Lahir</label>
                        <input id="m_birth_place" name="birth_place" type="text" class="input text-xs font-medium text-slate-900" placeholder="Kota lahir">
                    </div>
                    <div>
                        <label for="m_birth_date" class="field-label text-xs font-bold text-slate-800">Tanggal Lahir</label>
                        <input id="m_birth_date" name="birth_date" type="date" class="input text-xs font-semibold text-slate-900">
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="m_religion" class="field-label text-xs font-bold text-slate-800">Agama</label>
                        <select id="m_religion" name="religion" class="input text-xs font-semibold text-slate-900">
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Budha">Budha</option>
                            <option value="Konghucu">Konghucu</option>
                        </select>
                    </div>
                    <div>
                        <label for="m_marital_status" class="field-label text-xs font-bold text-slate-800">Status Perkawinan</label>
                        <select id="m_marital_status" name="marital_status" class="input text-xs font-semibold text-slate-900">
                            <option value="Kawin">Kawin</option>
                            <option value="Belum Kawin">Belum Kawin</option>
                            <option value="Cerai Hidup">Cerai Hidup</option>
                            <option value="Cerai Mati">Cerai Mati</option>
                        </select>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="m_job" class="field-label text-xs font-bold text-slate-800">Pekerjaan</label>
                        <input id="m_job" name="job" type="text" class="input text-xs font-medium text-slate-900" placeholder="Karyawan / Wiraswasta">
                    </div>
                    <div>
                        <label for="m_phone" class="field-label text-xs font-bold text-slate-800">Nomor HP / WhatsApp</label>
                        <input id="m_phone" name="phone" type="tel" class="input text-xs font-medium text-slate-900" placeholder="08...">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" id="btn-cancel-modal" class="btn btn-sm btn-quiet text-xs font-bold">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary text-xs font-bold" data-busy="Menyimpan...">Simpan Penghuni</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi & Pratinjau Foto KK Sebelum Scan --}}
    <div id="modal-confirm-pick" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-xs hidden">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-2xl w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
            <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-white/10 text-white">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 7V4h3"/><path d="M20 7V4h-3"/><path d="M4 17v3h3"/><path d="M20 17v3h-3"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-white">Periksa & Sesuaikan Orientasi Foto KK</h3>
                        <p class="text-xs text-slate-300 font-medium mt-0.5">Pastikan foto tegak (tidak miring/terbalik) agar sistem OCR membaca teks dengan benar.</p>
                    </div>
                </div>
                <button type="button" id="btn-cancel-pick-modal-x" class="text-slate-300 hover:text-white p-1 rounded-lg hover:bg-white/10 transition" aria-label="Tutup">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-4 sm:p-5 overflow-y-auto space-y-4">
                {{-- Controls bar --}}
                <div class="flex flex-wrap items-center justify-between gap-2 p-2.5 bg-slate-100 rounded-xl text-xs">
                    <div class="flex items-center gap-1.5 font-bold text-slate-700">
                        <span>Orientasi Saat Ini:</span>
                        <span id="pick-rotation-badge" class="px-2 py-0.5 rounded bg-white text-indigo-700 border border-slate-200 font-mono font-bold">0° (Tegak Normal)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" id="btn-modal-rotate-left" class="btn btn-xs btn-quiet font-bold text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 flex items-center gap-1" title="Putar 90° Kiri">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                            <span>Putar Kiri</span>
                        </button>
                        <button type="button" id="btn-modal-rotate-right" class="btn btn-xs btn-quiet font-bold text-slate-800 bg-white hover:bg-slate-50 border border-slate-200 flex items-center gap-1" title="Putar 90° Kanan">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 15 6-6m0 0-6-6m6 6H9a6 6 0 0 0 0 12h3" /></svg>
                            <span>Putar Kanan</span>
                        </button>
                    </div>
                </div>

                {{-- Interactive Preview Frame --}}
                <div class="relative overflow-auto max-h-[420px] min-h-[260px] rounded-xl bg-slate-950 flex items-center justify-center p-3 border border-slate-200">
                    <img id="pick-modal-preview-img" src="" alt="Foto Kartu Keluarga" class="max-h-[380px] w-auto object-contain transition-transform duration-200">
                </div>

                <div class="p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl text-xs text-indigo-950 flex items-start gap-2.5">
                    <svg class="size-4 text-indigo-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                    <span><strong>Tips OCR:</strong> Pastikan judul "KARTU KELUARGA" berada di bagian atas dan nomor NIK terbaca horizontal dari kiri ke kanan. Gambar yang tegak menjamin tingkat akurasi pembacaan maksimal.</span>
                </div>
            </div>

            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3 shrink-0">
                <button type="button" id="btn-cancel-pick-modal" class="btn btn-sm btn-quiet text-xs font-bold text-slate-700">
                    Batal / Ganti Foto
                </button>
                <button type="button" id="btn-confirm-start-ocr" class="btn btn-sm btn-primary text-xs font-extrabold flex items-center gap-2 shadow-sm">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h3"/><path d="M20 7V4h-3"/><path d="M4 17v3h3"/><path d="M20 17v3h-3"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
                    <span>Mulai Scan Dokumen Ini (Teks Sudah Tegak)</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Pass existing members data to JS for instant table edit if needed --}}
    <script>
        window.HOUSEHOLD_NUMBER = @json($household->number);
        window.EXISTING_MEMBERS = @json($household->members);
        window.EXISTING_KK_NUMBER = @json($household->kk_number);
        window.EXISTING_HEAD_NAME = @json($household->head_name);
        window.EXISTING_OCCUPANCY_STATUS = @json($household->occupancy_status ?? 'pemilik');
    </script>
</x-layouts.admin>
