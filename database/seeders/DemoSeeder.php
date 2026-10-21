<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\DuesType;
use App\Models\Expense;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Letter;
use App\Models\Payment;
use App\Models\PaymentSubmission;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Realistic-looking sample data for a small RT, for local development and demos only.
 * Names, phone numbers and bank accounts are made up. Never run this in production.
 */
class DemoSeeder extends Seeder
{
    private const HOUSEHOLDS = [
        'A-1' => ['Bambang Sutrisno', null],
        'A-2' => ['Budi Santoso', null],
        'A-3' => ['Hj. Siti Aminah', 'Tinggal bersama anak, tagihan diurus menantu (Pak Rudi).'],
        'A-4' => ['Agus Priyanto', null],
        'A-5' => ['Dedi Kurniawan', null],
        'A-6' => ['Yohanes Hartono', null],
        'A-7' => ['Rudi Hermawan', null],
        'A-8' => ['Wahyu Nugroho', null],
        'A-9' => ['Ir. Heru Prasetyo', 'Ketua RW 11.'],
        'A-10' => ['Fajar Ramadhan', 'Rumah kontrakan, kontrak sampai Juni tahun depan.'],
        'A-11' => ['Andreas Wijaya', null],
        'A-12' => ['Eko Susanto', null],
        'B-1' => ['Hendra Gunawan', null],
        'B-2' => ['Sugeng Riyadi', null],
        'B-3' => ['Agus Setiawan', null],
        'B-4' => ['Nur Hidayat', 'Istri (Ibu Sri Wahyuni) bendahara RT.'],
        'B-5' => ['Joko Purnomo', null],
        'B-6' => ['Endang Lestari', null],
        'B-7' => ['Drs. Suryanto', 'Ketua RT 05.'],
        'B-8' => ['Arif Rahman Hakim', null],
        'B-9' => ['Taufik Hidayatullah', null],
        'B-10' => ['dr. Rina Kartika', 'Buka praktik dokter umum sore hari.'],
        'B-11' => ["Imam Syafi'i", null],
        'B-12' => ['Gregorius Adi Nugraha', null],
        'C-1' => ['Hari Wibowo', null],
        'C-2' => ['Kurniawan Saputra', null],
        'C-3' => ['Yusuf Maulana', null],
        'C-4' => ['Ahmad Fauzi', null],
        'C-5' => ['Samsul Arifin', null],
        'C-6' => ['Dwi Rahayu', null],
        'C-7' => ['Rizky Pratama', null],
        'C-8' => ['Tri Wahyudi', null],
        'C-9' => ['Lukas Setiadi', null],
        'C-10' => ['Muhammad Iqbal', null],
        'C-11' => ['Anton Sujarwo', 'Rumah kosong, pemilik pindah ke Sidoarjo. Iuran dihentikan sementara.'],
        'C-12' => ['Wawan Kustiawan', 'Rumah dijual, penghuni lama sudah pindah.'],
    ];

    private const INACTIVE = ['C-11', 'C-12'];

    private const FEMALE_HEADS = ['A-3', 'B-6', 'B-10', 'C-6'];

    private const RELIGIONS = ['A-6' => 'Kristen', 'A-11' => 'Kristen', 'B-12' => 'Katolik', 'C-9' => 'Katolik'];

    // Left without KK data so the portal's "belum didata" state shows up too.
    private const NO_KK = ['A-10', 'C-3', 'C-10'];

    private const PHONE_PREFIXES = ['0812', '0813', '0821', '0822', '0852', '0857', '0878', '0895', '0838', '0819'];

    private CarbonImmutable $today;

    private User $admin;

    private User $treasurer;

    public function run(): void
    {
        mt_srand(20260930);

        $this->call(DatabaseSeeder::class);

        if (Payment::exists()) {
            $this->command?->warn('Payments already exist, skipping demo data. Use `php artisan migrate:fresh --seeder=DemoSeeder` for a clean demo.');

            return;
        }

        $this->today = CarbonImmutable::today();
        $this->admin = User::orderBy('id')->first();
        $this->treasurer = User::firstOrCreate(
            ['email' => 'bendahara@example.com'],
            ['name' => 'Sri Wahyuni', 'password' => 'password'],
        );

        $this->seedSettings();
        $banks = $this->seedBankAccounts();
        $households = $this->seedHouseholds();

        $start = $this->today->startOfMonth()->subMonths(14);
        $security = DuesType::firstOrCreate(['name' => 'Iuran Keamanan & Kebersihan'], [
            'amount' => 75000,
            'frequency' => DuesType::MONTHLY,
            'starts_on' => $start,
            'description' => 'Untuk honor satpam malam, petugas angkut sampah, dan listrik lampu jalan. Dibayar paling lambat tanggal 10 setiap bulan.',
        ]);
        $cash = DuesType::firstOrCreate(['name' => 'Kas RT'], [
            'amount' => 10000,
            'frequency' => DuesType::MONTHLY,
            'starts_on' => $this->today->startOfYear(),
            'description' => 'Dana cadangan RT untuk santunan warga sakit/berduka dan keperluan mendadak. Disepakati di rapat warga awal tahun.',
        ]);

        $augustYear = $this->today->month > 8 || ($this->today->month === 8 && $this->today->day >= 20)
            ? $this->today->year
            : $this->today->year - 1;
        $independence = DuesType::firstOrCreate(['name' => 'Iuran HUT RI ke-'.($augustYear - 1945)], [
            'amount' => 100000,
            'frequency' => DuesType::ONCE,
            'due_on' => CarbonImmutable::create($augustYear, 8, 10),
            'description' => 'Hadiah lomba anak & dewasa, konsumsi malam tirakatan, bendera dan umbul-umbul.',
        ]);
        $guardPost = DuesType::firstOrCreate(['name' => 'Sumbangan Renovasi Pos Ronda'], [
            'amount' => 150000,
            'frequency' => DuesType::ONCE,
            'due_on' => $this->today->addMonthNoOverflow()->endOfMonth(),
            'description' => 'Ganti atap seng yang bocor, keramik lantai, dan pengecatan ulang. Kekurangan dana ditutup dari Kas RT.',
        ]);

        $this->seedMonthlyPayments($households, $security, $cash, $banks);
        $this->seedOncePayments($households, $independence, CarbonImmutable::create($augustYear, 7, 20), 0.86);
        $this->seedOncePayments($households, $guardPost, $this->today->subDays(10), 0.25);
        $this->seedPendingSubmissions($households, $security, $guardPost, $banks);
        $this->seedExpenses($start, $augustYear);
        $this->seedPosts($households, $augustYear, $guardPost);
        $this->seedMembers($households);
        $this->seedLetters($households);
        $this->writeStatement($households);
    }

