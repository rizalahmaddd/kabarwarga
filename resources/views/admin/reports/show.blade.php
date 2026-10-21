<x-layouts.print :title="'Laporan Kas '.$periodLabel" :back="route('admin.dashboard')">
    <form method="GET" class="print:hidden mb-8 flex flex-wrap items-end gap-3 font-sans rounded-xl bg-slate-50 border border-slate-200 p-4">
        <div>
            <label for="tahun" class="field-label text-xs">Tahun</label>
            <select id="tahun" name="tahun" class="input min-h-10 py-1.5 text-sm">
                @foreach ($years as $option)
                    <option value="{{ $option }}" @selected($option === $year)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="bulan" class="field-label text-xs">Periode</label>
            <select id="bulan" name="bulan" class="input min-h-10 py-1.5 text-sm">
                <option value="">Setahun penuh</option>
                @foreach (range(1, 12) as $option)
                    <option value="{{ $option }}" @selected($option === $month)>{{ \Carbon\CarbonImmutable::create($year, $option, 1)->translatedFormat('F') }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-sm btn-quiet">Tampilkan</button>
    </form>

    <header class="text-center border-b-[3px] border-double border-black pb-3">
        <p class="text-lg font-bold uppercase tracking-wide">Laporan Pertanggungjawaban Kas</p>
        <p class="font-bold">{{ setting('site_name') }}</p>
        @if (setting('address'))
            <p class="text-sm">{{ setting('address') }}</p>
        @endif
        <p class="text-sm mt-1">Periode: {{ $from->translatedFormat('j F Y') }} s.d. {{ $until->translatedFormat('j F Y') }}</p>
    </header>

    <h2 class="mt-6 font-bold">A. Ringkasan</h2>
    <table class="mt-2 w-full text-sm border-collapse">
        <tbody>
            <tr class="border-b border-slate-300"><td class="py-1.5">Saldo awal</td><td class="py-1.5 text-right tabular-nums">{{ rupiah($opening) }}</td></tr>
            <tr class="border-b border-slate-300"><td class="py-1.5">Total pemasukan</td><td class="py-1.5 text-right tabular-nums">{{ rupiah($totalIn) }}</td></tr>
            <tr class="border-b border-slate-300"><td class="py-1.5">Total pengeluaran</td><td class="py-1.5 text-right tabular-nums">({{ rupiah($totalOut) }})</td></tr>
            <tr class="font-bold"><td class="py-1.5">Saldo akhir</td><td class="py-1.5 text-right tabular-nums">{{ rupiah($closing) }}</td></tr>
        </tbody>
    </table>

    <h2 class="mt-6 font-bold">B. Pemasukan per jenis iuran</h2>
    <table class="mt-2 w-full text-sm border-collapse">
        <thead>
            <tr class="border-y border-black text-left">
                <th class="py-1.5 font-semibold">Jenis iuran</th>
                <th class="py-1.5 font-semibold text-right w-36">Jumlah catatan</th>
                <th class="py-1.5 font-semibold text-right w-36">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($income as $row)
                <tr class="border-b border-slate-300">
                    <td class="py-1.5">{{ $row['name'] }}</td>
                    <td class="py-1.5 text-right tabular-nums">{{ $row['records'] }}</td>
                    <td class="py-1.5 text-right tabular-nums">{{ rupiah($row['total']) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="py-2 text-slate-600">Tidak ada pemasukan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="mt-6 font-bold">C. Pengeluaran per kategori</h2>
    <table class="mt-2 w-full text-sm border-collapse">
        <thead>
            <tr class="border-y border-black text-left">
                <th class="py-1.5 font-semibold">Kategori</th>
                <th class="py-1.5 font-semibold text-right w-36">Transaksi</th>
                <th class="py-1.5 font-semibold text-right w-36">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($byCategory as $row)
                <tr class="border-b border-slate-300">
                    <td class="py-1.5">{{ $row['label'] }}</td>
                    <td class="py-1.5 text-right tabular-nums">{{ $row['count'] }}</td>
                    <td class="py-1.5 text-right tabular-nums">{{ rupiah($row['total']) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="py-2 text-slate-600">Tidak ada pengeluaran pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($expenses->isNotEmpty())
        <h2 class="mt-6 font-bold">D. Rincian pengeluaran</h2>
        <table class="mt-2 w-full text-sm border-collapse">
            <thead>
                <tr class="border-y border-black text-left">
                    <th class="py-1.5 pr-2 font-semibold">Tanggal</th>
                    <th class="py-1.5 pr-2 font-semibold">Keterangan</th>
                    <th class="py-1.5 pr-2 font-semibold">Nota</th>
                    <th class="py-1.5 font-semibold text-right w-36">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($expenses as $expense)
                    <tr class="border-b border-slate-300 align-top break-inside-avoid">
                        <td class="py-1.5 pr-2 whitespace-nowrap">{{ $expense->spent_on->translatedFormat('j M Y') }}</td>
                        <td class="py-1.5 pr-2">{{ $expense->description }} <span class="text-slate-600">({{ $expense->categoryLabel() }})</span></td>
                        <td class="py-1.5 pr-2">{{ $expense->receipt_path ? 'Ada' : '-' }}</td>
                        <td class="py-1.5 text-right tabular-nums">{{ rupiah($expense->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="mt-12 grid grid-cols-2 gap-8 text-center break-inside-avoid">
        <div>
            <p>Mengetahui,</p>
            <p>Ketua {{ setting('site_name') }}</p>
            <div class="h-20"></div>
            <p class="font-bold underline underline-offset-4">{{ setting('chairman_name') ?: '(.............................)' }}</p>
        </div>
        <div>
            <p>{{ now()->translatedFormat('j F Y') }}</p>
            <p>Bendahara</p>
            <div class="h-20"></div>
            <p class="font-bold underline underline-offset-4">{{ setting('treasurer_name') ?: '(.............................)' }}</p>
        </div>
    </div>
</x-layouts.print>
