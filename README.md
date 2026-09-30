# Kabar Warga

Papan informasi lingkungan dan buku iuran untuk RT/RW, dibangun dengan Laravel 13 + Blade + Tailwind (tanpa Filament), dilengkapi REST API untuk aplikasi mobile.

- Warga (tanpa login): pengumuman, berita, kegiatan, status iuran per rumah, buku kas, dan pembayaran online: unggah bukti transfer, lalu cek statusnya dengan kode pengajuan.
- Pengurus (login di `/masuk`): mencatat pembayaran iuran (bisa beberapa bulan sekaligus), memeriksa bukti transfer (setujui penuh atau sebagian, tolak, batalkan), menulis postingan, mengelola rumah, jenis iuran, pengeluaran, rekening bank/QRIS, pengaturan, dan akun admin lain.

## Setup (development, SQLite)

```sh
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # membuat admin dari ADMIN_* di .env
php artisan storage:link            # dibutuhkan untuk foto postingan dan gambar QRIS
npm run build                       # atau `npm run dev` saat mengedit view
php artisan serve
```

Data demo yang realistis (nama karangan, khusus development) bersifat opsional: `php artisan migrate:fresh --seeder=DemoSeeder`.

Segera ganti kata sandi admin bawaan setelah login pertama (Pengaturan → Ganti kata sandi).

## REST API (aplikasi mobile)

- Base URL: `/api/v1`. Selalu kirim header `Accept: application/json`.
- Endpoint warga bersifat publik. Endpoint admin ada di `/api/v1/admin/*` (ditambah `/auth/me` dan `/auth/logout`) dan membutuhkan token Sanctum dari `POST /api/v1/auth/login` (`email`, `password`, `device_name`), dikirim sebagai `Authorization: Bearer <token>`.
- Nominal uang berupa integer dalam Rupiah. Periode bulanan memakai format `YYYY-MM`; iuran sekali bayar punya `period: null`.
- Update yang mengunggah file (postingan, rekening bank) harus dikirim sebagai `POST` multipart dengan `_method=PUT`. Flag boolean yang tidak dikirim saat update (`is_active`, `is_pinned`, `is_popup`) dianggap `false`, sama seperti form web.
- Error: `401` token tidak ada/tidak valid, `404` data tidak ditemukan, `409` konflik aturan bisnis (mis. menghapus rumah yang sudah punya pembayaran), `422` validasi (`errors` per field), `429` terkena rate limit.
- Rate limit: login 6/menit, pengiriman bukti pembayaran 10/menit.

| Area | Endpoint |
|---|---|
| Warga | `GET /home`, `/settings`, `/posts`, `/posts/{slug}`, `/households`, `/dues-types`, `/bank-accounts`, `/dues/ledger`, `/dues/cashbook`, `/households/{household}/dues/{duesType}` |
| Bukti pembayaran | `POST /payment-submissions`, `GET /payment-submissions/{code}` |
| Auth | `POST /auth/login`, `GET /auth/me`, `POST /auth/logout` |
| Admin | `payments`, `payment-submissions` (approve / reject / cancel / reopen, file bukti), `posts`, `households`, `dues-types`, `expenses`, `bank-accounts`, `settings`, `password`, `users` |

### Dokumentasi

Dokumentasi interaktif (Scalar) tersedia di **`/docs/api`**, dirender dari spesifikasi OpenAPI yang ditulis manual di `resources/openapi/v1.yaml`. Deskripsi di spesifikasi ditulis dalam Bahasa Indonesia untuk developer mobile; tulis endpoint baru dengan bahasa yang sama. Perbarui file tersebut setiap kali ada endpoint yang berubah. Secara default halaman dokumentasi terbuka untuk semua orang; batasi aksesnya dengan mendefinisikan gate `viewScalar` di `AppServiceProvider` bila perlu.

## Struktur kode

- Controller web: `app/Http/Controllers` (+ `Admin/`). Controller API: `app/Http/Controllers/Api/V1` (+ `Admin/`).
- Validasi yang dipakai bersama oleh web dan API: `app/Http/Requests`. Bentuk JSON: `app/Http/Resources`.
- Aturan pembayaran yang dipakai bersama oleh web dan API: `app/Support` (`PaymentSubmitter`, `PaymentRecorder`, `SubmissionReview`, `DuesLedger`, `HomeFeed`).

## Catatan

- Arah desain ada di `DESIGN.md`.
- Test: `php artisan test` (cakupan API di `tests/Feature/PublicApiTest.php` dan `tests/Feature/AdminApiTest.php`).

## TODO

- [x] REST API `/api/v1` untuk warga dan pengurus
- [x] Spesifikasi OpenAPI + dokumentasi Scalar di `/docs/api`
- [ ] Aplikasi mobile yang memakai API
  - [ ] Sisi warga: beranda, postingan, status iuran per rumah, buku kas
  - [ ] Pembayaran online: pilih rumah, jenis iuran, bulan, dan rekening tujuan, unggah bukti transfer (gambar atau PDF, maks 5 MB), cek status dengan kode pengajuan
  - [ ] Login pengurus (token Sanctum disimpan aman di perangkat) dan logout
  - [ ] Sisi pengurus: catat pembayaran, periksa bukti transfer, kelola postingan, rumah, jenis iuran, pengeluaran, rekening bank/QRIS, pengaturan, pengguna
  - [ ] Tangani respons `401` (kembali ke login), `409`, `422`, dan `429` secara konsisten
