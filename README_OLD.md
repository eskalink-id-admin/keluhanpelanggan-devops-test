## **Petunjuk Pengunaan**
Proyek ini menggunakan framework [Laravel 9](https://laravel.com/docs/9.x/deployment#server-requirements) yang siap digunakan untuk keperluan tes keahlian web developer

### **Software pendukung yang wajib disiapkan sebelum memulai**

- Git minimal versi 2 atau versi terbaru
- PHP minimal versi 8.0.2 atau versi terbaru
- Composer
- NodeJS minimal versi 16 atau versi terbaru

#
## **Github Repository**
### **1. Fork Repository**

1. Login ke [https://github.com](https://github.com) menggunakan akun anda
2. Kunjungi repository yang sudah kami siapkan di url ini [https://github.com/eskalink-id/keluhanpelanggan-testdev](https://github.com/eskalink-id/keluhanpelanggan-testdev)
3. Fork repository kami, dengan cara klik tombol Fork, seperti pada gambar ini:
![image](https://user-images.githubusercontent.com/116535942/197926492-abaa21fd-752d-47e4-b8bf-f230e7efe99c.png)



### **2. Clone Repository**

#### **- via Git Bash (Windows) atau Terminal (Linux)**
- Clone melalui dafar repository anda (https://github.com/**yourusername**/keluhanpelanggan-testdev.git
  ```
  git clone https://github.com/yourusername/keluhanpelanggan-testdev.git
  ```
- Pada Command line arahkan ke folder keluhanpelanggan
  ```
  cd keluhanpelanggan-testdev
  ```
- Kemudian lakukan instalasi laravel dan npm, seperti **Panduan Instalasi** dibawah ini

#
### **3. Push Repository**
- Sebelum push repository pastikan kamu membuat access token, pada menu developer settings, seperti pada gambar ini:
![image](https://user-images.githubusercontent.com/116535942/197929940-6f9c0f49-0a88-4982-8ef7-4983696b42b4.png)
- Tekan tombol Generate token yang ada dibawah 
- Push source code ke repository kamu
  ```
  git add .
  git commit -a -m "Commit source code tes"
  git push -u origin master
  
  Username for 'https://github.com':
  Password for 'https://your-username@github.com':

  Catatan: masukkan access token yang digenerate sebagai pengganti password:
  ```

  atau ketika muncul pop up seperti pada gambar berikut ini, pilih token lalu lakukan copy lalu paste token github anda:

  ![image](https://user-images.githubusercontent.com/116535942/197950844-9ce4ac36-dc9e-4ab9-bbd0-1844c5fa99f2.png)


#

***Lakukan langkah ke 4 berikut ini, setelah anda menyelesaikan soal tes***
### **4. Pull Request ke Repository (eskalink-id/keluhanpelanggan-testdev)**
Buka browser kemudian pilih repository **keluhanpelanggan-testdev** yang tadi anda Fork 
1. Klik tab **Pull Request** dilayar github anda
2. Tekan tombol **New Pull Request**
2. **Pilih branch sesuai nomor Tes, lihat pada gambar berikut:**
![image](https://user-images.githubusercontent.com/116535942/197924468-99369a22-c144-47cc-a0d0-f866d56acef2.png)

3. **Tekan tombol Create Pull Request, kemudian isi pesannya**
3. **Tekan tombol Create Pull Request**
4. **Jika berhasil akan muncul informasi pull request pada repository eskalink-id/keluhanpelanggan-testdev**
![image](https://user-images.githubusercontent.com/116535942/197925581-17a566fb-9545-4690-84aa-631d5ee961eb.png)


----


### **Panduan Instalasi**

- Buka Command Line arahkan ke folder ***keluhanpelanggan-testdev***
- kemudian jalankan perintah:
  ```
  composer install
  ```
- selanjutnya, jalankan perintah:
  ```
  php -r "copy('.env.example', '.env');";
  ```
- selanjutnya, jalankan perintah:
  ```
  php artisan key:generate
  ```
- selanjutnya, buat database dengan nama ***keluhan_pelanggan***
- selanjutnya, sesuaikan parameter pada file **.env**, seperti berikut:
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=keluhan_pelanggan
  DB_USERNAME=root
  DB_PASSWORD=
  ```
- selanjutnya, jalankan perintah:
  ```
  php artisan migrate
  ```
- selanjutnya, jalankan perintah berikut untuk compiled assets (development):
  ```
  - npm install
  - npm run dev
  ```
- selanjutnya, buka command line yang baru, jalankan perintah:
  ```
  php artisan serve
  ```

- selanjutnya, buka browser dengan url [http://localhost:8000](http://localhost:8000)
- selanjutnya, klik link **Register** dan lengkapi isian pada form register
- Selamat melanjutkan Tes, dengan membaca ketentuan pada **Soal Tes Keahlian**

----

## **Soal Tes Keahlian**

1. Pada halaman Keluhan Pelanggan buat fungsi create, read, update dan delete (CRUD)menggunakan axios atau ajax

2. Buat Model dengan nama **KeluhanPelanggan** serta migrationnya, dengan struktur table seperti berikut:
   ```
   Nama Tabel: keluhan_pelanggan
   Struktur Tabel:
   --------------------------------------------------------------
   | Column Name    | Type Data | Length | Note                 |
   --------------------------------------------------------------
   | id             | string    | 20     | not null primary key |
   | nama           | string    | 50     | not null             |
   | email          | string    | 20     | not null             |
   | nomor_hp       | integer   |        | null                 |
   | flag_aktif     | boolean   |        | default true         |
   | status_keluhan | varchar   | 1      | not null default 'O' |
   | keluhan        | text      |        | not null             |
   --------------------------------------------------------------
   ```

3. Buat Controller  dengan nama KeluhanPelangganController untuk proses CRUD
4. Ketika melakukan create dan update data berikan validasi sesuai dengan atribute table
5. Buat command scheduller : 
   ```
   - Menghapus keluhan jika flag_aktif bernilai false dan status_keluhan bernilai O (O = Open)
   - Untuk mengubah nilai status_keluhan menjadi C (C = Close) jika sudah lewat 1 hari sejak keluhan dibuat
   ```

**Selamat mengerjakan, semoga sukses**

#### **Jangan lupa melakukan pull request ke repository eskalink-id/keluhanpelanggan-testdev**
