<x-layouts.public title="Sesi habis">
    <div class="max-w-xl">
        <h1 class="font-bold text-3xl">Halaman sudah terlalu lama dibuka</h1>
        <p class="mt-2 text-ink-muted">Demi keamanan, isian tadi belum tersimpan. Muat ulang halaman sebelumnya lalu kirim lagi.</p>
        <a href="{{ url()->previous() }}" class="btn btn-primary mt-6">Kembali ke halaman sebelumnya</a>
    </div>
</x-layouts.public>