    private function seedSettings(): void
    {
        Setting::write([
            'site_name' => 'Kabar Warga RT 05',
            'site_tagline' => 'RT 05 / RW 11 Perumahan Griya Tlogomas Asri',
            'address' => 'Jl. Flamboyan Raya, Kel. Tlogomas, Kec. Lowokwaru, Kota Malang 65144',
            'treasurer_contact' => 'Ibu Sri Wahyuni (081334567812)',
            'chairman_name' => 'Drs. Suryanto',
            'treasurer_name' => 'Sri Wahyuni',
            'letter_code' => 'RT.05/RW.11',
            'payment_info' => "Transfer ke rekening Kas RT di bawah, lalu unggah bukti lewat halaman ini.\nBisa juga bayar tunai ke Ibu Sri (B-4) atau saat penagihan keliling tiap tanggal 5.",
        ]);
    }

    /**
     * @return array<int, BankAccount>
     */
    private function seedBankAccounts(): array
    {
        return [
            BankAccount::firstOrCreate(['bank_name' => 'BRI'], [
                'account_number' => '0457-01-018834-50-2',
                'account_name' => 'Kas RT 05 RW 11 Griya Tlogomas',
                'position' => 1,
            ]),
            BankAccount::firstOrCreate(['bank_name' => 'BSI'], [
                'account_number' => '7219834406',
                'account_name' => 'Sri Wahyuni (Bendahara RT 05)',
                'position' => 2,
            ]),
        ];
    }

