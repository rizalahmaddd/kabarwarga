# Warga Update

Neighbourhood notice board and dues ledger for an RT/RW, built with Laravel 13 + Blade + Tailwind (no Filament).

- Residents (no login): announcements, news, activities, dues status per house, cash book.
- Pengurus (login at `/masuk`): record dues payments (several months at once), write posts, manage houses, dues types, expenses, settings, and other admin accounts.

## Setup (development, SQLite)

```sh
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # creates the admin from ADMIN_* in .env
php artisan storage:link            # needed for post photos
npm run build                       # or `npm run dev` while editing views
php artisan serve
```

Optional sample data (obviously fake names, dev only): `php artisan db:seed --class=DemoSeeder`.

Change the default admin password right after the first login (Pengaturan → Ganti kata sandi).

## Notes

- Design direction lives in `DESIGN.md`.
- Tests: `php artisan test`.
