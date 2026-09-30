# Warga Update

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

Optional sample data (obviously fake names, dev only): `php artisan db:seed --class=DemoSeeder`.

Change the default admin password right after the first login (Pengaturan → Ganti kata sandi).

## REST API (mobile app)

- Base URL: `/api/v1`. Always send `Accept: application/json`.
- Resident endpoints are public. Admin endpoints live under `/api/v1/admin/*` and need a Sanctum token from `POST /api/v1/auth/login` (`email`, `password`, `device_name`), sent as `Authorization: Bearer <token>`.
- Errors: `422` validation (`errors` per field), `409` business rule conflict (e.g. deleting a house that already has payments), `401` missing/invalid token.
- Updates that upload files (posts, bank accounts) must be sent as `POST` multipart with `_method=PUT`.
- Rate limits: login 6/min, payment proof submission 10/min.

### Documentation

Interactive docs (Scalar) are at **`/docs/api`**, rendered from the hand-written OpenAPI spec in `resources/openapi/v1.yaml`. Update that file whenever an endpoint changes. By default the docs page is open to everyone; restrict it by defining the `viewScalar` gate in `AppServiceProvider` if needed.

## Code layout

- Web controllers: `app/Http/Controllers` (+ `Admin/`). API controllers: `app/Http/Controllers/Api/V1` (+ `Admin/`).
- Validation shared by web and API: `app/Http/Requests`. JSON shapes: `app/Http/Resources`.
- Payment rules shared by web and API: `app/Support` (`PaymentSubmitter`, `PaymentRecorder`, `SubmissionReview`, `DuesLedger`, `HomeFeed`).

## Notes

- Design direction lives in `DESIGN.md`.
- Tests: `php artisan test`.
