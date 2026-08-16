# POS Apotek Restock SAW

Sistem skripsi berbasis arsitektur terpisah:

- `backend`: Laravel 12 RESTful JSON API, MySQL, Sanctum SPA cookie auth, Clean Architecture layers, Policy, API Resource, dan Form Request.
- `frontend`: Vue 3 SPA, Vite, Vue Router, Axios, Pinia, Tailwind CSS.
- `docs`: ERD, arsitektur, dan dokumentasi endpoint API.

Judul penelitian:

> Implementasi Metode Simple Additive Weighting (SAW) untuk Penentuan Prioritas Restock Obat pada Sistem POS Apotek

## Struktur

```text
backend/
  app/Application/UseCases
  app/Domain/Contracts
  app/Http/Controllers/Api
  app/Http/Requests
  app/Http/Resources
  app/Infrastructure/Persistence
  app/Models
  app/Policies
  database/migrations
  database/factories
  database/seeders
  routes/api.php
  tests
frontend/
  src/api
  src/components
  src/layouts
  src/pages
  src/router
  src/stores
docs/
  architecture.md
  erd.md
  api.md
```

Modul utama:

- Auth berbasis Laravel Sanctum SPA cookie.
- User dan role Super Admin/Admin.
- Obat dan supplier.
- Faktur pembelian obat untuk arsip batch, expired, dan retur.
- Penjualan dan detail transaksi.
- Laporan harian, bulanan, tahunan.
- Ranking rekomendasi restock menggunakan metode SAW.

## Setup Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

## Setup Frontend

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

Login dummy:

- Email: `superadmin@apotek.test`
- Password: `password123`

Frontend menggunakan Sanctum cookie mode. Pastikan `.env` backend berisi:

```env
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
SESSION_DOMAIN=
SESSION_SAME_SITE=lax
```

## Testing

```bash
cd backend
php artisan test
```

Catatan: dependency Composer/NPM belum diunduh di workspace ini karena akses jaringan Composer memerlukan persetujuan.
