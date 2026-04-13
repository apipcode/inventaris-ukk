Viewed DatabaseSeeder.php:1-34

cara menjalankan project 

fix 
 fitur Export CSV dan Update Stok), -> urutan command:

1. Persiapan Awal (di environment baru)
 1. Install semua library PHP
composer install

 2. Install semua library Javascript
npm install

 3. Buat file konvergensi lingkungan (jika belum ada)
cp .env.example .env

 4. Generate kunci keamanan
php artisan key:generate

 5. Jalankan migrasi database
php artisan migrate --seed

 6. Hubungkan storage (WAJIB agar file Export bisa didownload)
php artisan storage:link
```
Note: Perintah `migrate --seed` akan membuat akun default:
*   Admin: `admin@admin.com` (Pasword: `password`)
*   Staff: `staff@staff.com` (Pasword: `password`)

---

2. Cara Menjalankan Project (PENTING)
Dalam project ini, fitur Export CSV Filament berjalan di latar belakang (background queue). Jika Anda hanya menjalankan `php artisan serve`, fitur export mungkin akan macet di status "Pending".

Gunakan perintah ini untuk menjalankan semuanya sekaligus (Server + Vite + Queue Worker):
```bash
npm run dev
```
Perintah `npm run dev` di project ini sudah saya konfigurasi untuk menjalankan:
1.  Server Laravel (`php artisan serve`)
2.  Vite (untuk tampilan UI)
3.  Queue Listener (untuk memproses Export CSV di background)
4.  Pail (untuk memantau log error secara real-time)

---

 3. Tips Tambahan Agar Tidak Error
-   Akses Dashboard: Buka browser di alamat `http://127.0.0.1:8000/admin`.
-   Jangan Matikan Terminal: Pastikan terminal tempat Anda menjalankan `npm run dev` tetap terbuka saat Anda mencoba fitur Export.
-   Bersihkan Cache (Jika ada perubahan tampilan yang tidak muncul):
    ```bash
    php artisan optimize:clear
    ```

wajib `npm run dev`, agar seluruh sistem sinkronisasi stok dan proses export yang baru diperbaiki akan berjalan secara otomatis dan lancar.