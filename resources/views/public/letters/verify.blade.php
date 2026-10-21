<x-layouts.public title="Cek Keaslian Surat">
    <div class="max-w-xl mx-auto">
        <div class="sheet p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="stamp text-daun-dark border-daun-dark -rotate-2">Asli</span>
                <h1 class="font-bold text-xl text-slate-900">Surat ini terdaftar</h1>
            </div>
            <p class="mt-2 text-sm text-slate-600">
                Diterbitkan oleh pengurus {{ setting('site_name') }}. Cocokkan data di bawah dengan surat yang Anda pegang.
            </p>

            <dl class="mt-5 divide-y divide-slate-100 text-sm">
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Jenis surat</dt>
                    <dd class="font-bold text-slate-900 text-right">{{ $letter->typeTitle() }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Nomor</dt>
                    <dd class="font-bold text-slate-900 text-right">{{ $letter->number }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Atas nama</dt>
                    <dd class="font-bold text-slate-900 text-right">{{ $letter->person['name'] }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">NIK</dt>
                    <dd class="font-mono text-slate-900 text-right">{{ $letter->maskedNik() }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Tanggal terbit</dt>
                    <dd class="font-bold text-slate-900 text-right">{{ $letter->issued_on->translatedFormat('j F Y') }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Ditandatangani</dt>
                    <dd class="font-bold text-slate-900 text-right">{{ setting('chairman_name') ?: 'Ketua '.setting('site_name') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-layouts.public>
