Dokumentasi Project Sistem Inventaris Habibi

- Project UKK: Sistem Manajemen Inventaris Berbasis Web  
- Tech-Stack yang saya pakai: Laravel 13 · Filament v5 · MySQL  
- Bahasa Pemrograman yang di pakai: PHP 8.3

 1. Latar Belakang

Sistem Inventaris Barang adalah aplikasi web yang dirancang untuk membantu instansi dalam mengelola data barang, memantau stok, serta mencatat proses peminjaman dan pengembalian barang secara digital. Sistem ini menggantikan proses pencatatan manual yang rentan terhadap kesalahan dan sulit dilacak.


 2. Teknologi yang saya digunakan

| Komponen | Teknologi | Keterangan |
|---------
| Backend Framework | Laravel 13                  | Framework PHP modern berbasis MVC |
| Admin Panel       | Filament v5                 | Panel CRUD otomatis berbasis Livewire |
| Database          | MySQL | Relational database management system |
| Frontend (Landing)| HTML + CSS murni            | Tidak pakait framework CSS |
| Server (dev)      | Laragon / php artisan serve | Laragon(local) |
| PHP | PHP 8.3     | Versi yang tersedia di laptop |



 3. Struktur Database (ERD)

Sistem menggunakan 5 tabel utama yang saling berelasi:

 3.1 Tabel `users`
```sql
id           BIGINT PRIMARY KEY AUTO_INCREMENT
name         VARCHAR(255)
email        VARCHAR(255) UNIQUE
password     VARCHAR(255)
role         ENUM('admin', 'staff') DEFAULT 'staff'
created_at   TIMESTAMP
updated_at   TIMESTAMP
```

 3.2 Tabel `item_categories`
```sql
id           BIGINT PRIMARY KEY AUTO_INCREMENT
name         VARCHAR(255)    -- Nama kategori
division     VARCHAR(255)    -- Nama divisi/bidang
created_at   TIMESTAMP
updated_at   TIMESTAMP
```

 3.3 Tabel `item_stocks`
```sql
id              BIGINT PRIMARY KEY AUTO_INCREMENT
category_id     BIGINT FK → item_categories.id
item_name       VARCHAR(255)
total_stock     INT DEFAULT 0   -- Stok tersedia
total_repaired  INT DEFAULT 0   -- Jumlah barang rusak
total_borrowed  INT DEFAULT 0   -- Jumlah sedang dipinjam
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

 3.4 Tabel `borrowed_items`
```sql
id               BIGINT PRIMARY KEY AUTO_INCREMENT
staff_id         BIGINT FK → users.id
item_id          BIGINT FK → item_stocks.id
total_item       INT
name_of_borrower VARCHAR(255)
date             DATETIME
notes            TEXT NULL
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

 3.5 Tabel `returned_items`
```sql
id               BIGINT PRIMARY KEY AUTO_INCREMENT
staff_id         BIGINT FK → users.id
borrowed_item_id BIGINT FK → borrowed_items.id
return_date      DATETIME
notes            TEXT NULL
created_at       TIMESTAMP
updated_at       TIMESTAMP
```

---
 4. Relasi Antar Tabel

```
item_categories ──< item_stocks ──< borrowed_items ──< returned_items
                                        │
                        users ──────────┘ (staff_id)
                          │
                          └──────────────────────────── returned_items (staff_id)
```

---

## 5. Struktur Direktori Proyek

```
inventaris/
├── app/
│   ├── Filament/
│   │   ├── Exports/              ← Kelas exporter (CSV)
│   │   │   ├── UserExporter.php
│   │   │   ├── ItemCategoryExporter.php
│   │   │   ├── ItemStockExporter.php
│   │   │   └── BorrowedItemExporter.php
│   │   └── Resources/            ← Resource Filament (CRUD)
│   │       ├── Users/
│   │       │   ├── UserResource.php
│   │       │   ├── Pages/
│   │       │   ├── Schemas/UserForm.php
│   │       │   └── Tables/UsersTable.php
│   │       ├── ItemCategories/
│   │       │   ├── ItemCategoryResource.php
│   │       │   ├── Pages/
│   │       │   ├── Schemas/ItemCategoryForm.php
│   │       │   └── Tables/ItemCategoriesTable.php
│   │       ├── ItemStocks/
│   │       │   ├── ItemStockResource.php
│   │       │   ├── Pages/
│   │       │   ├── Schemas/ItemStockForm.php
│   │       │   └── Tables/ItemStocksTable.php
│   │       └── BorrowedItems/
│   │           ├── BorrowedItemResource.php
│   │           ├── Pages/CreateBorrowedItem.php (afterCreate hook)
│   │           ├── Schemas/BorrowedItemForm.php
│   │           └── Tables/BorrowedItemsTable.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── ItemCategory.php
│   │   ├── ItemStock.php
│   │   ├── BorrowedItem.php
│   │   └── ReturnedItem.php
│   └── Providers/
│       └── Filament/
│           └── AdminPanelProvider.php  ← Konfigurasi panel utama
├── database/
│   ├── migrations/               ← File migrasi tabel
│   └── seeders/
│       └── DatabaseSeeder.php    ← Data awal (admin & staff)
└── resources/views/
    └── welcome.blade.php         ← Halaman depan (landing page)
```

