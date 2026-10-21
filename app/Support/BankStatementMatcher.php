<?php

namespace App\Support;

use App\Models\Household;
use App\Models\PaymentSubmission;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BankStatementMatcher
{
    /**
     * Pulls incoming amounts and a date out of each statement line. Works on CSV exports and text copied from
     * internet banking alike, since every bank formats them differently.
     *
     * @return Collection<int, array{line: string, date: string|null, amounts: array<int, int>}>
     */
    public function parse(string $statement): Collection
    {
        return collect(preg_split('/\R/', $statement))
            ->map(fn (string $line) => trim($line))
            ->filter(fn (string $line) => $line !== '' && ! preg_match('/\b(DB|DEBET|DEBIT)\b/i', $line))
            ->map(fn (string $line) => [
                'line' => mb_strimwidth($line, 0, 200, '…'),
                'date' => $this->dateIn($line),
                'amounts' => $this->amountsIn($line),
            ])
            ->filter(fn (array $row) => $row['amounts'] !== [])
            ->values();
    }

    /**
     * @return array{matches: Collection<int, array{submission: PaymentSubmission, status: string, row: array{line: string, date: string|null, amounts: array<int, int>}|null}>, unmatched: Collection<int, array{row: array{line: string, date: string|null, amounts: array<int, int>}, households: Collection<int, Household>}>}
     */
    public function match(string $statement): array
    {
        $rows = $this->parse($statement);
        $pending = PaymentSubmission::pending()->with(['household', 'duesType', 'bankAccount'])->oldest()->get();
        $sameAmount = $pending->countBy(fn (PaymentSubmission $submission) => $submission->transferTotal());
        $used = [];

        $matches = $pending->map(function (PaymentSubmission $submission) use ($rows, $sameAmount, &$used) {
            $total = $submission->transferTotal();
            $hits = $rows->filter(fn (array $row) => in_array($total, $row['amounts'], true));

            // Two pending transfers of the same amount, or one amount on two lines, need a human to pick.
            $status = match (true) {
                $hits->isEmpty() => 'missing',
                $sameAmount[$total] > 1 || $hits->count() > 1 => 'ambiguous',
                default => 'matched',
            };
            foreach ($hits->keys() as $index) {
                $used[$index] = true;
            }

            return ['submission' => $submission, 'status' => $status, 'row' => $hits->first()];
        });

        $households = Household::active()->get();
        $unmatched = $rows
            ->reject(fn (array $row, int $index) => isset($used[$index]))
            ->map(fn (array $row) => [
                'row' => $row,
                'households' => $households->filter(fn (Household $household) => collect($row['amounts'])->contains(
                    fn (int $amount) => $amount % 1000 !== 0 && $amount % 1000 === $household->uniqueCode()
                ))->values(),
            ])
            ->values();

        return ['matches' => $matches, 'unmatched' => $unmatched];
    }

    /**
     * @return array<int, int>
     */
    private function amountsIn(string $line): array
    {
        preg_match_all('/(?<![\d.,])(\d{1,3}(?:[.,]\d{3})+|\d{4,})([.,]\d{2})?(?![\d.,])/', $line, $found, PREG_SET_ORDER);

        return collect($found)
            ->map(fn (array $match) => (int) str_replace(['.', ','], '', $match[1]))
            ->filter(fn (int $amount) => $amount >= 1000 && $amount <= 100_000_000)
            ->unique()
            ->values()
            ->all();
    }

    private function dateIn(string $line): ?string
    {
        if (preg_match('/\b(\d{4})-(\d{2})-(\d{2})\b/', $line, $m)) {
            [$year, $month, $day] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } elseif (preg_match('/\b(\d{1,2})[\/-](\d{1,2})(?:[\/-](\d{2,4}))?\b/', $line, $m)) {
            [$day, $month] = [(int) $m[1], (int) $m[2]];
            $year = isset($m[3]) ? (int) $m[3] : (int) now()->year;
            $year += $year < 100 ? 2000 : 0;
        } else {
            return null;
        }

        if (! checkdate($month, $day, $year)) {
            return null;
        }

        $date = CarbonImmutable::create($year, $month, $day);

        return $date->isFuture() ? null : $date->toDateString();
    }
}
