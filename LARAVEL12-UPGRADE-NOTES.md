# Catatan Upgrade Laravel 12

Paket ini menargetkan Laravel 12 dengan PHP `^8.3` berdasarkan `composer.json`.

## Yang diubah

- `laravel/framework` diarahkan ke `^12.0`.
- Paket development yang digunakan untuk tes/debug disesuaikan ke rentang versi yang ditujukan untuk Laravel 12.
- `barryvdh/laravel-dompdf` dinaikkan ke `^3.1` karena versi lama yang ada pada arsip menyatakan dukungan Illuminate hanya sampai Laravel 11.
- `composer.lock` lama tidak disertakan karena mengunci `laravel/framework v11.45.2`. Lockfile baru harus dibuat dari dependency solver di lingkungan build.
- Folder `vendor/` tidak disertakan. Dependency harus dipasang ulang; jangan memakai vendor Laravel 11 dari arsip asli.

## Langkah pemasangan / validasi

Gunakan PHP 8.3+ dan Composer 2.7+ di lingkungan staging:

```bash
composer validate
composer update --with-all-dependencies
composer audit
php artisan --version
php artisan about
php artisan route:list
php artisan test
```

Simpan `composer.lock` yang dihasilkan setelah semua paket berhasil diselesaikan dan tes berjalan. Jika `composer update` gagal, jangan lanjut deploy; simpan error lengkap dan selesaikan konflik paket satu per satu.

## Batasan yang diketahui

- Upgrade source-level belum dapat dinyatakan selesai/teruji end-to-end tanpa Composer yang berhasil menyelesaikan dependency Laravel 12 dan menjalankan test suite.
- Arsip `core.zip` tidak berisi migration PHP di `database/migrations`; instalasi baru dan audit relasi database membutuhkan migration sumber atau dump skema resmi.
- Integrasi pembayaran, registrar, dan panel hosting memerlukan tes sandbox dengan kredensial baru di staging.
