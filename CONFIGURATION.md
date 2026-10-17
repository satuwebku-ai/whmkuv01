# Konfigurasi Aplikasi

Konfigurasi server dan rahasia aplikasi dilakukan melalui file `.env`, bukan dengan mengedit file PHP. Mulai dari template:

```bash
cp .env.example .env
php artisan key:generate
```

Edit nilai berikut sesuai lingkungan Anda:

```dotenv
APP_NAME="Nama Aplikasi Anda"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.example
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD="password_database"

MAIL_MAILER=smtp
MAIL_HOST=mail.domain-anda.example
MAIL_PORT=587
MAIL_USERNAME=akun_email
MAIL_PASSWORD="password_email"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@domain-anda.example
MAIL_FROM_NAME="${APP_NAME}"

# Wajib diisi dengan nilai acak yang panjang sebelum route /cron digunakan.
CRON_SECRET="ganti-dengan-random-secret-panjang"
```

## Keterangan

- `APP_NAME`: nama yang dibaca oleh `config('app.name')`; beberapa label/branding lain mungkin berasal dari pengaturan aplikasi di database.
- `APP_URL`: URL dasar yang digunakan Laravel untuk membangun URL dari CLI dan notifikasi. Gunakan URL kanonis dengan HTTPS pada produksi.
- `APP_ENV=production` dan `APP_DEBUG=false`: wajib untuk produksi agar detail exception tidak dibuka kepada pengunjung.
- `APP_TIMEZONE`: zona waktu PHP/Laravel. Sesuaikan dengan lokasi server/aplikasi.
- `DB_*`, `MAIL_*`, `CACHE_*`, `SESSION_*`, `QUEUE_*`, dan `REDIS_*`: sesuaikan dengan layanan yang benar-benar dipakai.
- `CRON_SECRET`: rahasia untuk endpoint scheduler. Pemanggil harus mengirim header `X-Cron-Secret: <nilai-CRON_SECRET>`. Jangan taruh secret di URL atau commit file `.env`.
- Jangan mengganti `APP_KEY` pada instalasi yang sudah berjalan tanpa rencana migrasi; hal itu dapat membuat data terenkripsi/session lama tidak dapat dibaca.

Sesudah mengubah konfigurasi pada server, jalankan:

```bash
php artisan optimize:clear
php artisan config:cache
```

Jalankan perintah di atas melalui terminal/SSH tepercaya, bukan endpoint HTTP publik.
