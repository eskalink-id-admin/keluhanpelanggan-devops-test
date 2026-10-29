# Technical Test DevOps — Mini CI/CD

Repository ini menyediakan aplikasi Laravel 12 dengan PHP 8.4 untuk menguji kemampuan
kandidat membangun pipeline CI/CD sampai deployment QA, Production, dan rollback.
Fitur aplikasi sudah tersedia; kandidat berfokus pada otomasi delivery aplikasi.

## Ruang lingkup

Buat pipeline dengan tahapan validasi, test, build, packaging, deployment, dan
verifikasi. Gunakan satu platform CI/CD yang dikuasai, seperti Jenkins, GitLab CI/CD,
atau GitHub Actions. Deployment boleh menggunakan Docker maupun langsung ke server.

Dua environment boleh dijalankan pada satu mesin dengan port/container terpisah.
Tidak wajib menggunakan cloud berbayar, Kubernetes, atau tools tambahan yang rumit.

## Pembagian peran dan branch

Penguji berperan sebagai developer: menyiapkan branch, perubahan aplikasi, push,
serta PR/MR dan merge saat demonstrasi. Kandidat tidak perlu membuat branch fitur
atau mengembangkan fitur bisnis. Kandidat menyiapkan konfigurasi CI/CD dan script
pendukung yang diperlukan.

| Branch | Pemicu | Hasil yang diharapkan |
| --- | --- | --- |
| `feature/*` | Push atau pembaruan PR/MR ke `qa-master` | Validasi dan test; tanpa deployment |
| `qa-master` | Perubahan digabungkan ke `qa-master` | Test/build berhasil, lalu deployment QA otomatis |
| `master` | Perubahan digabungkan ke `master` | Test/build berhasil, lalu deployment Production melalui approval/manual |

Penguji menyiapkan `master` sebagai baseline versi `1.0.0`, membuat `qa-master` dari
`master`, lalu menyiapkan perubahan pada `feature/banner` dari `qa-master`.
Branch selain `master` tidak boleh melakukan deployment ke Production.

QA dan Production harus memiliki konfigurasi, database, storage, dan alamat akses
terpisah. Pertahankan `APP_KEY` pada setiap environment. Gunakan `APP_ENV=qa` untuk
QA serta `APP_ENV=production` dan `APP_DEBUG=false` untuk Production.

## Perubahan rilis 1.1.0

Branch `qa-master` menambahkan ringkasan total catatan milik pengguna pada dashboard,
pesan rilis baru, dan pemetaan branch QA. Tidak ada perubahan schema database.
Saat rollback ke baseline `1.0.0`, ringkasan baru hilang dan data catatan tetap tersedia.

## Fitur aplikasi yang disediakan

- Registrasi, login, dan logout.
- Dashboard `/deployment` dengan badge environment, versi, branch, commit, dan build.
- Catatan per pengguna untuk memverifikasi data setelah deployment dan rollback.
- Perintah `release:stamp` untuk mencatat identitas artifact.
- Simulasi rilis bermasalah yang membuat readiness gagal.

| Endpoint | Fungsi |
| --- | --- |
| `GET /health` | Liveness; HTTP 200 ketika aplikasi hidup |
| `GET /ready` | Readiness; HTTP 200 jika siap, HTTP 503 jika pemeriksaan gagal |
| `GET /version` | Identitas rilis dan environment aktif |

Gunakan `/ready` sebagai pemeriksaan setelah deployment. Registrasi, login, dan
aset frontend tetap perlu diperiksa saat demo.

## Menjalankan aplikasi

Prasyarat: PHP 8.4 beserta ekstensi Laravel dan driver PDO, Composer 2, Node.js/npm,
serta MySQL atau SQLite. Pengujian otomatis membutuhkan ekstensi `pdo_sqlite`.
Pastikan perintah `php` menggunakan PHP 8.4; jika menggunakan alias `php84`, sesuaikan
perintah di bawah.

Untuk instalasi baru:

```sh
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
```

Atur koneksi database dan `APP_URL` di `.env`, lalu jalankan:

```sh
php artisan migrate
npm ci
npm run build
php artisan serve
```

Buka `/register`, login, lalu akses `/deployment`. Buat catatan sebelum melakukan
pengujian deployment. Panduan untuk database lama dan detail konfigurasi tersedia
pada [Deployment Lab](docs/DEVOPS-LAB.md).

## Validasi dan packaging

```sh
composer validate --strict
composer check-platform-reqs
php artisan config:clear
php artisan test
npm run build
```

Test memakai `.env.testing` dan SQLite in-memory. Suite berhenti sebelum migration jika
config cache masih aktif atau konfigurasi database testing tidak sesuai.
Kegagalan test harus menghentikan
pipeline sebelum deployment.

Saat build, isi identitas rilis menggunakan metadata CI:

```sh
php artisan release:stamp qa-master COMMIT_SHA BUILD_NUMBER
```

Ganti `qa-master` sesuai branch sumber. Sertakan `release.json` yang dihasilkan dalam
artifact/image bersama source aplikasi, dependensi runtime, dan `public/build`.
Jangan sertakan `.env`, secret, database, `public/hot`, atau config cache mesin build.
Bangun config cache menggunakan konfigurasi server tujuan.

## Skenario demonstrasi

1. **Baseline:** deploy versi `1.0.0`, daftar akun, dan simpan catatan.
2. **Feature ke QA:** penguji menyediakan perubahan versi/pesan pada `feature/banner`,
   lalu merge ke `qa-master`. Tunjukkan pipeline dan deployment QA otomatis.
3. **QA ke Production:** penguji merge ke `master`. Tunjukkan approval/manual sebelum
   deployment Production dan identitas artifact yang aktif.
4. **Test gagal:** penguji menyediakan perubahan versi tidak valid. Pipeline harus
   gagal tanpa mengganti versi aplikasi yang sedang aktif.
5. **Readiness gagal:** penguji menyediakan rilis dengan simulasi kegagalan. Pipeline
   harus mendeteksi HTTP 503 dan mempertahankan atau memulihkan rilis sehat sebelumnya.
6. **Rollback setelah sukses:** jalankan satu job/perintah rollback ke artifact sukses
   sebelumnya. Versi kembali, readiness 200, login berhasil, dan catatan tetap tersedia.

Rollback harus memakai artifact yang sudah tersedia tanpa membangun ulang source.
Jangan mereset database atau menggunakan `migrate:fresh`. Rollback database otomatis
bukan persyaratan; jelaskan penanganan migration yang tidak kompatibel.

Detail simulasi dan perintah tersedia di [docs/DEVOPS-LAB.md](docs/DEVOPS-LAB.md).

## Output yang dikumpulkan

- Konfigurasi pipeline beserta script deployment dan rollback.
- Dockerfile/Compose jika digunakan dan contoh konfigurasi tanpa secret.
- Dokumentasi prasyarat, arsitektur singkat, variabel konfigurasi, serta cara deploy
  dan rollback yang dapat diulang oleh penguji.
- Bukti eksekusi seluruh skenario berupa log, tautan build, atau rekaman demo.
- Penjelasan singkat asumsi, keterbatasan, dan strategi migration database.

Penilaian berfokus pada pipeline yang berjalan, pemetaan branch/environment yang
benar, deployment yang dapat ditelusuri, pemulihan kegagalan, data persisten,
dan dokumentasi yang jelas.
