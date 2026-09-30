@props(['title'])
<div {{ $attributes->class('rounded-md border-2 border-dashed border-rule px-5 py-8 text-center') }}>
    <p class="font-serif font-bold text-lg">{{ $title }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-1 text-ink-muted">{{ $slot }}</div>
    @endif
</div>