---

 6. Penjelasan Kode Utama

 6.1 Model `User.php`
Model User mengimplementasikan `FilamentUser` agar bisa mengakses panel admin.

```php
class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        // Admin dan Staff bisa akses panel Filament
        return in_array($this->role, ['admin', 'staff']);
    }
}
```

 6.2 Model `ItemCategory.php`
```php
class ItemCategory extends Model
{
    protected $fillable = ['name', 'division'];

    // Relasi: 1 kategori memiliki banyak item
    public function itemStocks(): HasMany
    {
        return $this->hasMany(ItemStock::class, 'category_id');
    }
}
```

 6.3 Model `BorrowedItem.php`
```php
class BorrowedItem extends Model
{
    // Relasi ke staff yang mengelola peminjaman
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    // Relasi ke barang yang dipinjam
    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemStock::class, 'item_id');
    }

    // Relasi ke data pengembalian (1 peminjaman → 1 pengembalian)
    public function returnedItem(): HasOne
    {
        return $this->hasOne(ReturnedItem::class, 'borrowed_item_id');
    }
}
```

---

 6.4 Resource: Kontrol Hak Akses

Setiap resource dikontrol aksesnya berdasarkan role pengguna yang sedang login.

-Admin only (Kategori & Barang):**
```php
public static function canAccess(): bool
{
    // Hanya admin yang bisa melihat dan mengelola halaman ini
    return auth()->check() && auth()->user()->role === 'admin';
}
```

Staff hanya melihat sesama Staff (di menu Pengguna):
```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    // Jika login sebagai staff, hanya tampilkan user dengan role staff
    if (auth()->check() && auth()->user()->role === 'staff') {
        $query->where('role', 'staff');
    }

    return $query;
}
```

---

 6.5 Fitur: Kolom Total Dipinjam sebagai Link

Di tabel Data Barang, kolom **Dipinjam** adalah link yang mengarah ke halaman peminjaman yang sudah difilter sesuai barang yang dipilih.

```php
TextColumn::make('total_borrowed')
    ->label('Dipinjam')
    ->numeric()
    ->sortable()
    // Klik angka → diarahkan ke halaman peminjaman, difilter per barang
    ->url(fn (ItemStock $record): string =>
        BorrowedItemResource::getUrl('index') .
        '?tableFilters[item_id][value]=' . $record->id
    ),
```

---

 6.6 Fitur: Aksi "Tambah Item Rusak"

Admin bisa menambah jumlah barang rusak melalui tombol aksi langsung di tabel. Stok otomatis berkurang.

```php
Action::make('tambahRusak')
    ->label('Tambah Rusak')
    ->icon('heroicon-o-wrench')
    ->form([
        TextInput::make('jumlah_rusak')
            ->label('Jumlah Rusak Tambahan')
            ->numeric()->minValue(1)->required()
    ])
    ->action(function (ItemStock $record, array $data): void {
        // Tambah jumlah rusak
        $record->total_repaired += $data['jumlah_rusak'];

        // Kurangi stok yang tersedia
        if ($record->total_stock >= $data['jumlah_rusak']) {
            $record->total_stock -= $data['jumlah_rusak'];
        }
        $record->save();
    }),
```

---

 6.7 Fitur: Proses Pengembalian Barang

Staff menekan tombol **Kembalikan** pada baris peminjaman. Sistem akan:
1. Membuat record baru di tabel `returned_items`
2. Mengurangi `total_borrowed` di `item_stocks`
3. Menyembunyikan tombol jika barang sudah dikembalikan

