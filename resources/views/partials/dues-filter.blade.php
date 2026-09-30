@php
    $typeOptions = [];
    foreach ($types as $t) {
        $typeOptions[$t->id] = $t->name . ($t->is_active ? '' : ' (selesai)');
    }

    $yearOptions = [];
    if (isset($years)) {
        foreach ($years as $y) {
            $yearOptions[$y] = 'Tahun ' . $y;
        }
    }
@endphp

<form method="GET" class="flex flex-col sm:flex-row sm:items-end gap-3 w-full">
    <div class="flex-1 min-w-0">
        <label for="jenis" class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Iuran</label>
        <x-dropdown name="jenis" :value="$type?->id" :options="$typeOptions" autosubmit />
    </div>

    @if ($type?->isMonthly())
        <div class="w-full sm:w-44 shrink-0">
            <label for="tahun" class="block text-xs font-bold text-slate-700 mb-1.5">Periode Tahun</label>
            <x-dropdown name="tahun" :value="$year" :options="$yearOptions" autosubmit />
        </div>
    @endif

    <noscript>
        <button class="btn btn-quiet text-xs font-bold h-11">Tampilkan</button>
    </noscript>
</form>

