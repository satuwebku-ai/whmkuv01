# Audit Keamanan Statis — core.zip

Tanggal pemeriksaan: 2026-10-15  
Cakupan: source code yang ada di `app/`, `bootstrap/`, `config/`, `routes/`, serta konfigurasi Composer. Ini audit statis, bukan penetration test. Tidak ada koneksi ke database, gateway pembayaran, registrar, atau server hosting.

## Ringkasan temuan

| ID | Prioritas | Temuan | Tindakan/status |
|---|---|---|---|
| SEC-01 | Kritis | `routes/web.php` memiliki `GET /clear` yang memanggil `Artisan::call('optimize:clear')` tanpa autentikasi. | Dihapus dari route publik. Bersihkan route cache saat deploy. |
| SEC-02 | Tinggi | `GET /cron` menjalankan scheduler dan dapat memicu action/URL cron yang disimpan. | Ditambahkan middleware `cron.secret`; wajib mengirim `X-Cron-Secret`. Konfigurasi `CRON_SECRET` yang kosong akan menolak semua permintaan. Uji kompatibilitas dengan cron eksternal sebelum produksi. |
| SEC-03 | Tinggi | `core.zip` berisi `.env` produksi. | File `.env` tidak disertakan di paket hasil. Jika rahasia di dalamnya asli dan pernah tersebar, rotasi password database, purchase/license code, dan kredensial terkait. |
| SEC-04 | Tinggi | `composer.lock` asli mengunci Laravel `v11.45.2`; composer.json lama menyatakan `^11.0`. | Target composer diubah ke Laravel `^12.0`; lock lama dan vendor tidak disertakan. Dependency resolution dan tes Laravel 12 masih harus dijalankan di lingkungan Composer. |
| SEC-05 | Tinggi | Tidak ditemukan file migration PHP pada `database/migrations` di arsip. | Tidak membuat migration tebakan. Minta migration sumber atau dump skema untuk validasi DB. |
| SEC-06 | Sedang | Logout admin dan pelanggan menggunakan GET di `routes/admin.php` dan `routes/user.php`. | Belum diubah karena perlu memperbarui semua form/menu pemanggil secara konsisten. Disarankan ubah menjadi POST dengan CSRF dan uji semua layout. |
| SEC-07 | Tinggi | `vendor/laramin/utility/src/VugiChugi.php` menyamarkan nama fungsi/URL dengan ROT13. `UtilityController::laraminSubmit()` mengirim purchase code, username, email, dan URL ke server lisensi; lalu menulis file `.env` ke lokasi yang diambil dari respons server (`$response->location`). | Ini adalah perilaku lisensi/aktivasi yang berisiko tinggi karena path tujuan dipengaruhi respons remote. Bukan bukti otomatis malware, tetapi jangan aktifkan route ini sebelum kode vendor dan tujuan server diverifikasi. Pertimbangkan menghapus/menonaktifkan aktivasi ini jika tidak diperlukan. |
| SEC-08 | Tinggi | `Admin\SystemController::systemUpdateProcess()` mengunduh arsip dari server update, mengekstraknya ke direktori di luar root aplikasi (`base_path('../')`), dan menjalankan SQL remote melalui `DB::unprepared()`. | Fitur updater yang didesain vendor, tetapi sangat berkuasa: kompromi server update/validasi file dapat berujung eksekusi kode dan SQL. Pastikan otorisasi admin kuat, validasi signature/hash arsip, dan backup sebelum menggunakannya. |
| SEC-09 | Sedang | Beberapa callback gateway memproses raw request (`php://input`) dan memakai cURL. | Bukan bukti berbahaya dengan sendirinya. Setiap gateway perlu dites untuk signature, nominal, status transaksi, idempotensi, timeout, dan logging tanpa rahasia. |
| SEC-10 | Sedang | Admin menyediakan fitur yang menulis file ke disk, misalnya pengaturan timezone, robots/sitemap/CSS, bahasa, serta update sistem. | Pastikan rute hanya dapat diakses role berwenang; validasi input, path, izin filesystem, CSRF, dan audit log perlu diuji. |
| SEC-11 | Rendah/perlu tinjauan | Ada `eval(...)` pada JavaScript di Blade untuk scroll positioning; tidak ditemukan pemanggilan PHP `eval()` dalam folder source utama. | Tidak terlihat seperti PHP webshell dari hasil pencarian ini, tetapi sebaiknya diganti dengan operasi JavaScript biasa. Compiled Blade cache dibersihkan dari paket. |

## Pemeriksaan pola kode mencurigakan