```php
Action::make('kembalikan')
    ->label('Kembalikan')
    ->icon('heroicon-o-check-circle')
    // Tombol tersembunyi jika sudah ada data pengembalian
    ->hidden(fn (BorrowedItem $record) => $record->returnedItem !== null)
    ->requiresConfirmation()
    ->form([
        Textarea::make('notes')->label('Catatan Pengembalian')
    ])
    ->action(function (BorrowedItem $record, array $data): void {
        // Simpan data pengembalian
        ReturnedItem::create([
            'staff_id'         => auth()->id(),
            'borrowed_item_id' => $record->id,
            'return_date'      => now(),
            'notes'            => $data['notes'] ?? null,
        ]);

        // Kembalikan stok: kurangi total_borrowed
        $item = $record->item;
        if ($item) {
            $item->total_borrowed -= $record->total_item;
            $item->save();
        }
    }),
```

---

 6.8 Otomatis Update Stok Saat Peminjaman Dibuat

Saat staff membuat peminjaman baru, `total_borrowed` di tabel barang otomatis bertambah melalui hook `afterCreate`.

```php
// File: BorrowedItems/Pages/CreateBorrowedItem.php
protected function afterCreate(): void
{
    $record = $this->record;
    $item = ItemStock::find($record->item_id);

    if ($item) {
        // Tambah jumlah dipinjam sesuai total item yang dipinjam
        $item->total_borrowed += $record->total_item;
        $item->save();
    }
}
```

---

 6.9 Form Peminjaman (BorrowedItemForm.php)

```php
Hidden::make('staff_id')
    ->default(fn () => auth()->id()),   // Otomatis diisi dari user login

Select::make('item_id')
    ->relationship('item', 'item_name') // Dropdown nama barang
    ->label('Barang')
    ->searchable()
    ->required(),

TextInput::make('total_item')
    ->label('Jumlah Pinjam')
    ->required()->numeric(),

TextInput::make('name_of_borrower')
    ->label('Nama Peminjam')
    ->required(),

DateTimePicker::make('date')
    ->label('Tanggal Pinjam')
    ->default(now())
    ->required(),

Textarea::make('notes')
    ->label('Catatan'),
```

---
 7. Konfigurasi Panel Admin (AdminPanelProvider.php)

```php
return $panel
    ->default()
    ->id('admin')
    ->path('admin')           // URL: /admin
    ->login()                 // Aktifkan halaman login
    ->brandName('Inventaris') // Nama brand di sidebar
    ->colors(['primary' => Color::Blue])  // Warna utama: Biru
    ->discoverResources(...)  // Auto-detect semua Resource
    ->discoverPages(...)
    ->discoverWidgets(...);



 8. Database Seeder (Akun Default)

```php
// File: database/seeders/DatabaseSeeder.php

User::factory()->create([
    'name'     => 'Administrator',
    'email'    => 'admin@admin.com',
    'password' => Hash::make('password'),
    'role'     => 'admin',
]);

User::factory()->create([
    'name'     => 'Staff Operator',
    'email'    => 'staff@staff.com',
    'password' => Hash::make('password'),
    'role'     => 'staff',
]);
```

---

 9. Fitur Per Role Pengguna

 9.1 Admin
| Menu | Fitur |
|---|---|
| Kategori Barang | Tambah, Lihat, Edit, Hapus, Export CSV |
| Data Barang | Tambah, Lihat, Edit, Hapus, Export CSV, Link detail peminjaman, Tambah Rusak |
| Pengguna | Kelola semua user (Admin & Staff), Ubah password, Export |

 9.2 Staff / Operator
| Menu | Fitur |
|---|---|
| Peminjaman | Tambah, Lihat, Hapus, Export CSV |
| Peminjaman | Tombol Kembalikan barang |
| Pengguna | Kelola user role Staff saja, Ubah password, Export |



 10. Cara Running Proyek jika incase di environment baru

```bash
 1. Clone / masuk ke direktori proyek
cd inventaris

 2. Install dependensi PHP
composer install

 3. Copy file konfigurasi environment
cp .env.example .env

 4. Set koneksi database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventaris
DB_USERNAME=root
DB_PASSWORD=

 5. Generate application key
php artisan key:generate

 6. Jalankan migrasi + seeder
php artisan migrate:fresh --seed

 7. Jalankan server
php artisan serve
```

Akses di: http://127.0.0.1:8000

| Akun | Email | Password |
|---|---|---|
| Admin | admin@admin.com | password |
| Staff | staff@staff.com | password |

---

 11. Halaman Aplikasi

| URL | Keterangan |
|---|---|
| `/` | Landing page — halaman depan |
| `/admin/login` | Halaman login |
| `/admin` | Dashboard utama |
| `/admin/item-categories` | Kelola kategori (Admin) |
| `/admin/item-stocks` | Kelola barang (Admin) |
| `/admin/borrowed-items` | Kelola peminjaman (Staff) |
| `/admin/users` | Kelola pengguna (Admin & Staff) |