    /**
     * @return array<string, array{model: Household, lag: int, transfer: bool}>
     */
    private function seedHouseholds(): array
    {
        $result = [];

        foreach (self::HOUSEHOLDS as $number => [$name, $note]) {
            $household = Household::firstOrCreate(['number' => $number], [
                'head_name' => $name,
                'occupancy_status' => str_contains(strtolower($note ?? ''), 'kontrak') ? 'kontrak' : 'pemilik',
                'phone' => mt_rand(1, 100) <= 85 ? $this->phone() : null,
                'is_active' => ! in_array($number, self::INACTIVE, true),
                'note' => $note,
            ]);

            // lag = months behind today; negative means paid ahead.
            $roll = mt_rand(1, 100);
            $lag = match (true) {
                $roll <= 52 => 0,
                $roll <= 62 => -mt_rand(1, 3),
                $roll <= 78 => 1,
                $roll <= 91 => mt_rand(2, 3),
                default => mt_rand(5, 8),
            };

            $result[$number] = [
                'model' => $household,
                'lag' => $lag,
                'transfer' => mt_rand(1, 100) <= 45,
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, array{model: Household, lag: int, transfer: bool}>  $households
     * @param  array<int, BankAccount>  $banks
     */
    private function seedMonthlyPayments(array $households, DuesType $security, DuesType $cash, array $banks): void
    {
        $months = [];
        for ($m = $security->starts_on; $m->lte($this->today->startOfMonth()->addMonths(3)); $m = $m->addMonth()) {
            $months[] = $m;
        }
        $currentIdx = array_search($this->today->format('Y-m'), array_map(fn ($m) => $m->format('Y-m'), $months), true);

        foreach ($households as $info) {
            if (! $info['model']->is_active) {
                continue;
            }

            $lastPaid = $currentIdx - $info['lag'];
            $habit = $info['lag'] > 0 ? [2, 3] : (mt_rand(1, 100) <= 20 ? [3, 3] : [1, 1]);

            $i = 0;
            while ($i <= $lastPaid) {
                $size = min(mt_rand(...$habit), $lastPaid - $i + 1);
                $batch = array_slice($months, $i, $size);
                $i += $size;

                $last = end($batch);
                $paidOn = $info['lag'] > 0
                    ? $last->addMonth()->addDays(mt_rand(0, 20))
                    : $batch[0]->addDays(mt_rand(0, 9));
                if ($paidOn->gt($this->today)) {
                    $paidOn = $this->today->subDays(mt_rand(0, min(4, $this->today->day - 1)));
                }

                $transfer = $info['transfer'] && mt_rand(1, 100) <= 85;
                $online = $transfer && $paidOn->gte($this->today->subMonths(4)) && mt_rand(1, 100) <= 70;
                $recorder = mt_rand(1, 100) <= 80 ? $this->treasurer : $this->admin;

                foreach ([$security, $cash] as $type) {
                    $periods = collect($batch)->filter(fn ($m) => $type->appliesToMonth($m))->map(fn ($m) => $m->format('Y-m'))->values()->all();
                    if ($periods === []) {
                        continue;
                    }

                    $submission = $online
                        ? $this->submission($info['model'], $type, $periods, $banks[mt_rand(0, 1)], PaymentSubmission::APPROVED, $paidOn->setTime(mt_rand(6, 20), mt_rand(0, 59)), $recorder)
                        : null;

                    foreach ($periods as $period) {
                        Payment::firstOrCreate(
                            ['household_id' => $info['model']->id, 'dues_type_id' => $type->id, 'period' => $period],
                            [
                                'amount' => $type->amount,
                                'paid_on' => $paidOn,
                                'method' => $transfer ? 'transfer' : 'tunai',
                                'note' => $submission ? "Bukti online #{$submission->code}" : ($size > 1 && $period === $periods[0] ? "Bayar {$size} bulan sekaligus" : null),
                                'recorded_by' => $recorder->id,
                                'payment_submission_id' => $submission?->id,
                            ],
                        );
                    }
                }
            }
        }
    }

    /**
     * @param  array<string, array{model: Household, lag: int, transfer: bool}>  $households
     */
    private function seedOncePayments(array $households, DuesType $type, CarbonImmutable $opensOn, float $ratio): void
    {
        foreach ($households as $info) {
            if (! $info['model']->is_active || $info['lag'] >= 5 || mt_rand(1, 100) > $ratio * 100) {
                continue;
            }

            $latest = $type->due_on->addDays(10)->min($this->today);
            $paidOn = $opensOn->addDays(mt_rand(0, max(0, (int) $opensOn->diffInDays($latest))));

            Payment::firstOrCreate(
                ['household_id' => $info['model']->id, 'dues_type_id' => $type->id, 'period' => ''],
                [
                    'amount' => $type->amount,
                    'paid_on' => $paidOn,
                    'method' => $info['transfer'] ? 'transfer' : 'tunai',
                    'note' => mt_rand(1, 100) <= 15 ? 'Titip lewat Pak RT' : null,
                    'recorded_by' => $this->treasurer->id,
                ],
            );
        }
    }

    /**
     * @param  array<string, array{model: Household, lag: int, transfer: bool}>  $households
     * @param  array<int, BankAccount>  $banks
     */
    private function seedPendingSubmissions(array $households, DuesType $security, DuesType $guardPost, array $banks): void
    {
        $late = collect($households)->filter(fn ($h) => $h['model']->is_active && $h['lag'] > 0 && $h['lag'] < 5)->values();

        foreach ($late->take(3) as $n => $info) {
            $periods = collect(range($info['lag'] - 1, 0))
                ->map(fn ($back) => $this->today->startOfMonth()->subMonths($back)->format('Y-m'))
                ->all();

            $this->submission($info['model'], $security, $periods, $banks[$n % 2], PaymentSubmission::PENDING, $this->today->subDays($n)->setTime(mt_rand(7, 21), mt_rand(0, 59))->min(now()));
        }

        $rejected = $late->get(3);
        if ($rejected) {
            $this->submission(
                $rejected['model'], $security, [$this->today->format('Y-m')], $banks[0], PaymentSubmission::REJECTED,
                $this->today->subDays(6)->setTime(20, 14), $this->treasurer,
                'Nominal di bukti transfer Rp 50.000, kurang dari iuran Rp 75.000. Mohon transfer kekurangannya lalu unggah ulang ya, Pak.',
            );
        }

        $unpaid = collect($households)->first(fn ($h) => $h['model']->is_active && $h['lag'] <= 0
            && ! Payment::where('household_id', $h['model']->id)->where('dues_type_id', $guardPost->id)->exists());
        if ($unpaid) {
            $this->submission($unpaid['model'], $guardPost, [''], $banks[1], PaymentSubmission::PENDING, $this->today->setTime(6, 48)->min(now()));
        }
    }

    /**
     * @param  array<int, string>  $periods
     */
    private function submission(Household $household, DuesType $type, array $periods, BankAccount $bank, string $status, CarbonImmutable $submittedAt, ?User $reviewer = null, ?string $rejectReason = null): PaymentSubmission
    {
        $code = $this->code();
        $amount = $type->amount * count($periods) + $household->uniqueCode();
        $submission = new PaymentSubmission([
            'code' => $code,
            'household_id' => $household->id,
            'dues_type_id' => $type->id,
            'periods' => $periods,
            'approved_periods' => $status === PaymentSubmission::APPROVED ? $periods : null,
            'unit_amount' => $type->amount,
            'unique_code' => $household->uniqueCode(),
            'bank_account_id' => $bank->id,
            'payer_name' => mt_rand(1, 100) <= 70 ? $household->head_name : null,
            'phone' => $household->phone,
            'note' => mt_rand(1, 100) <= 25 ? collect(['Transfer dari rekening istri', 'Maaf telat, Bu', 'Sekalian bulan depan ya'])->get(mt_rand(0, 2)) : null,
            'proof_path' => $this->proofImage($code, $bank, $status === PaymentSubmission::REJECTED ? 50000 : $amount, $submittedAt, $household->head_name),
            'status' => $status,
            'reject_reason' => $rejectReason,
            'reviewed_by' => $reviewer?->id,
            'reviewed_at' => $reviewer ? $submittedAt->addMinutes(mt_rand(20, 180)) : null,
        ]);
        $submission->created_at = $submittedAt;
        $submission->updated_at = $submission->reviewed_at ?? $submittedAt;
        $submission->save();

        return $submission;
    }

    private function seedExpenses(CarbonImmutable $start, int $augustYear): void
    {
        $incidentals = [
            ['Kantong sampah besar 2 pak', 64000],
            ['Ganti 2 lampu jalan LED 30W di Blok B', 96000],
            ['Konsumsi rapat pengurus', 175000],
            ['Sapu lidi, serok, dan karbol untuk pos ronda', 58000],
            ['Fotokopi undangan rapat warga', 24000],
            ['Baterai senter dan kentongan baru untuk ronda', 67500],
            ['Servis kunci gembok portal Blok C', 120000],
            ['Kopi, gula, dan teh untuk pos ronda', 48000],
            ['Cat ulang polisi tidur Jl. Flamboyan', 185000],
            ['Pangkas dahan pohon depan A-6 (upah 2 orang)', 250000],
            ['Abate dan obat nyamuk untuk PSN', 42000],
        ];

        for ($m = $start; $m->lte($this->today); $m = $m->addMonth()) {
            $label = $m->translatedFormat('F Y');
            $entries = [[$m->addDays(mt_rand(2, 6)), 'Token listrik pos ronda & lampu jalan', mt_rand(0, 1) ? 100000 : 150000]];
            // Honors are paid from the previous month's dues, so the first month has none.
            if ($m->gt($start)) {
                $entries[] = [$m->addDays(24), "Honor satpam malam (Pak Slamet) {$label}", 1000000];
                $entries[] = [$m->addDays(24), "Honor petugas angkut sampah (Pak Mulyono) {$label}", 450000];
            }

            foreach (array_rand($incidentals, 2) as $key) {
                if (mt_rand(1, 100) <= 70) {
                    $entries[] = [$m->addDays(mt_rand(7, 22)), ...$incidentals[$key]];
                }
            }

            if ($m->month === 8 && $m->year === $augustYear) {
                array_push($entries,
                    [$m->addDays(3), 'Bendera, umbul-umbul, dan tali untuk HUT RI', 320000],
                    [$m->addDays(15), 'Konsumsi malam tirakatan 16 Agustus', 865000],
                    [$m->addDays(15), 'Sewa sound system tirakatan & lomba', 400000],
                    [$m->addDays(16), 'Hadiah lomba 17-an (anak & dewasa)', 1350000],
                );
            }
            if ($m->month === 12) {
                $entries[] = [$m->addDays(19), 'Parcel akhir tahun untuk Pak Slamet dan Pak Mulyono', 400000];
            }

            foreach ($entries as [$spentOn, $description, $amount]) {
                if ($spentOn->gt($this->today)) {
                    continue;
                }
                $this->expense($spentOn, $description, $amount);
            }
        }

        $mourning = $this->today->subDays(38);
        $this->expense($mourning, 'Santunan duka keluarga Bpk. Agus Setiawan (B-3)', 500000);
        $this->expense($mourning, 'Karangan bunga duka cita a.n. warga RT 05', 350000);
    }

    private function expense(CarbonImmutable $spentOn, string $description, int $amount): void
    {
        $category = match (true) {
            (bool) preg_match('/listrik|fotokopi|rapat/i', $description) => 'operasional',
            (bool) preg_match('/santunan|duka|parcel/i', $description) => 'sosial',
            (bool) preg_match('/sampah|sapu|karbol|pangkas|abate/i', $description) => 'kebersihan',
            (bool) preg_match('/satpam|ronda|kentongan/i', $description) => 'keamanan',
            (bool) preg_match('/lampu|cat ulang|servis|polisi tidur/i', $description) => 'perbaikan',
            (bool) preg_match('/HUT|tirakatan|lomba|17-an|sound/i', $description) => 'kegiatan',
            default => 'lainnya',
        };

        // Honors are handed over in cash without a nota; recent purchases come with a photo of one.
        $withReceipt = ! str_starts_with($description, 'Honor') && $spentOn->gte($this->today->subMonths(4)) && mt_rand(1, 100) <= 65;

        Expense::firstOrCreate(
            ['spent_on' => $spentOn->toDateString(), 'description' => $description],
            [
                'category' => $category,
                'amount' => $amount,
                'receipt_path' => $withReceipt ? $this->receiptImage($spentOn, $description, $amount) : null,
                'recorded_by' => $this->treasurer->id,
            ],
        );
    }

    /**
     * @param  array<string, array{model: Household, lag: int, transfer: bool}>  $households
     */
    private function seedPosts(array $households, int $augustYear, DuesType $guardPost): void
    {
        $nextSunday = $this->today->next(CarbonImmutable::SUNDAY);
        $rondaMonth = $this->today->day >= 20 ? $this->today->startOfMonth()->addMonth() : $this->today->startOfMonth();
        $lastMonth = $this->today->startOfMonth()->subMonth();
        $hut = $augustYear - 1945;

        $men = collect($households)
            ->filter(fn ($h) => $h['model']->is_active && ! preg_match('/^(Hj\.|dr\.|Endang|Dwi)/', $h['model']->head_name))
            ->map(fn ($h, $number) => 'Pak '.Str::of($h['model']->head_name)->replaceMatches('/^(Ir\.|Drs\.)\s*/', '')->explode(' ')->first()." ({$number})")
            ->values();
        $nights = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $schedule = collect($nights)
            ->map(fn ($night, $i) => "- **{$night}:** ".$men->slice($i * 4, 4)->implode(', '))
            ->implode("\n");

        $income = Payment::whereBetween('paid_on', [$lastMonth, $lastMonth->endOfMonth()])->sum('amount');
        $spent = Expense::whereBetween('spent_on', [$lastMonth, $lastMonth->endOfMonth()])->sum('amount');
        $rupiah = fn (int $n) => 'Rp '.number_format($n, 0, ',', '.');
        $guardPaid = Payment::where('dues_type_id', $guardPost->id)->count();

        $posts = [
            [
                'category' => 'pengumuman',
                'title' => 'Jadwal Ronda Malam '.$rondaMonth->translatedFormat('F Y'),
                'image' => 'ronda-malam.jpg',
                'is_pinned' => true,
                'published_at' => $this->today->subDays(3)->setTime(19, 5),
                'body' => "Berikut jadwal ronda untuk bulan {$rondaMonth->translatedFormat('F')}. Ronda mulai pukul **22.00 sampai 03.00**, kumpul di pos ronda Blok B.\n\n{$schedule}\n\nYang berhalangan mohon tukar jadwal sendiri dengan warga lain lalu kabari Pak RT. Yang tidak hadir tanpa kabar dikenakan denda Rp 20.000 masuk Kas RT, sesuai kesepakatan rapat.\n\nTerima kasih, semoga lingkungan kita tetap aman.",
            ],
            [
                'category' => 'iuran',
                'title' => 'Iuran Renovasi Pos Ronda Sudah Dibuka',
                'image' => 'renovasi-pos-ronda.jpg',
                'is_popup' => true,
                'published_at' => $this->today->subDays(10)->setTime(8, 30),
                'body' => "Sesuai hasil rapat warga bulan lalu, pos ronda akan direnovasi karena atapnya bocor dan lantainya retak.\n\n**Sumbangan: {$rupiah($guardPost->amount)} per rumah**, paling lambat {$guardPost->due_on->translatedFormat('j F Y')}.\n\nRencana pekerjaan:\n\n- Ganti atap seng dengan spandek\n- Pasang keramik lantai\n- Cat ulang dan perbaiki bangku\n\nSampai hari ini sudah {$guardPaid} rumah yang menyumbang. Pembayaran bisa transfer lalu unggah bukti di menu **Bayar Iuran**, atau tunai ke Ibu Sri (B-4).",
            ],
            [
                'category' => 'kegiatan',
                'title' => 'Kerja Bakti Bersih Saluran Air Menjelang Musim Hujan',
                'image' => 'kerja-bakti.jpg',
                'event_starts_at' => $nextSunday->setTime(7, 0),
                'event_location' => 'Kumpul di pos ronda Blok B',
                'published_at' => $this->today->subDays(2)->setTime(16, 20),
                'body' => "Musim hujan sebentar lagi. Tahun lalu got di depan Blok C sempat meluap, jadi kita bersihkan bersama sebelum terlambat.\n\nMohon setiap rumah mengirim minimal satu orang dan membawa:\n\n- Cangkul atau sekop\n- Karung bekas\n- Sarung tangan\n\nKonsumsi (nasi bungkus dan teh) disiapkan dari Kas RT. Selesai kira-kira pukul 10.00.",
            ],
            [
                'category' => 'kegiatan',
                'title' => 'Posyandu Balita dan Lansia '.$nextSunday->addDays(6)->translatedFormat('F'),
                'image' => 'posyandu.jpg',
                'event_starts_at' => $nextSunday->addDays(6)->setTime(8, 0),
                'event_location' => 'Teras rumah Ibu Ketua RT (B-7)',
                'published_at' => $this->today->subDay()->setTime(10, 0),
                'body' => "Posyandu rutin bersama kader dari Puskesmas Dinoyo.\n\n- **Balita:** timbang, ukur tinggi badan, vitamin A, dan imunisasi sesuai jadwal\n- **Lansia:** cek tensi, gula darah, dan asam urat (gratis)\n\nJangan lupa bawa buku KIA untuk balita dan KTP untuk lansia.",
            ],
            [
                'category' => 'kegiatan',
                'title' => 'Rapat Warga: Pembahasan Pemasangan CCTV',
                'image' => 'rapat-warga.jpg',
                'event_starts_at' => $nextSunday->addDays(13)->setTime(19, 30),
                'event_location' => 'Balai warga RW 11',
                'published_at' => $this->today->setTime(7, 15),
                'body' => "Setelah kejadian hilangnya helm di carport A-4 bulan lalu, beberapa warga mengusulkan pemasangan CCTV di dua pintu masuk perumahan.\n\nYang akan dibahas:\n\n1. Titik pemasangan dan jumlah kamera\n2. Perkiraan biaya (sudah ada 2 penawaran dari toko)\n3. Cara pengumpulan dana\n\nMohon kehadiran kepala keluarga atau perwakilannya. Keputusan diambil berdasarkan suara yang hadir.",
            ],
            [
                'category' => 'iuran',
                'title' => 'Laporan Kas RT Bulan '.$lastMonth->translatedFormat('F Y'),
                'published_at' => $this->today->startOfMonth()->addDays(min(4, $this->today->day - 1))->setTime(20, 0),
                'body' => "Rekap keuangan RT 05 untuk bulan {$lastMonth->translatedFormat('F Y')}:\n\n- **Pemasukan iuran:** {$rupiah($income)}\n- **Pengeluaran:** {$rupiah($spent)}\n- **Selisih:** {$rupiah($income - $spent)}\n\nPengeluaran rutin masih untuk honor satpam, petugas sampah, dan listrik lampu jalan. Rincian per transaksi bisa dilihat di menu **Buku Kas**.\n\nBagi warga yang masih ada tunggakan, mohon segera dilunasi. Terima kasih untuk yang sudah tertib membayar.",
            ],
            [
                'category' => 'berita',
                'title' => 'Berita Duka: Ayahanda Bapak Agus Setiawan (B-3)',
                'published_at' => $this->today->subDays(38)->setTime(5, 40),
                'body' => "Innalillahi wa inna ilaihi raji'un.\n\nTelah berpulang ayahanda dari Bapak Agus Setiawan (B-3), **Bapak H. Sukarman** (78 tahun), pagi ini pukul 03.15 di RS Saiful Anwar.\n\nJenazah disemayamkan di rumah duka B-3 dan dimakamkan ba'da Dzuhur di TPU Tlogomas.\n\nWarga yang bisa membantu mohon merapat ke rumah duka untuk persiapan tenda dan kursi. Santunan dari Kas RT sudah diserahkan kepada keluarga.",
            ],
            [
                'category' => 'berita',
                'title' => "Keseruan Lomba HUT RI ke-{$hut} di RT 05",
                'image' => 'lomba-hut-ri.jpg',
                'published_at' => CarbonImmutable::create($augustYear, 8, 19, 9, 0),
                'body' => "Terima kasih untuk seluruh warga yang sudah meramaikan lomba 17-an kemarin. Suasananya guyub sekali.\n\nPara juara:\n\n- **Balap karung anak:** Naufal (A-5)\n- **Makan kerupuk:** Keisha (C-7)\n- **Tarik tambang bapak-bapak:** tim Blok B\n- **Estafet kelereng ibu-ibu:** tim Blok A\n- **Panjat pinang:** tim gabungan pemuda\n\nHadiah sudah dibagikan saat malam puncak. Laporan penggunaan dana HUT RI bisa dicek di Buku Kas.",
            ],
            [
                'category' => 'kegiatan',
                'title' => 'Malam Tirakatan 16 Agustus',
                'image' => 'tirakatan.jpg',
                'event_starts_at' => CarbonImmutable::create($augustYear, 8, 16, 19, 30),
                'event_location' => 'Jalan depan Blok B (ditutup sementara)',
                'published_at' => CarbonImmutable::create($augustYear, 8, 10, 18, 0),
                'body' => "Malam tirakatan menyambut HUT RI ke-{$hut}. Acara: doa bersama, sambutan Pak RT, pembacaan nama pahlawan oleh adik-adik, lalu makan tumpeng bersama.\n\nIbu-ibu dimohon membawa satu jenis kue untuk dimakan bersama. Tikar dan konsumsi utama disiapkan panitia.",
            ],
            [
                'category' => 'pengumuman',
                'title' => 'Jadwal Angkut Sampah Berubah Jadi Senin, Rabu, Sabtu',
                'image' => 'angkut-sampah.jpg',
                'published_at' => $this->today->subDays(21)->setTime(12, 10),
                'body' => "Mulai minggu depan, Pak Mulyono mengangkut sampah setiap **Senin, Rabu, dan Sabtu** mulai pukul 07.00 (sebelumnya Selasa, Kamis, Sabtu), menyesuaikan jadwal TPS Tlogomas.\n\nMohon sampah dikeluarkan pagi hari, jangan malam, supaya tidak dibongkar kucing. Sampah dipilah organik dan anorganik kalau bisa.",
            ],
            [
                'category' => 'berita',
                'title' => 'Fogging DBD Sudah Dilakukan di Seluruh Blok',
                'image' => 'fogging-dbd.jpg',
                'published_at' => $this->today->subDays(55)->setTime(15, 0),
                'body' => "Petugas Puskesmas Dinoyo sudah melakukan fogging di Blok A, B, dan C setelah ada satu warga yang dirawat karena DBD.\n\nFogging hanya membunuh nyamuk dewasa, jadi tetap lakukan **3M Plus**: menguras, menutup, dan mendaur ulang barang bekas, ditambah taburkan abate. Abate bisa diminta gratis ke Ibu Sri (B-4).",
            ],
            [
                'category' => 'pengumuman',
                'title' => 'Rencana Pembuatan Grup WhatsApp Khusus Ibu-Ibu PKK',
                'published_at' => null,
                'body' => 'Draf, belum diumumkan. Menunggu konfirmasi Ibu Ketua PKK soal admin grup.',
            ],
        ];

        foreach ($posts as $post) {
            $image = Arr::pull($post, 'image');
            $model = Post::firstOrCreate(['slug' => Str::slug($post['title'])], $post + [
                'is_pinned' => false,
                'is_popup' => false,
                'author_id' => $post['category'] === 'iuran' ? $this->treasurer->id : $this->admin->id,
            ]);

            if ($image && ! $model->image_path) {
                $path = 'kabar/demo-'.$image;
                Storage::disk('public')->put($path, file_get_contents(database_path('seeders/images/kabar/'.$image)));
                $model->update(['image_path' => $path]);
            }
        }
    }

    /**
     * @param  array<string, array{model: Household, lag: int, transfer: bool}>  $households
     */
    private function seedMembers(array $households): void
    {
        $wives = ['Siti Rahmawati', 'Dewi Lestari', 'Endah Purwaningsih', 'Rina Marlina', 'Yuli Astuti', 'Nurul Hidayah', 'Retno Wulandari', 'Fitri Handayani', 'Ani Susilowati', 'Lilik Suryani', 'Wulan Sari', 'Indah Permatasari', 'Tutik Rahayu', 'Erna Kusumawati', 'Heni Sulistyowati'];
        $christianWives = ['Maria Christina', 'Yuliana Kristanti', 'Theresia Lestari', 'Agnes Kurniasari'];
        $sons = ['Raka Aditya', 'Fikri Hidayat', 'Bagas Saputra', 'Dimas Ardiansyah', 'Galih Prakoso', 'Rafi Alfarizi', 'Yoga Pratama', 'Arya Wicaksana', 'Daffa Ramadhan', 'Farel Kurniawan'];
        $daughters = ['Nadia Putri', 'Salsabila Azzahra', 'Alya Rahma', 'Citra Anggraini', 'Zahra Nur Aini', 'Tiara Kusuma', 'Aurel Safitri', 'Bunga Lestari', 'Nayla Putri', 'Anisa Fitriani'];
        // Kids named in the HUT RI post.
        $knownKids = ['A-5' => ['Naufal Pratama', 'L'], 'C-7' => ['Keisha Aulia', 'P']];

        foreach ($households as $number => $info) {
            $household = $info['model'];
            if (! $household->is_active || in_array($number, self::NO_KK, true)) {
                continue;
            }

            $religion = self::RELIGIONS[$number] ?? 'Islam';
            $headBorn = $this->today->subYears($number === 'A-3' ? mt_rand(68, 74) : mt_rand(34, 64))->subDays(mt_rand(0, 364));
            $headMarital = match ($number) {
                'A-3', 'B-6' => 'Cerai Mati',
                'C-6' => 'Cerai Hidup',
                'B-10' => 'Belum Kawin',
                default => 'Kawin',
            };

            $members = [[
                'name' => preg_replace('/^(Hj\.|Ir\.|Drs\.|dr\.)\s*/', '', $household->head_name),
                'gender' => in_array($number, self::FEMALE_HEADS, true) ? 'P' : 'L',
                'birth_date' => $headBorn,
                'family_relation' => 'Kepala Keluarga',
                'marital_status' => $headMarital,
                'education' => match ($number) {
                    'A-9', 'B-7', 'B-10' => 'S1',
                    default => collect(['SMA / Sederajat', 'SMA / Sederajat', 'D3', 'S1', 'S1', 'SMP / Sederajat'])->get(mt_rand(0, 5)),
                },
                'job' => match (true) {
                    $number === 'B-10' => 'Dokter',
                    $number === 'A-3' || $headBorn->lte($this->today->subYears(58)) => 'Pensiunan',
                    $number === 'B-6' => 'Pedagang',
                    default => collect(['Karyawan Swasta', 'Karyawan Swasta', 'Wiraswasta', 'PNS', 'Guru', 'Pedagang', 'Sopir'])->get(mt_rand(0, 6)),
                },
                'phone' => $household->phone,
            ]];

            if ($headMarital === 'Kawin') {
                $members[] = [
                    'name' => $number === 'B-4' ? 'Sri Wahyuni' : ($religion === 'Islam' ? $wives : $christianWives)[mt_rand(0, $religion === 'Islam' ? count($wives) - 1 : count($christianWives) - 1)],
                    'gender' => 'P',
                    'birth_date' => $headBorn->addYears(mt_rand(0, 6))->addDays(mt_rand(0, 300)),
                    'family_relation' => 'Istri',
                    'marital_status' => 'Kawin',
                    'education' => collect(['SMA / Sederajat', 'D3', 'S1'])->get(mt_rand(0, 2)),
                    'job' => collect(['Mengurus Rumah Tangga', 'Mengurus Rumah Tangga', 'Mengurus Rumah Tangga', 'Karyawan Swasta', 'Guru', 'Wiraswasta', 'Perawat'])->get(mt_rand(0, 6)),
                    'phone' => mt_rand(1, 100) <= 60 ? $this->phone() : null,
                ];
            }

            if ($number === 'A-3') {
                $daughterBorn = $headBorn->addYears(27);
                array_push($members,
                    ['name' => 'Ratna Dewi', 'gender' => 'P', 'birth_date' => $daughterBorn, 'family_relation' => 'Anak', 'marital_status' => 'Kawin', 'education' => 'S1', 'job' => 'Guru'],
                    ['name' => 'Rudi Santoso', 'gender' => 'L', 'birth_date' => $daughterBorn->subYears(3), 'family_relation' => 'Menantu', 'marital_status' => 'Kawin', 'education' => 'S1', 'job' => 'Karyawan Swasta', 'phone' => $this->phone()],
                    ['name' => 'Rizki Ananda', 'gender' => 'L', 'birth_date' => $daughterBorn->addYears(29), 'family_relation' => 'Cucu', 'marital_status' => 'Belum Kawin'],
                );
            } elseif ($number !== 'B-10') {
                $kids = mt_rand(0, 3);
                $kidBorn = $headBorn->addYears(mt_rand(25, 33));
                for ($i = 0; $i < max($kids, isset($knownKids[$number]) ? 1 : 0); $i++) {
                    if ($kidBorn->gt($this->today->subYear())) {
                        break;
                    }
                    [$name, $gender] = $i === 0 && isset($knownKids[$number])
                        ? $knownKids[$number]
                        : (mt_rand(0, 1) ? [$sons[mt_rand(0, count($sons) - 1)], 'L'] : [$daughters[mt_rand(0, count($daughters) - 1)], 'P']);
                    $members[] = ['name' => $name, 'gender' => $gender, 'birth_date' => $kidBorn, 'family_relation' => 'Anak', 'marital_status' => 'Belum Kawin'];
                    $kidBorn = $kidBorn->addYears(mt_rand(2, 5))->addDays(mt_rand(0, 300));
                }
            }

            foreach ($members as $seq => $member) {
                $age = $member['birth_date']->diffInYears($this->today);
                $household->members()->create($member + [
                    'nik' => $this->nik($member['birth_date'], $member['gender'], $seq + 1),
                    'birth_place' => collect(['Malang', 'Malang', 'Malang', 'Surabaya', 'Blitar', 'Kediri', 'Pasuruan', 'Jember', 'Batu', 'Tulungagung'])->get(mt_rand(0, 9)),
                    'religion' => $religion,
                    'education' => match (true) {
                        isset($member['education']) => $member['education'],
                        $age < 6 => 'Belum Sekolah',
                        $age < 13 => 'SD / Sederajat',
                        $age < 16 => 'SMP / Sederajat',
                        $age < 22 => 'SMA / Sederajat',
                        default => 'S1',
                    },
                    'job' => $member['job'] ?? match (true) {
                        $age < 5 => 'Belum / Tidak Bekerja',
                        $age < 23 => 'Pelajar / Mahasiswa',
                        default => 'Karyawan Swasta',
                    },
                ]);
            }

            $household->update(['kk_number' => '357305'.str_pad((string) mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT)]);
            if (mt_rand(1, 100) <= 50) {
                $household->update(['kk_image_path' => $this->kkImage($household->load('members'))]);
            }
        }
    }

    /**
     * @param  array<string, array{model: Household, lag: int, transfer: bool}>  $households
     */
    private function seedLetters(array $households): void
    {
        $withMembers = collect($households)->pluck('model')->filter(fn (Household $h) => $h->members()->exists())->values();
        if ($withMembers->isEmpty()) {
            return;
        }

        $requests = [
            [64, 'domisili', 'Membuka rekening bank', Letter::APPROVED],
            [41, 'skck', 'Melamar pekerjaan', Letter::APPROVED],
            [23, 'usaha', 'Pengajuan Kredit Usaha Rakyat (KUR) di BRI', Letter::APPROVED],
            [12, 'domisili', 'Mengurus pindah sekolah anak', Letter::APPROVED],
            [9, 'nikah', 'Persyaratan nikah di KUA Lowokwaru', Letter::REJECTED, 'Status perkawinan di data KK masih tertulis Kawin. Mohon kirim foto KK terbaru ke Ibu Sri (B-4), setelah diperbaiki silakan ajukan ulang.'],
            [3, 'skck', 'Melamar kerja di BUMN', Letter::PENDING],
            [1, 'domisili', 'Mengurus BPJS Kesehatan', Letter::PENDING],
            [0, 'tidak_mampu', 'Keringanan biaya sekolah anak', Letter::PENDING],
        ];
        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $sequences = [];

        foreach ($requests as $i => [$daysAgo, $type, $purpose, $status]) {
            $household = $withMembers[($i * 5) % $withMembers->count()];
            $adults = $household->members()->get()->filter(fn (HouseholdMember $m) => $m->birth_date?->lte($this->today->subYears(17)));
            $member = in_array($type, ['skck', 'nikah'], true) && $adults->count() > 1 ? $adults->last() : $adults->first();
            $submittedAt = $this->today->subDays($daysAgo)->setTime(mt_rand(6, 21), mt_rand(0, 59))->min(now());

            $letter = new Letter([
                'code' => Letter::newCode(),
                'household_id' => $household->id,
                'household_member_id' => $member->id,
                'type' => $type,
                'purpose' => $purpose,
                'person' => Letter::personFrom($member),
                'phone' => $member->phone ?? $household->phone,
                'status' => $status,
                'reject_reason' => $requests[$i][4] ?? null,
            ]);

            if ($status !== Letter::PENDING) {
                $reviewedAt = $submittedAt->addHours(mt_rand(3, 30))->min(now());
                $letter->forceFill(['reviewed_by' => $this->admin->id, 'reviewed_at' => $reviewedAt]);

                if ($status === Letter::APPROVED) {
                    $sequence = $sequences[$reviewedAt->year] = ($sequences[$reviewedAt->year] ?? 0) + 1;
                    $letter->forceFill([
                        'sequence' => $sequence,
                        'number' => sprintf('%03d/%s/%s/%d', $sequence, 'RT.05/RW.11', $romanMonths[$reviewedAt->month - 1], $reviewedAt->year),
                        'issued_on' => $reviewedAt->toDateString(),
                    ]);
                }
            }

            $letter->created_at = $submittedAt;
            $letter->updated_at = $letter->reviewed_at ?? $submittedAt;
            $letter->save();
        }

        $this->command?->info('Contoh link halaman rumah warga: '.$withMembers->first()->portalUrl());
    }

    /**
     * Sample bank statement for trying the reconciliation page: most pending transfers are on it, one is not,
     * and one QRIS payment has no confirmation yet.
     *
     * @param  array<string, array{model: Household, lag: int, transfer: bool}>  $households
     */
    private function writeStatement(array $households): void
    {
        $pending = PaymentSubmission::pending()->with('household')->oldest()->get();
        $rupiah = fn (int $n) => number_format($n, 2, '.', ',');
        $lines = [];

        foreach ($pending->slice(0, max(1, $pending->count() - 1)) as $submission) {
            $payer = Str::upper(Str::ascii($submission->payer_name ?? $submission->household->head_name));
            $lines[] = [$submission->created_at, "TRSF E-BANKING CR DARI {$payer}", $rupiah($submission->transferTotal()).' CR'];
        }

        $busy = $pending->pluck('household_id')->all();
        $qris = collect($households)->pluck('model')->first(fn (Household $h) => $h->is_active && ! in_array($h->id, $busy, true));
        if ($qris) {
            $lines[] = [$this->today->subDay()->setTime(19, 42), 'QRIS CR PEMBAYARAN QR NMID ID1023289412881', $rupiah(75000 + $qris->uniqueCode()).' CR'];
        }
        $lines[] = [$this->today->subDays(2)->setTime(9, 15), 'TRSF E-BANKING DB PEMBELIAN TOKEN PLN', $rupiah(150000).' DB'];
        $lines[] = [$this->today->subDays(3)->setTime(23, 59), 'BUNGA TABUNGAN', $rupiah(1250).' CR'];

        $text = collect($lines)
            ->sortBy(fn (array $line) => $line[0])
            ->map(fn (array $line) => sprintf('%s  %-48s %18s', $line[0]->format('d/m/Y'), $line[1], $line[2]))
            ->implode("\n");

        Storage::disk('local')->put('demo/mutasi-rekening.txt', $text."\n");
        $this->command?->info('Contoh mutasi rekening untuk menu Cocokkan Mutasi: '.Storage::disk('local')->path('demo/mutasi-rekening.txt'));
    }

    private function nik(CarbonImmutable $born, string $gender, int $seq): string
    {
        return '357305'.str_pad((string) ($born->day + ($gender === 'P' ? 40 : 0)), 2, '0', STR_PAD_LEFT).$born->format('my').str_pad((string) (mt_rand(1, 9989) + $seq), 4, '0', STR_PAD_LEFT);
    }

    private function kkImage(Household $household): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $img = imagecreatetruecolor(900, 260 + $household->members->count() * 28);
        $paper = imagecolorallocate($img, 246, 243, 232);
        $dark = imagecolorallocate($img, 30, 41, 59);
        $muted = imagecolorallocate($img, 100, 116, 139);
        $line = imagecolorallocate($img, 203, 213, 225);
        $red = imagecolorallocate($img, 220, 38, 38);

        imagefill($img, 0, 0, $paper);
        imagestring($img, 5, 330, 20, 'KARTU KELUARGA', $dark);
        imagestring($img, 4, 320, 42, 'No. '.$household->kk_number, $dark);
        imagestring($img, 3, 30, 80, 'Nama Kepala Keluarga : '.Str::ascii($household->members->first()->name), $dark);
        imagestring($img, 3, 30, 98, 'Alamat               : Perum Griya Tlogomas Asri '.$household->number.' RT 005 / RW 011', $dark);
        imagestring($img, 3, 30, 116, 'Kelurahan            : Tlogomas, Kec. Lowokwaru, Kota Malang', $dark);

        $y = 150;
        imageline($img, 30, $y - 6, 870, $y - 6, $line);
        foreach (['No' => 30, 'Nama Lengkap' => 60, 'NIK' => 290, 'L/P' => 450, 'Tgl Lahir' => 490, 'Hubungan' => 590, 'Pekerjaan' => 700] as $label => $x) {
            imagestring($img, 3, $x, $y, $label, $muted);
        }
        foreach ($household->members as $i => $member) {
            $y += 28;
            imageline($img, 30, $y - 6, 870, $y - 6, $line);
            foreach ([30 => $i + 1, 60 => Str::limit(Str::ascii($member->name), 30, ''), 290 => $member->nik, 450 => $member->gender, 490 => $member->birth_date->format('d-m-Y'), 590 => $member->family_relation, 700 => Str::limit($member->job, 22, '')] as $x => $value) {
                imagestring($img, 3, $x, $y, (string) $value, $dark);
            }
        }
        imagestring($img, 5, 330, $y + 50, 'CONTOH DATA DEMO', $red);

        $path = "kk/demo-{$household->number}.png";
        ob_start();
        imagepng($img);
        Storage::disk('local')->put($path, ob_get_clean());

        return $path;
    }

    private function receiptImage(CarbonImmutable $spentOn, string $description, int $amount): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $img = imagecreatetruecolor(320, 420);
        $paper = imagecolorallocate($img, 255, 253, 245);
        $ink = imagecolorallocate($img, 30, 58, 138);
        $muted = imagecolorallocate($img, 100, 116, 139);
        $line = imagecolorallocate($img, 203, 213, 225);

        imagefill($img, 0, 0, $paper);
        $store = collect(['TB SUMBER REJEKI', 'TOKO MAKMUR JAYA', 'UD SINAR ABADI', 'TOKO LISTRIK TERANG', 'WARUNG BU TINI', 'FOTOCOPY BAROKAH'])->get(mt_rand(0, 5));
        imagestring($img, 5, (int) (160 - strlen($store) * 4.5), 20, $store, $ink);
        imagestring($img, 2, 92, 42, 'Jl. MT Haryono, Malang', $muted);
        imagestring($img, 4, 20, 76, 'NOTA', $ink);
        imagestring($img, 3, 180, 78, 'Tgl: '.$spentOn->format('d-m-Y'), $ink);
        imageline($img, 20, 104, 300, 104, $line);

        $y = 116;
        foreach (str_split(Str::ascii($description), 38) as $chunk) {
            imagestring($img, 3, 20, $y, $chunk, $ink);
            $y += 18;
        }
        imageline($img, 20, 320, 300, 320, $line);
        $total = 'Rp '.number_format($amount, 0, ',', '.');
        imagestring($img, 4, 20, 332, 'TOTAL', $ink);
        imagestring($img, 4, 300 - strlen($total) * 8, 332, $total, $ink);
        imagestring($img, 2, 104, 388, 'Terima kasih', $muted);

        $path = 'nota/demo-'.substr(md5($spentOn->toDateString().$description), 0, 12).'.png';
        ob_start();
        imagepng($img);
        Storage::disk('public')->put($path, ob_get_clean());

        return $path;
    }

    private function phone(): string
    {
        return self::PHONE_PREFIXES[mt_rand(0, count(self::PHONE_PREFIXES) - 1)].str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    private function code(): string
    {
        return collect(range(1, 8))->map(fn () => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'[mt_rand(0, 31)])->implode('');
    }

    private function proofImage(string $code, BankAccount $bank, int $amount, CarbonImmutable $at, string $payer): string
    {
        $path = "bukti-bayar/demo-{$code}.png";
        if (! function_exists('imagecreatetruecolor')) {
            return $path;
        }

        $img = imagecreatetruecolor(360, 520);
        $white = imagecolorallocate($img, 255, 255, 255);
        $brand = imagecolorallocate($img, 0, 82, 155);
        $green = imagecolorallocate($img, 22, 163, 74);
        $dark = imagecolorallocate($img, 30, 41, 59);
        $muted = imagecolorallocate($img, 100, 116, 139);
        $line = imagecolorallocate($img, 226, 232, 240);

        imagefill($img, 0, 0, $white);
        imagefilledrectangle($img, 0, 0, 360, 64, $brand);
        imagestring($img, 5, 20, 24, 'Mobile Banking', $white);
        imagefilledellipse($img, 180, 120, 56, 56, $green);
        imagestring($img, 5, 174, 112, 'v', $white);
        imagestring($img, 5, 108, 164, 'Transfer Berhasil', $green);
        $total = 'Rp '.number_format($amount, 0, ',', '.');
        imagestring($img, 5, (int) (180 - strlen($total) * 4.5), 196, $total, $dark);

        $rows = [
            'Tanggal' => $at->format('d M Y H:i').' WIB',
            'Dari' => Str::limit(Str::upper(Str::ascii($payer)), 20, ''),
            'Ke' => Str::limit(Str::upper(Str::before($bank->account_name, ' (')), 22, ''),
            'Bank' => Str::limit($bank->bank_name, 20, ''),
            'No. Rek' => $bank->account_number,
            'No. Ref' => $at->format('ymdHi').str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
            'Berita' => 'IURAN RT05',
        ];
        $y = 244;
        foreach ($rows as $label => $value) {
            imageline($img, 20, $y - 8, 340, $y - 8, $line);
            imagestring($img, 3, 20, $y, $label, $muted);
            imagestring($img, 3, 340 - strlen($value) * 7, $y, $value, $dark);
            $y += 34;
        }

        ob_start();
        imagepng($img);
        Storage::disk('local')->put($path, ob_get_clean());

        return $path;
    }
}
