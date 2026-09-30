<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Support\Collection;

class HomeFeed
{
    public function __construct(private DuesLedger $ledger) {}

    /**
     * @return array{popupPost: Post|null, pinned: Collection, upcoming: Collection, latest: Collection, progress: Collection}
     */
    public function build(): array
    {
        $pinned = Post::published()->where('is_pinned', true)->latest('published_at')->get();

        $upcoming = Post::published()
            ->where('category', 'kegiatan')
            ->where('event_starts_at', '>=', now()->startOfDay())
            ->orderBy('event_starts_at')
            ->take(3)
            ->get();

        $latest = Post::published()
            ->whereNotIn('id', $pinned->pluck('id')->merge($upcoming->pluck('id')))
            ->latest('published_at')
            ->take(6)
            ->get();

        $popupPost = Post::published()
            ->where('is_popup', true)
            ->latest('published_at')
            ->first();

        return [
            'popupPost' => $popupPost,
            'pinned' => $pinned,
            'upcoming' => $upcoming,
            'latest' => $latest,
            'progress' => $this->ledger->currentProgress(),
        ];
    }
}
