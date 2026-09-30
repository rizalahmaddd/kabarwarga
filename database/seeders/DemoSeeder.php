<?php

namespace Database\Seeders;

use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Household;
use App\Models\Payment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Obviously-fake sample data for local development only. Never run this in production.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);
        $admin = User::first();

        foreach (range(1, 12) as $i) {
            Household::firstOrCreate(['number' => 'A-'.$i], ['head_name' => "Contoh Warga {$i}"]);
        }

        $monthly = DuesType::firstOrCreate(['name' => 'Iuran kebersihan (contoh)'], [
            'amount' => 20000, 'frequency' => DuesType::MONTHLY, 'starts_on' => now()->startOfYear(),
        ]);
        DuesType::firstOrCreate(['name' => 'Iuran acara (contoh)'], [
            'amount' => 50000, 'frequency' => DuesType::ONCE, 'due_on' => now()->addMonth(),
        ]);

        foreach (Household::take(7)->get() as $h) {
            foreach (range(1, now()->month) as $m) {
                if ($m > now()->month - ($h->id % 3)) {
                    continue;
                }
                Payment::firstOrCreate(
                    ['household_id' => $h->id, 'dues_type_id' => $monthly->id, 'period' => now()->format('Y').'-'.str_pad($m, 2, '0', STR_PAD_LEFT)],
                    ['amount' => 20000, 'paid_on' => now()->startOfYear()->addMonths($m - 1)->addDays(4), 'recorded_by' => $admin->id],
                );
            }
        }

        Expense::firstOrCreate(['description' => 'Contoh pengeluaran: kantong sampah'], [
            'spent_on' => now()->subDays(10), 'amount' => 45000, 'recorded_by' => $admin->id,
        ]);

        $posts = [
            ['pengumuman', 'Contoh pengumuman yang dipasang', true, null],
            ['kegiatan', 'Contoh kegiatan kerja bakti', false, now()->addDays(5)->setTime(7, 0)],
            ['berita', 'Contoh berita warga', false, null],
            ['iuran', 'Contoh info iuran bulan ini', false, null],
        ];
        foreach ($posts as $i => [$category, $title, $pinned, $eventAt]) {
            Post::firstOrCreate(['slug' => \Illuminate\Support\Str::slug($title)], [
                'title' => $title,
                'category' => $category,
                'body' => "Ini isi contoh untuk mencoba tampilan.\n\nParagraf kedua, masih contoh.\n\n- butir satu\n- butir dua",
                'is_pinned' => $pinned,
                'event_starts_at' => $eventAt,
                'event_location' => $eventAt ? 'Contoh tempat' : null,
                'published_at' => now()->subDays($i),
                'author_id' => $admin->id,
            ]);
        }
    }
}
