@if ($paginator->hasPages())
    <nav aria-label="Halaman" class="flex flex-wrap items-center gap-3">
        @if ($paginator->onFirstPage())
            <span class="btn btn-quiet opacity-50" aria-disabled="true">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-quiet">Sebelumnya</a>
        @endif
        <span class="text-ink-muted">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-quiet">Berikutnya</a>
        @else
            <span class="btn btn-quiet opacity-50" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif
