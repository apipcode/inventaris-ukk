# 📦 Inventaris UKK

> Aplikasi manajemen inventaris barang berbasis web yang dibangun dengan **Laravel 13** dan **Filament v4**. Dikembangkan sebagai proyek Uji Kompetensi Keahlian (UKK).

[![Laravel](https://img.shields.io/badge/Laravel-13.x-red?logo=laravel)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-v4-orange?logo=filament)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 📋 Deskripsi Proyek

Sistem Inventaris UKK adalah aplikasi admin panel yang dirancang untuk memudahkan pengelolaan barang inventaris di suatu organisasi atau instansi. Fitur utama mencakup:

- ✅ Manajemen **kategori barang** dan **data stok** inventaris
- ✅ Pencatatan **transaksi peminjaman** dan **pengembalian barang**
- ✅ Pembaruan stok secara **otomatis** saat transaksi terjadi
- ✅ **Ekspor PDF** untuk semua jenis laporan data
- ✅ **Cetak struk** peminjaman per transaksi
- ✅ Sistem **role-based access control** (Admin & Staff)

---

## 🗂️ Struktur Menu

| Menu | Grup | Akses |
|------|------|-------|
| Kategori Barang | Master Data | Admin saja |
| Data Barang | Master Data | Admin saja |
| Peminjaman | Transaksi | Admin & Staff |
| Pengguna | Pengaturan | Admin & Staff |

---

## 👥 Role & Hak Akses

| Fitur | Admin | Staff |
|-------|:-----:|:-----:|
| Kelola Kategori Barang | ✅ | ❌ |
| Kelola Data Barang (Stok) | ✅ | ❌ |
| Lihat & Catat Peminjaman | ✅ | ✅ |
| Kembalikan Barang | ✅ | ✅ |
| Cetak Struk Peminjaman | ✅ | ✅ |
| Export PDF (semua modul) | ✅ | ✅ |
| Edit Data Peminjaman | ✅ | ❌ |
| Kelola Pengguna (Admin) | ✅ | ❌ |
| Kelola Sesama Staff | ✅ | ✅ |
| Ubah Role Pengguna | ✅ | ❌ |

---

## 🛠️ Tech Stack

| Teknologi | Versi | Fungsi |
|-----------|-------|--------|
| **PHP** | ^8.3 | Bahasa pemrograman backend |
| **Laravel** | ^13.0 | Framework PHP utama |
| **Filament** | v4 (`*`) | Admin panel & CRUD builder |
| **MySQL / SQLite** | - | Database relasional |
| **barryvdh/laravel-dompdf** | ^3.1 | Generasi file PDF |
| **Vite** | - | Bundler aset frontend |

---

## ⚙️ Cara Instalasi

### Prerequisites

Pastikan sudah terinstal:
- PHP 8.3+
- Composer
- Node.js & NPM
- MySQL (atau gunakan SQLite untuk pengembangan lokal)

### Langkah-langkah

**1. Clone repositori**
```bash
git clone https://github.com/apipcode/inventaris-ukk.git
cd inventaris-ukk
```

**2. Instal dependensi PHP**
```bash
composer install
```

**3. Salin file environment**
```bash
cp .env.example .env
```

**4. Generate application key**
```bash
php artisan key:generate
```

**5. Konfigurasi database di file `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventaris_ukk
DB_USERNAME=root
DB_PASSWORD=
```

> 💡 **Tips:** Untuk pengembangan cepat, gunakan SQLite dengan mengatur `DB_CONNECTION=sqlite` dan hapus baris `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

**6. Jalankan migrasi & seed data awal**
```bash
php artisan migrate --seed
```

**7. Instal dependensi frontend & build aset**
```bash
npm install
npm run build
```

**8. Jalankan server pengembangan**
```bash
composer run dev
```

Atau secara terpisah:
```bash
php artisan serve
```

**9. Buka di browser**

Akses panel admin di: **[http://localhost:8000/admin](http://localhost:8000/admin)**

---

## 🔑 Akun Default (Seeder)

> ⚠️ **Penting:** Segera ubah password ini setelah pertama kali login di environment production!

| Role | Email | Password |
|------|-------|----------|
| **Admin** | `admin@admin.com` | `password` |
| **Staff** | `staff@staff.com` | `password` |

---

## 🗄️ Skema Database

```
item_categories          item_stocks
──────────────           ────────────────────────
id (PK)          ◄─┐    id (PK)
name             │  └── category_id (FK)
division             item_name
created_at           total_stock
updated_at           total_repaired
                     total_borrowed  ← otomatis dikelola sistem
                     created_at
                     updated_at
                          │
                          ▼ (FK: item_id)
                     borrowed_items
                     ────────────────────────
                     id (PK)
                     staff_id (FK → users)
                     item_id (FK → item_stocks)
                     total_item
                     name_of_borrower
                     date
                     notes
                     created_at
                     updated_at
                          │
                          ▼ (FK: borrowed_item_id)
                     returned_items
                     ────────────────────────
                     id (PK)
                     staff_id (FK → users)
                     borrowed_item_id (FK)
                     return_date
                     total_damaged
                     notes
                     created_at
                     updated_at
```

---

## 🔄 Alur Logika Otomatis (Side Effects)

### Saat Peminjaman Baru Dicatat
```
BorrowedItem::created()
  → item_stocks.total_stock   -= total_item
  → item_stocks.total_borrowed += total_item
```

### Saat Barang Dikembalikan
```
Aksi "Kembalikan" di tabel Peminjaman
  → ReturnedItem::create(...)
  → item_stocks.total_stock    += jumlah_baik
  → item_stocks.total_repaired += jumlah_rusak
  → item_stocks.total_borrowed -= total_item
```

### Saat Data Peminjaman Dihapus (Sebelum Dikembalikan)
```
BorrowedItem::deleting()
  → item_stocks.total_stock    += total_item  (dikembalikan ke stok)
  → item_stocks.total_borrowed -= total_item
```

---

## 📁 Struktur Direktori Utama

```
app/
├── Filament/
│   └── Resources/
│       ├── BorrowedItems/          # Resource transaksi peminjaman
│       │   ├── BorrowedItemResource.php
│       │   ├── Pages/              # Create, Edit, List
│       │   ├── Schemas/            # Form fields (BorrowedItemForm.php)
│       │   └── Tables/             # Kolom & aksi tabel (BorrowedItemsTable.php)
│       ├── ItemCategories/         # Resource kategori barang
│       ├── ItemStocks/             # Resource data stok barang
│       └── Users/                  # Resource manajemen pengguna
├── Models/
│   ├── BorrowedItem.php            # Model dengan hooks otomatis stok
│   ├── ItemCategory.php            # Model dengan validasi duplikat
│   ├── ItemStock.php               # Model dengan validasi duplikat
│   ├── ReturnedItem.php            # Model data pengembalian
│   └── User.php                    # Model user dengan canAccessPanel()
├── Providers/
│   ├── AppServiceProvider.php
│   └── Filament/
│       └── AdminPanelProvider.php  # Konfigurasi panel Filament
└── Support/
    └── PdfExport.php               # Helper generasi PDF (tabel & struk)

database/
├── migrations/                     # Skema tabel database
└── seeders/
    └── DatabaseSeeder.php          # Data awal (2 user default)
```

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

## 👨‍💻 Pengembang

**Habiburramdhan**  
Proyek UKK — Sistem Informasi Manajemen Inventaris

🔗 **Repository:** [https://github.com/apipcode/inventaris-ukk](https://github.com/apipcode/inventaris-ukk)
