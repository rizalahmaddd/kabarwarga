@props(['title', 'back' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} · {{ setting('site_name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { size: A4; margin: 1.6cm 1.8cm; }
        @media print {
            html, body { background: #fff !important; }
            .print-sheet { box-shadow: none !important; border: 0 !important; padding: 0 !important; margin: 0 !important; max-width: none !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-slate-100 text-black antialiased">
    <div class="print:hidden sticky top-0 z-10 bg-white/95 border-b border-slate-200">
        <div class="max-w-[21cm] mx-auto px-4 py-3 flex items-center justify-between gap-3">
            @if ($back)
                <a href="{{ $back }}" class="btn btn-sm btn-quiet text-xs">← Kembali</a>
            @else
                <span></span>
            @endif
            <button type="button" onclick="window.print()" class="btn btn-sm btn-primary text-xs">Cetak / Simpan PDF</button>
        </div>
    </div>

    <main class="print-sheet max-w-[21cm] mx-auto my-6 bg-white shadow-sm border border-slate-200 px-6 py-8 sm:px-12 sm:py-12 font-serif text-[15px] leading-relaxed">
        {{ $slot }}
    </main>
</body>
</html>
