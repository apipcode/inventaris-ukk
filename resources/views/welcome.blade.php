<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Inventaris Barang</title>
    <meta name="description" content="Sistem manajemen inventaris untuk mengelola barang, peminjaman, dan pengembalian secara efisien.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }

        /* ============================
           HEADER / NAVIGASI
        ============================ */
        header {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background-color: #2563eb;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon svg {
            width: 18px;
            height: 18px;
            color: #ffffff;
            stroke: #ffffff;
        }

        .brand-name {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            padding: 8px 18px;
            background-color: #2563eb;
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.15s;
        }

        .btn-login:hover {
            background-color: #1d4ed8;
        }

        /* batas 1 hero */
        .hero {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 72px 24px 80px;
        }

        .hero-inner {
            max-width: 1100px;
            margin: 0 auto;
        }

        .hero-tag {
            display: inline-block;
            padding: 4px 12px;
            background-color: #eff6ff;
            color: #2563eb;
            font-size: 13px;
            font-weight: 600;
            border-radius: 20px;
            margin-bottom: 20px;
            border: 1px solid #bfdbfe;
        }

        .hero h1 {
            font-size: 40px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 18px;
            max-width: 620px;
        }

        .hero p {
            font-size: 17px;
            color: #475569;
            margin-bottom: 36px;
            max-width: 560px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            padding: 11px 24px;
            background-color: #2563eb;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.15s;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            padding: 11px 24px;
            background-color: #ffffff;
            color: #374151;
            font-size: 15px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            border: 1px solid #d1d5db;
            transition: background-color 0.15s;
        }

        .btn-secondary:hover {
            background-color: #f9fafb;
        }

        /* batas fitur 1 */
        .features {
            padding: 72px 24px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-label {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 26px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 40px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 28px;
        }

        .feature-number {
            font-size: 13px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 12px;
            letter-spacing: 0.05em;
        }

        .feature-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .feature-card p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
        }

        /* section peran pengguna */
        .roles {
            background-color: #ffffff;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 72px 24px;
        }

        .roles-inner {
            max-width: 1100px;
            margin: 0 auto;
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .role-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 24px;
        }

        .role-badge {
            display: inline-block;
            padding: 3px 10px;
            background-color: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            margin-bottom: 14px;
            border: 1px solid #bfdbfe;
        }

        .role-card h3 {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .role-card ul {
            list-style: none;
            padding: 0;
        }

        .role-card ul li {
            font-size: 14px;
            color: #475569;
            padding: 5px 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .role-card ul li:last-child {
            border-bottom: none;
        }

        .role-card ul li::before {
            content: '';
            width: 6px;
            height: 6px;
            background-color: #2563eb;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* foter */
        footer {
            padding: 28px 24px;
            text-align: center;
        }

        footer p {
            font-size: 13px;
            color: #94a3b8;
        }

        @media (max-width: 640px) {
            .hero h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="header-inner">
            <a href="/" class="brand">
                <div class="brand-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <span class="brand-name">Inventaris</span>
            </a>
            @auth
                <a href="/admin" class="btn-login">Dashboard</a>
            @else
                <a href="/admin/login" class="btn-login">Masuk</a>
            @endauth
        </div>
    </header>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-inner">
            <span class="hero-tag">Sistem Inventaris Barang</span>
            <h1>Kelola Inventaris Instansi Anda dengan Lebih Mudah</h1>
            <p>Aplikasi pencatatan dan pengelolaan barang, peminjaman, serta pengembalian yang dirancang untuk keperluan instansi pemerintah maupun swasta.</p>
            <div class="hero-actions">
                <a href="/admin/login" class="btn-primary">Buka Dashboard</a>
                <a href="#fitur" class="btn-secondary">Lihat Fitur</a>
            </div>
        </div>
    </section>

    <!-- Fitur -->
    <section class="features" id="fitur">
        <p class="section-label">Fitur</p>
        <h2 class="section-title">Apa yang bisa dilakukan sistem ini?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <p class="feature-number">01 — Kategori</p>
                <h3>Manajemen Kategori Barang</h3>
                <p>Buat dan kelola kategori barang beserta divisi terkait. Setiap kategori menampilkan jumlah barang yang terdaftar di dalamnya.</p>
            </div>
            <div class="feature-card">
                <p class="feature-number">02 — Stok</p>
                <h3>Data Stok Barang</h3>
                <p>Pantau stok barang secara real-time — termasuk jumlah barang tersedia, rusak, dan sedang dipinjam dalam satu tampilan tabel.</p>
            </div>
            <div class="feature-card">
                <p class="feature-number">03 — Peminjaman</p>
                <h3>Pencatatan Peminjaman</h3>
                <p>Catat proses peminjaman barang oleh peminjam. Data stok akan otomatis diperbarui saat peminjaman dibuat atau dikembalikan.</p>
            </div>
            <div class="feature-card">
                <p class="feature-number">04 — Pengembalian</p>
                <h3>Proses Pengembalian</h3>
                <p>Staff dapat merekam pengembalian barang langsung dari tabel peminjaman. Status pengembalian tampil secara visual di tabel.</p>
            </div>
            <div class="feature-card">
                <p class="feature-number">05 — Pengguna</p>
                <h3>Manajemen Pengguna</h3>
                <p>Admin dapat mengelola semua akun pengguna. Staff hanya dapat mengelola akun sesama staff, termasuk pengubahan password.</p>
            </div>
            <div class="feature-card">
                <p class="feature-number">06 — Ekspor</p>
                <h3>Export Data</h3>
                <p>Setiap data (kategori, barang, peminjaman, pengguna) dapat diekspor ke format spreadsheet untuk keperluan pelaporan.</p>
            </div>
        </div>
    </section>

    <!-- Peran -->
    <section class="roles">
        <div class="roles-inner">
            <p class="section-label">Hak Akses</p>
            <h2 class="section-title">Dua peran pengguna dalam sistem</h2>
            <div class="roles-grid">
                <div class="role-card">
                    <span class="role-badge">Admin</span>
                    <h3>Hak Akses Administrator</h3>
                    <ul>
                        <li>Kelola kategori barang (CRUD + ekspor)</li>
                        <li>Kelola data stok barang (CRUD + ekspor)</li>
                        <li>Tambah laporan barang rusak</li>
                        <li>Lihat detail peminjaman per barang</li>
                        <li>Kelola semua pengguna (Admin & Staff)</li>
                    </ul>
                </div>
                <div class="role-card">
                    <span class="role-badge">Staff</span>
                    <h3>Hak Akses Staff / Operator</h3>
                    <ul>
                        <li>Kelola data peminjaman (tambah, lihat, hapus, ekspor)</li>
                        <li>Proses pengembalian barang</li>
                        <li>Kelola pengguna dengan role Staff</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Foter -->
    <footer>
        <p>&copy; 2026 Sistem Inventaris Barang. Dibangun dengan Laravel &amp; Filament.</p>
    </footer>

</body>
</html>
