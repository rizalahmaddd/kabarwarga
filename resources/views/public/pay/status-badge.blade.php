@php
    $badge = $submission->isPartial()
        ? ['badge-warning', 'Sebagian diterima']
        : [
            \App\Models\PaymentSubmission::PENDING => ['badge-warning', 'Dicek'],
            \App\Models\PaymentSubmission::APPROVED => ['badge-success', '✓ Diterima'],
            \App\Models\PaymentSubmission::REJECTED => ['badge-danger', 'Ditolak'],
        ][$submission->status];
@endphp
<span class="badge {{ $badge[0] }} shrink-0">{{ $badge[1] }}</span>