- Pencarian statis untuk `eval(` PHP, `base64_decode`, `gzinflate`, `str_rot13`, `shell_exec`, `passthru`, `system`, `assert`, `proc_open` dan pola serupa tidak menemukan pemanggilan PHP yang jelas-jelas mengobfuscate atau mengeksekusi payload di folder source utama.
- Ada penggunaan `proc_open` di klien Textmagic (`app/Notify/Textmagic/textmagic-cli.php`) untuk menjalankan command internal library. Itu perlu dibatasi/ditinjau jika skrip tersebut diekspos atau menerima input pengguna; bukan bukti malware sendirian.
- Ada pemanggilan jaringan ke provider pembayaran/registrar, Google, layanan notifikasi, dan layanan lisensi/update. Sebagian sesuai fungsi aplikasi. `VugiChugi.php` sengaja menyamarkan URL/fungsi dengan ROT13; tujuan yang didekode mencakup domain lisensi ViserLab. `SystemController::systemUpdateProcess()` mempercayai daftar URL file dari respons updater dan menjalankan SQL dari paket update. Perilaku ini bisa bagian dari updater resmi, tetapi harus diperlakukan sebagai jalur supply-chain berprivilege tinggi. Audit ini belum memverifikasi integritas server vendor.
- Dependency audit belum selesai. Jalankan `composer audit` setelah dependency Laravel 12 berhasil dipasang; jangan anggap hasil pencarian pola source menggantikan audit dependency.

## Checklist sebelum produksi

1. Buat `.env` baru dari `.env.example`; jangan mengembalikan `.env` lama.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` HTTPS, zona waktu, DB/mail/cache/session/queue, serta `CRON_SECRET` acak.
3. Rotasi rahasia yang ada di `.env` arsip jika itu kredensial aktif.
4. Jalankan `composer update --with-all-dependencies`, `composer audit`, `php artisan route:list`, `php artisan test`, dan smoke test pada staging.
5. Pastikan scheduler eksternal mengirim header `X-Cron-Secret`; jangan membuka endpoint cron tanpa secret.
6. Periksa semua callback pembayaran: tanda tangan, amount, currency, status, duplicate callback, dan transisi status order/invoice.
7. Jalankan tes role/permission, kepemilikan data pelanggan, upload/download, reset password, logout, dan perubahan pengaturan admin.
8. Pulihkan migration sumber/dump skema resmi sebelum mencoba instalasi database baru. Jangan menjalankan `migrate:fresh` pada database produksi.
9. Audit atau nonaktifkan route aktivasi lisensi dan updater otomatis bila tidak diperlukan; jangan gunakan updater sampai sumber file/SQL dan integritas respons dapat diverifikasi.
10. Pastikan route cache lama dibersihkan setelah deploy agar `/clear` yang dihapus tidak tetap tercatat dalam cache.

## Kesimpulan

Dalam pencarian statis ini tidak ditemukan bukti konklusif berupa webshell PHP, tetapi ditemukan risiko operasional nyata pada endpoint cache-clear dan scheduler serta integrasi lisensi/update yang perlu dipahami. Tidak mungkin menjamin aplikasi bebas kode berbahaya hanya dari grep/pemeriksaan statis. Laravel 12 baru dinyatakan terverifikasi setelah dependency solver berhasil, test suite berjalan, dan alur nyata diuji pada staging.


## Perubahan hardening setelah audit
- Menghapus dependensi `laramin/utility` dari `composer.json` dan menghapus hook `Onumoti`/middleware `VugiChugi` dari alur aplikasi.
- Menonaktifkan updater jarak jauh yang mengunduh ZIP dan mengeksekusi SQL tanpa verifikasi integritas yang memadai. Endpoint proses updater kini mengembalikan HTTP 410.
- Menonaktifkan pengambilan template dari server eksternal; `getTemplates()` kini mengembalikan `null`.
- Perubahan ini belum membuktikan aplikasi bebas malware dan belum diuji runtime; audit manual serta tes regresi tetap wajib.


## Security hardening performed (2026-10-16)
1. Removed `VugiChugi` middleware hook from route registration and removed `Onumoti` hooks from base/login controllers.
2. Disabled remote updater execution: no longer downloads arbitrary ZIPs from a remote response, extracts them into parent directories, or runs downloaded SQL through `DB::unprepared`. Update-process endpoint returns HTTP 410.
3. Disabled remote template listing/download integration; `getTemplates()` now returns `null`.
4. Removed `laramin/utility` from Composer requirements (if it was supplied transitively/externally, it is no longer directly required).
5. Removed original production `.env` from deliverable; use `.env.example` and create your own `.env`.

This is a targeted hardening pass, not a certification that the whole application is malware-free. A full review of every PHP/Blade/JS file, all Composer dependencies, and runtime behavior remains necessary.

## Perbaikan lanjutan (2026-10-16)
- Route penghapusan/pembacaan automation errors diubah dari GET menjadi POST; form konfirmasi memakai CSRF.
- Pengosongan keranjang memakai POST dan penghapusan item memakai DELETE; tombol di Blade diubah menjadi form CSRF.
- Redirect notifikasi admin dibatasi ke path relatif lokal atau URL dengan host yang sama; target tidak tepercaya kembali ke halaman notifikasi admin.
- Penulisan sitemap, robots, dan custom CSS diberi validasi ukuran/tipe string, target file tetap, pengecekan direktori, dan penulisan dengan LOCK_EX.
- Lookup geolokasi memakai HTTPS, timeout pendek, validasi IP publik, parser XML dengan LIBXML_NONET, dan gagal secara aman jika layanan tidak tersedia.
- Sintaks PHP file yang diubah dan JSON composer.json lolos pemeriksaan. Runtime/regression test belum dijalankan.
