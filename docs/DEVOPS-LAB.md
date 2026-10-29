# Deployment Lab

Aplikasi Laravel 12 / PHP 8.4 untuk technical test DevOps. Kandidat mengerjakan
pipeline, packaging, deployment, dan rollback. Registrasi/login, dashboard rilis,
data uji persisten, dan probe sudah disediakan. Tidak diperlukan Kubernetes atau cloud.

## Menjalankan aplikasi

Gunakan PHP 8.4, Composer 2, Node.js, serta MySQL atau SQLite. Setelah membuat `.env`
dari `.env.example`, atur koneksi database untuk mesin Anda.

```sh
composer install
npm ci
npm run build
php artisan key:generate
php artisan migrate
php artisan serve
```

Buka `/register`, lalu `/deployment`. Menu **Deployment Lab** juga tersedia setelah
login. Catatan hanya dapat dilihat/dihapus pemiliknya. Buat catatan sebagai bukti data
tidak hilang ketika mengganti artifact. Migration baru bersifat tambahan dan tidak
mengubah tabel pengguna yang sudah ada.

Untuk instalasi yang sudah berjalan, cukup jalankan `php artisan migrate` dan
`npm run build`. Jangan mengganti `APP_KEY` yang sudah digunakan.

Jika database lama sudah memiliki tabel tetapi riwayat migration tidak sesuai,
jangan memakai `migrate:fresh`. Periksa `php artisan migrate:status` dan rekonsiliasi
riwayat berdasarkan schema yang benar. Untuk menambahkan fitur lab pada database
tersebut tanpa menjalankan ulang migration lama, gunakan:

```sh
php artisan migrate --path=database/migrations/2026_10_29_000000_create_deployment_notes_table.php
```

Instalasi baru untuk kandidat tetap menjalankan seluruh migration secara normal.

## Branch dan environment

| Branch sumber | Environment target | Perilaku pipeline yang dibuat kandidat |
| --- | --- | --- |
| `feature/*` | Tidak wajib deploy; opsional `preview` | Validasi dan test pada PR/MR ke `qa` |
| `qa` | `APP_ENV=qa` | Deploy otomatis setelah test/build berhasil |
| `master` | `APP_ENV=production` | Deploy dengan approval/manual |

Gunakan database, storage, URL, secret, dan config cache terpisah untuk QA dan
Production. Pertahankan `APP_KEY` yang stabil untuk setiap environment. Production
menggunakan `APP_DEBUG=false`. Satu mesin dengan port/container terpisah diperbolehkan.

Dashboard menampilkan environment aktual dan metadata branch sumber sebagai dua hal
berbeda. Warna badge: QA kuning, Production merah, environment lain biru. Jika target
branch berbeda dengan environment aktual, dashboard menampilkan peringatan.
Pemetaan ini bersifat informasi; pembatasan deploy dan approval merupakan tanggung
jawab pipeline. Aplikasi tidak menjalankan Git atau memilih server berdasarkan branch.

## Identitas artifact

Saat build, jalankan perintah berikut menggunakan metadata dari CI:

```sh
php artisan release:stamp qa COMMIT_SHA BUILD_NUMBER
```

Contoh lain:

```sh
php artisan release:stamp feature/banner abc123 feature-12
php artisan release:stamp master abc123 production-15 --release-version=1.1.0
```

Perintah membuat `release.json` di root proyek. Berkas ini tidak di-commit, tetapi
**wajib disertakan di artifact/image**. Jangan membuat ulang metadata tersebut pada
server ketika melakukan rollback. Tanpa stamping, aplikasi menampilkan branch/build
`local` dan commit `untracked`.

Versi dan pesan default ada di `config/release.php`. `--release-version` opsional;
metadata artifact mengalahkan versi default tersebut. Tidak perlu membuat Git branch
secara otomatis di aplikasi: kandidat membuat branch melalui workflow Git-nya sendiri.

