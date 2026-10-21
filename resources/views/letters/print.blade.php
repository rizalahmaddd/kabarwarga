@php
    $person = $letter->person;
    $address = trim("Rumah {$letter->household->number}".(setting('address') ? ', '.setting('address') : ''));
    $rows = [
        'Nama' => $person['name'] ?? '-',
        'NIK' => $person['nik'] ?: '-',
        'Tempat, tanggal lahir' => trim(($person['birth_place'] ?? '').(! empty($person['birth_date']) ? ', '.\Carbon\CarbonImmutable::parse($person['birth_date'])->translatedFormat('j F Y') : '')) ?: '-',
        'Jenis kelamin' => \App\Models\HouseholdMember::GENDERS[$person['gender'] ?? ''] ?? '-',
        'Agama' => $person['religion'] ?: '-',
        'Status perkawinan' => $person['marital_status'] ?: '-',
        'Pekerjaan' => $person['job'] ?: '-',
        'Alamat' => $address,
    ];
@endphp
<x-layouts.print :title="$letter->typeTitle().' '.$letter->number" :back="url()->previous() !== url()->current() ? url()->previous() : null">
    <header class="text-center border-b-[3px] border-double border-black pb-3">
        <p class="text-lg font-bold uppercase tracking-wide">Pengurus {{ setting('site_name') }}</p>
        @if (setting('address'))
            <p class="text-sm">{{ setting('address') }}</p>
        @endif
    </header>

    <div class="text-center mt-6">
        <p class="font-bold uppercase underline underline-offset-4">{{ $letter->typeTitle() }}</p>
        <p class="text-sm">Nomor: {{ $letter->number }}</p>
    </div>

    <p class="mt-6">Yang bertanda tangan di bawah ini, Ketua {{ setting('site_name') }}, menerangkan bahwa:</p>

    <table class="mt-3 ml-4 sm:ml-8">
        @foreach ($rows as $label => $value)
            <tr class="align-top">
                <td class="pr-3 py-0.5 whitespace-nowrap">{{ $label }}</td>
                <td class="pr-2 py-0.5">:</td>
                <td class="py-0.5">{{ $value }}</td>
            </tr>
        @endforeach
    </table>

    <p class="mt-4 text-justify">adalah {{ $letter->statement() }}</p>
    <p class="mt-3 text-justify">Surat ini diberikan untuk keperluan: <b>{{ $letter->purpose }}</b>.</p>
    <p class="mt-3 text-justify">Demikian surat ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

    <div class="mt-10 flex items-end justify-between gap-6">
        <div class="text-center text-[11px] leading-snug text-slate-700 font-sans">
            <canvas data-qr="{{ route('letters.verify', $letter->code) }}" class="size-24 mx-auto" aria-label="QR verifikasi surat"></canvas>
            <p class="mt-1">Pindai untuk cek keaslian<br>Kode {{ $letter->code }}</p>
        </div>
        <div class="text-center min-w-[14rem]">
            <p>{{ $letter->issued_on->translatedFormat('j F Y') }}</p>
            <p>Ketua {{ setting('site_name') }}</p>
            <div class="h-20"></div>
            <p class="font-bold underline underline-offset-4">{{ setting('chairman_name') ?: '(.............................)' }}</p>
        </div>
    </div>
</x-layouts.print>
