- Habibi Sistem Inventaris (Filament)

Aplikasi inventaris berbasis web. memakai framework Laravel, Filament,MySQL dengan pendekatan.

1. Antarmuka Depan (Front Page)
Halaman depan aplikasi dibuat menggunakan -TailwindCSS- include panel dengan akses langsung untuk fitur -login- ke dashboard (`/admin/login`).

2. Fitur Role Masing-Masing

A. Role Admin
Admin memiliki akses penuh ke sistem dari Dashboard Admin.
- Kategori Barang: Admin dapat menambah, mengubah, menghapus, serta mengekspor data (`CSV`). Tabel secara otomatis menunjukkan -Jumlah Item- di dalam tiap kategori.
- (Item / Stock Barang): Data ditampilkan dengan lengkap (Stok tersedia, jumlah rusak, dan jumlah dipinjam).
  - ada tombol -Tambah Item Rusak- pada setiap baris data untuk update keterangan barang yang cacat secara instan dengan modal pop-up.
  - Jumlah pinjaman merupakan atribut -Clickable Action-, yang jika diklik akan me-redirect admin ke Halaman Peminjaman dan otomatis terfilter sesuai item yang dipilih tersebut.
- Manajemen Pengguna: Admin bisa melihat dan mengedit semua pengguna (termasuk Admin lain maupun Staff). Fitur ganti password otomatis ter-hash.

B. Role Staff / Operator
Staff mempunyai akses terbatas ke hal-hal operasional:
- Peminjaman Barang: Staff membuat rekaman peminjaman baru. Sistem otomatis mentotal dan memotong ketersediaan stok sambil mensinkron/merefresh data kepada -total_borrowed- pada -ItemStock-.
- Pengembalian Barang-: Staff hanya perlu menekan tombol -Kembalikan- (warna hijau) pada data yang dipinjam. Sebuah Action khusus akan menjalankan _query_ serempak/berbarengan (mengembalikan jumlah `total_borrowed`, menyimpan catatan `ReturnedItem`, dan log admin terkait pengembaliannya). Tombol akan otomatis hilang ketika barang sudah status kembali.
- -Manajemen Pengguna Staff: Sesuai dengan spesifikasi, Staff bisa menambah, menghapus, mengupdate, mengganti kata sandi, dan mengekspor data pengguna yang hanya memiliki role `Staff`. Sistem mencegah Staff mengakses data akun Admin berkat lokalisasi per-kueri ke database (`auth()->user()->role === 'staff'`).

3. Langkah Pengujian (Testing)
Sistem include fitur -seeder- default. run project:
   - Login ke dashboard di `/admin` pakek kredensial:
   - (Admin): (admin@admin.com) - Password: (password)
   - (Staff): 	(staff@staff.com) - Password: (password)