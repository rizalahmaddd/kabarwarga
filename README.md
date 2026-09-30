# Kabar Warga

Neighbourhood notice board and dues ledger for an RT/RW, built with Laravel 13 + Blade + Tailwind (no Filament), with a REST API for the mobile app.

- Residents (no login): announcements, news, activities, dues status per house, cash book, and online payment: upload a transfer proof, then check its status with the submission code.
- Pengurus (login at `/masuk`): record dues payments (several months at once), review transfer proofs (approve fully or partially, reject, undo), write posts, manage houses, dues types, expenses, bank accounts/QRIS, settings, and other admin accounts.

## Setup (development, SQLite)

```sh
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # creates the admin from ADMIN_* in .env
php artisan storage:link            # needed for post photos and QRIS images
npm run build                       # or `npm run dev` while editing views
php artisan serve
```

Optional realistic demo data (made-up names, dev only): `php artisan migrate:fresh --seeder=DemoSeeder`.

Change the default admin password right after the first login (Pengaturan → Ganti kata sandi).

## REST API (mobile app)

- Base URL: `/api/v1`. Always send `Accept: application/json`.
- Resident endpoints are public. Admin endpoints live under `/api/v1/admin/*` (plus `/auth/me` and `/auth/logout`) and need a Sanctum token from `POST /api/v1/auth/login` (`email`, `password`, `device_name`), sent as `Authorization: Bearer <token>`.
- Money is an integer in Rupiah. Monthly periods use `YYYY-MM`; one-off dues have `period: null`.
- Updates that upload files (posts, bank accounts) must be sent as `POST` multipart with `_method=PUT`. Boolean flags left out of an update (`is_active`, `is_pinned`, `is_popup`) are treated as `false`, same as the web forms.
- Errors: `401` missing/invalid token, `404` not found, `409` business rule conflict (e.g. deleting a house that already has payments), `422` validation (`errors` per field), `429` rate limited.
- Rate limits: login 6/min, payment proof submission 10/min.

| Area | Endpoints |
|---|---|
| Residents | `GET /home`, `/settings`, `/posts`, `/posts/{slug}`, `/households`, `/dues-types`, `/bank-accounts`, `/dues/ledger`, `/dues/cashbook`, `/households/{household}/dues/{duesType}` |
| Payment proofs | `POST /payment-submissions`, `GET /payment-submissions/{code}` |
| Auth | `POST /auth/login`, `GET /auth/me`, `POST /auth/logout` |
| Admin | `payments`, `payment-submissions` (approve / reject / cancel / reopen, proof file), `posts`, `households`, `dues-types`, `expenses`, `bank-accounts`, `settings`, `password`, `users` |

### Documentation

Interactive docs (Scalar) are at **`/docs/api`**, rendered from the hand-written OpenAPI spec in `resources/openapi/v1.yaml`. The descriptions in the spec are written in Indonesian for the mobile developers; keep new endpoints in the same language. Update that file whenever an endpoint changes. By default the docs page is open to everyone; restrict it by defining the `viewScalar` gate in `AppServiceProvider` if needed.

## Code layout

- Web controllers: `app/Http/Controllers` (+ `Admin/`). API controllers: `app/Http/Controllers/Api/V1` (+ `Admin/`).
- Validation shared by web and API: `app/Http/Requests`. JSON shapes: `app/Http/Resources`.
- Payment rules shared by web and API: `app/Support` (`PaymentSubmitter`, `PaymentRecorder`, `SubmissionReview`, `DuesLedger`, `HomeFeed`).

## Notes

- Design direction lives in `DESIGN.md`.
- Tests: `php artisan test` (API coverage in `tests/Feature/PublicApiTest.php` and `tests/Feature/AdminApiTest.php`).

## TODO

- [x] REST API `/api/v1` for residents and pengurus
- [x] OpenAPI spec + Scalar docs at `/docs/api`
- [ ] Mobile app built on the API
  - [ ] Resident side: home feed, posts, dues status per house, cash book
  - [ ] Online payment: pick house, dues type, months, and destination account, upload transfer proof (image or PDF, max 5 MB), check status by submission code
  - [ ] Pengurus login (Sanctum token stored securely on the device) and logout
  - [ ] Pengurus side: record payments, review transfer proofs, manage posts, houses, dues types, expenses, bank accounts/QRIS, settings, users
  - [ ] Handle `401` (back to login), `409`, `422`, and `429` responses consistently
