<x-layouts.public title="Status Pembayaran #{{ $submission->code }}">
    <div class="max-w-xl mx-auto">
        <div class="sheet p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Kode kiriman</p>
                    <p class="font-mono text-2xl font-extrabold tracking-widest text-slate-900">{{ $submission->code }}</p>
                </div>
                @include('public.pay.status-badge')
            </div>

            @if ($submission->status === \App\Models\PaymentSubmission::PENDING)
                <div class="mt-4 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
                    <p class="font-bold">Menunggu dicek pengurus</p>
                    <p class="mt-1">Simpan halaman ini atau catat kodenya untuk mengecek status nanti. Status juga tampil di halaman Bayar Iuran saat Anda memilih rumah yang sama.</p>
                </div>
            @elseif ($submission->isPartial())
                <div class="mt-4 rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-950">
                    <p class="font-bold">Pembayaran diterima sebagian</p>
                    <p class="mt-1">Tercatat lunas: <strong>{{ $submission->periodsLabel($submission->acceptedPeriods()) }}</strong> ({{ rupiah($submission->acceptedTotal()) }}).</p>
                    <p class="mt-1">Belum diterima: <strong>{{ $submission->periodsLabel($submission->declinedPeriods()) }}</strong>.</p>
                    @if ($submission->reject_reason)
                        <p class="mt-1">Keterangan pengurus: <strong>{{ $submission->reject_reason }}</strong></p>
                    @endif
                    <a href="{{ route('pay.create', ['rumah' => $submission->household_id, 'jenis' => $submission->dues_type_id]) }}" class="btn btn-sm btn-primary mt-3">Bayar bulan yang belum</a>
                </div>
            @elseif ($submission->status === \App\Models\PaymentSubmission::APPROVED)
                <div class="mt-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-900">
                    <p class="font-bold">✓ Pembayaran diterima dan sudah tercatat lunas</p>
                    <p class="mt-1">Dikonfirmasi {{ $submission->reviewed_at?->translatedFormat('j F Y, H:i') }}. Terima kasih!</p>
                </div>
            @else
                <div class="mt-4 rounded-xl bg-terakota-soft/60 border border-terakota/30 p-4 text-sm text-slate-900">
                    <p class="font-bold text-terakota">Pembayaran belum bisa diterima</p>
                    @if ($submission->reject_reason)
                        <p class="mt-1">Alasan dari pengurus: <strong>{{ $submission->reject_reason }}</strong></p>
                    @endif
                    <a href="{{ route('pay.create', ['rumah' => $submission->household_id, 'jenis' => $submission->dues_type_id]) }}" class="btn btn-sm btn-primary mt-3">Kirim ulang bukti</a>
                </div>
            @endif

            <dl class="mt-5 divide-y divide-slate-100 text-sm">
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Rumah</dt>
                    <dd class="font-bold text-slate-900 text-right">{{ $submission->household->label() }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Iuran</dt>
                    <dd class="font-bold text-slate-900 text-right">{{ $submission->duesType->name }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Periode</dt>
                    <dd class="font-bold text-slate-900 text-right">{{ $submission->periodsLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Jumlah</dt>
                    <dd class="font-extrabold text-slate-900 text-right tabular-nums">{{ rupiah($submission->total()) }}</dd>
                </div>
                @if ($submission->bankAccount)
                    <div class="flex justify-between gap-4 py-2.5">
                        <dt class="text-slate-500">Ke rekening</dt>
                        <dd class="font-bold text-slate-900 text-right">{{ $submission->bankAccount->label() }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 py-2.5">
                    <dt class="text-slate-500">Dikirim</dt>
                    <dd class="font-bold text-slate-900 text-right">{{ $submission->created_at->translatedFormat('j F Y, H:i') }}</dd>
                </div>
            </dl>

            <div class="mt-5 flex flex-col sm:flex-row gap-2">
                <a href="{{ route('dues.index', ['jenis' => $submission->dues_type_id]) }}" class="btn btn-quiet flex-1">Lihat Cek Iuran</a>
                <a href="{{ route('pay.create', ['rumah' => $submission->household_id]) }}" class="btn btn-quiet flex-1">Bayar iuran lain</a>
            </div>
        </div>
    </div>
</x-layouts.public>
