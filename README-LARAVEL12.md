# Whmlab — Paket Source Target Laravel 12

Paket ini adalah hasil penyesuaian source dari `core.zip`, bukan aplikasi baru. Nama aplikasi awal `Whmlab` tetap menjadi nilai awal yang dapat diganti.

## Pengaturan nama, domain, dan layanan

1. Salin `.env.example` menjadi `.env`.
2. Atur `APP_NAME`, `APP_URL`, `APP_ENV`, `APP_DEBUG`, `APP_TIMEZONE`, `APP_LOCALE`, database, mail, cache, session, queue, dan `CRON_SECRET` sesuai server.
3. Jalankan `php artisan key:generate` hanya untuk instalasi baru. Jangan mengganti APP_KEY aplikasi produksi yang sudah berjalan tanpa rencana migrasi.
4. Untuk produksi, gunakan `APP_ENV=production`, `APP_DEBUG=false`, dan URL HTTPS.
5. Baca `CONFIGURATION.md` sebelum menjalankan aplikasi.

## Instalasi dependency Laravel 12

Prasyarat: PHP 8.3+, Composer 2.7+, ekstensi PHP yang dibutuhkan paket, dan database yang cocok dengan skema aplikasi.

```bash
composer update --with-all-dependencies
composer audit
cp .env.example .env
php artisan key:generate
php artisan optimize:clear
php artisan config:cache
php artisan route:list
php artisan test
```

Arsip asli mengunci Laravel 11.45.2, sehingga lockfile lama dan folder vendor lama sengaja tidak dibawa. Simpan `composer.lock` baru setelah dependency berhasil diselesaikan dan pengujian lulus.

## Cron dan endpoint cache

- Endpoint publik `/clear` dihapus. Jalankan `php artisan optimize:clear` lewat SSH/terminal tepercaya.
- Endpoint `/cron` sekarang memerlukan header `X-Cron-Secret` yang nilainya sama dengan `CRON_SECRET` di `.env`. Konfigurasikan cron eksternal agar dapat mengirim header tersebut. Tanpa header yang valid, endpoint akan membalas 403.

## Batasan penting

- `SECURITY-AUDIT.md` berisi temuan yang perlu ditindaklanjuti. Ada komponen aktivasi lisensi dan updater remote yang berprivilege tinggi; jangan gunakan sampai tujuan server dan integritas paket update diverifikasi.
- Arsip asli tidak menyertakan migration PHP. Jangan mengarang migration atau menjalankan `migrate:fresh` pada database yang berisi data. Dapatkan migration sumber atau dump skema resmi terlebih dahulu.
- Kode PHP lulus pemeriksaan sintaks pada file source yang diperiksa. Composer dependency resolution, `composer audit`, `php artisan`, test suite, dan tes end-to-end belum dijalankan di lingkungan ini.