Urutan build: install dependensi, test, build aset, stamp metadata, package. Dependensi
runtime dapat dipasang dengan `composer install --no-dev --prefer-dist --optimize-autoloader`.
Artifact mencakup source aplikasi, `vendor`, `public/build`, dan `release.json`.
Jangan sertakan `.env`, `public/hot`, database, config cache mesin build, atau secret.

Jalankan `php artisan config:cache` setelah konfigurasi environment tujuan tersedia.
Jika stamping dilakukan setelah config cache dibuat, cache harus dibangun ulang.

## Probe untuk pipeline

| Endpoint GET | Respons | Tujuan |
| --- | --- | --- |
| `/health` | HTTP 200, `status=ok` | Proses aplikasi hidup; tidak memeriksa database |
| `/ready` | HTTP 200 atau 503 | Database terhubung, tabel dasar tersedia, storage dapat ditulis, rilis tidak disimulasikan rusak |
| `/version` | HTTP 200 | Versi, pesan, branch, commit, build, environment aktual, target branch |

Ketiganya tidak membutuhkan login/session, menggunakan `Cache-Control: no-store`, dan
tidak mengembalikan secret atau detail exception database. `/ready` bukan pengganti
uji end-to-end: aset frontend, registrasi, dan login tetap diperiksa saat demo.

```sh
curl --fail http://localhost:8000/ready
curl --fail http://localhost:8000/version
```

Gunakan **`/ready` sebagai gate setelah deploy**, bukan `/health` saja. Verifikasi
commit/build yang dikembalikan `/version` terhadap artifact yang dipilih.

## Latihan feature, gagal test, dan rollback

1. Baseline: deploy versi `1.0.0`, daftar akun, dan simpan catatan di `/deployment`.
2. Buat `feature/banner`. Ubah `version` menjadi `1.1.0` dan `message` di
   `config/release.php`. Jalankan test, ajukan PR/MR ke `qa`, lalu promosikan ke `master`.
   Dashboard harus menampilkan pesan/versi baru beserta branch/commit/build yang sesuai.
3. Gagal CI: pada branch latihan, ubah versi menjadi `invalid`. Test validasi versi
   harus gagal dan tidak boleh ada deployment. Kembalikan perubahan sebelum melanjutkan.
4. Gagal readiness: buat rilis baru dengan `simulate_failure => true` di
   `config/release.php`. Package sebagai artifact baru. `/health` tetap 200, `/ready`
   menjadi 503. Pipeline harus mempertahankan/mengembalikan rilis baik sebelumnya.
5. Insiden setelah deploy sukses: deploy versi sehat baru, lalu jalankan mekanisme
   rollback kandidat ke artifact sukses sebelumnya. Verifikasi versi/commit/build
   kembali, readiness 200, akun dapat login, dan catatan masih tersedia.

Simulasi kegagalan mengikuti source rilis, bukan flag `.env` yang terus aktif setelah
rollback. Tidak ada endpoint publik untuk mengubah status kegagalan. Jangan membangun
ulang source saat rollback, mereset database, atau menjalankan `migrate:fresh`.
Rollback aplikasi tidak otomatis berarti rollback schema; kandidat menjelaskan
strategi migration yang kompatibel. Semua rilis latihan dimulai dari baseline yang
sudah memiliki migration catatan.

## Pengujian

```sh
composer validate --strict
composer check-platform-reqs
php artisan test
npm run build
```

Test menggunakan `.env.testing` dan SQLite in-memory, bukan database QA/Production.
Suite mencakup autentikasi, metadata branch, health/readiness, simulasi kegagalan,
validasi catatan, isolasi pemilik, dan data saat identitas rilis berubah. Bukti rollback
antarartifact sesungguhnya tetap harus diberikan kandidat; test aplikasi hanya
memverifikasi perilaku aplikasi. Build frontend lama dapat menampilkan warning Sass
dari dependensi Bootstrap/Vite; status keluar proses tetap menjadi acuan build.

Pipeline dan script deployment/rollback sengaja belum disediakan karena merupakan
bagian yang dinilai dalam technical test.
