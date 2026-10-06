# Kabar Warga

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![REST API](https://img.shields.io/badge/REST_API-OpenAPI_/_Scalar-0ea5e9?logo=openapiinitiative&logoColor=white)](#-rest-api-aplikasi-mobile)
[![OCR](https://img.shields.io/badge/OCR-Kartu_Keluarga-8A2BE2)](https://github.com/naptha/tesseract.js)
[![Tests](https://img.shields.io/badge/Tests-55_passed-success?logo=phpunit&logoColor=white)](#-pengujian-kode)

Papan informasi lingkungan dan buku iuran untuk RT/RW, dibangun dengan **Laravel 13 + Blade + Tailwind CSS v4** (tanpa Filament), dilengkapi REST API untuk aplikasi mobile.

- **Warga (tanpa login)**: pengumuman, berita, kegiatan, status iuran per rumah, buku kas transparan, kuitansi digital, pembayaran online (QRIS / transfer + kode unik otomatis), serta cek status pengajuan.
- **Pengurus (login di `/masuk`)**: mencatat pembayaran iuran (tunai / door-to-door), memeriksa bukti bayar (setujui penuh / sebagian / tolak), kirim bukti ke WhatsApp warga, kelola kabar, data rumah & penghuni dengan OCR Kartu Keluarga, jenis iuran, pengeluaran kas, rekening/QRIS, dan akun admin.

---

## ✨ Fitur Utama

- 📢 **Papan Informasi Warga**: Pengumuman, berita, agenda kegiatan warga, dan tombol bagikan langsung ke WhatsApp.
- 💳 **Pembayaran Online & QRIS**: Dukungan scan QRIS dinamis/statis, transfer bank, 3 digit kode unik otomatis, unggah bukti bayar, dan verifikasi admin (penuh / sebagian / tolak).
- 🧾 **Kuitansi Digital Resmi**: Nomor bukti otomatis (`KW-YYYYMM-XXXX`), nominal terbilang (*misal: "Lima Puluh Ribu Rupiah"*), stempel "✓ LUNAS", serta siap cetak A5/A4/PDF bersih tanpa header/footer web.
- 💬 **Integrasi WhatsApp Instan**: Kirim tanda terima resmi via WhatsApp setelah mencatat iuran, pengingat tagihan ramah bagi yang belum lunas, dan salin rekap kas bulanan 1-klik untuk grup WhatsApp RT.
- 👨‍👩‍👧‍👦 **Pendataan Penghuni & OCR Kartu Keluarga**: Rekap anggota keluarga per rumah (NIK, relasi, status hunian pemilik/kontrak), serta fitur foto/unggah Kartu Keluarga dengan pembacaan teks otomatis (OCR).
- 📊 **Transparansi Kas & Ekspor Data**: Buku kas umum transparan dengan saldo berjalan, serta ekspor file CSV (Excel-ready) untuk mutasi kas dan data penduduk.
- 📱 **REST API & Dokumentasi Scalar**: Endpoint publik warga dan admin lengkap dengan spesifikasi OpenAPI interaktif di `/docs/api`.

---

## ⚡ Setup (Development, SQLite)

```sh
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # membuat admin dari ADMIN_* di .env
php artisan storage:link            # dibutuhkan untuk foto postingan dan gambar QRIS
npm run build                       # atau `npm run dev` saat mengedit view
php artisan serve
```

> Data demo yang realistis (nama karangan, khusus development) bersifat opsional:
> ```sh
> php artisan migrate:fresh --seeder=DemoSeeder
> ```
> Segera ganti kata sandi admin bawaan setelah login pertama (*Pengaturan → Ganti kata sandi*).

---

## 🌐 REST API (Aplikasi Mobile)

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
| Admin | `payments`, `payment-submissions` (approve / reject / cancel / reopen, file bukti), `posts`, `households`, `households/{household}/members`, `dues-types`, `expenses`, `bank-accounts`, `settings`, `password`, `users` |

### 📖 Dokumentasi Interaktif

Dokumentasi interaktif (Scalar) tersedia di **`/docs/api`**, dirender dari spesifikasi OpenAPI yang ditulis manual di `resources/openapi/v1.yaml`. Deskripsi di spesifikasi ditulis dalam Bahasa Indonesia untuk developer mobile; tulis endpoint baru dengan bahasa yang sama. Perbarui file tersebut setiap kali ada endpoint yang berubah.

---

## 🧪 Pengujian Kode

```sh
php artisan test
```

Semua endpoint, aturan kas, pembayaran, kuitansi digital, OCR KK, serta ekspor CSV telah diuji dengan **55 Feature & Unit tests (100% pass)**.

---

## 🏗️ Struktur Kode

- Controller web: `app/Http/Controllers` (+ `Admin/`). Controller API: `app/Http/Controllers/Api/V1` (+ `Admin/`).
- Validasi yang dipakai bersama oleh web dan API: `app/Http/Requests`. Bentuk JSON: `app/Http/Resources`.
- Aturan bisnis & keuangan yang dipakai bersama: `app/Support` (`PaymentSubmitter`, `PaymentRecorder`, `SubmissionReview`, `DuesLedger`, `HomeFeed`).
- Arah desain visual ada di `DESIGN.md`.

---

## ☕ Dukung & Donasi

Jika proyek ini bermanfaat bagi Anda, dukung pengembangan proyek ini melalui **QRIS**:

<p align="center">
  <img src="docs/qris.png" width="240" alt="QRIS Donasi - RZ Printing" />
  <br>
  <em>Scan QRIS menggunakan BCA, Mandiri, BRI, GoPay, OVO, DANA, ShopeePay, atau mobile banking lainnya.</em>
</p>

---

## 📬 Kontak

Dikembangkan oleh **rizalahmaddd**:
- **WhatsApp**: [+62 857-7777-5477](https://wa.me/6285777775477)
- **GitHub**: [@rizalahmaddd](https://github.com/rizalahmaddd)
- **Lokasi**: Kota Malang, Jawa Timur, Indonesia
