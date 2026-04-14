<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * AdminPanelProvider — Mengkonfigurasi panel admin utama berbasis Filament.
 *
 * Panel ini dapat diakses di URL: /admin
 * Semua Resource, Page, dan Widget di dalam direktori app/Filament
 * otomatis didaftarkan melalui metode discoverResources(), discoverPages(), discoverWidgets().
 *
 * Akses panel dibatasi oleh metode canAccessPanel() di model User.
 */
class AdminPanelProvider extends PanelProvider
{
    /**
     * Mendefinisikan konfigurasi lengkap panel admin Filament.
     *
     * @param Panel $panel Objek panel Filament yang akan dikonfigurasi.
     * @return Panel
     */
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')

            // URL path untuk mengakses panel (contoh: domain.com/admin)
            ->path('admin')

            // Aktifkan halaman login bawaan Filament
            ->login()

            // Nama brand yang tampil di sidebar dan judul browser
            ->brandName('Inventaris')

            // Warna utama (primary color) tema panel
            ->colors([
                'primary' => Color::Blue,
            ])

            // Otomatis menemukan & mendaftarkan semua kelas Resource di direktori ini
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')

            // Otomatis menemukan & mendaftarkan semua kelas Page di direktori ini
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')

            // Mendaftarkan halaman Dashboard bawaan Filament
            ->pages([
                Dashboard::class,
            ])

            // Otomatis menemukan & mendaftarkan semua kelas Widget di direktori ini
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')

            // Mendaftarkan widget bawaan Filament (info akun & info versi Filament)
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])

            // Middleware untuk menangani sesi, keamanan CSRF, enkripsi cookie, dll.
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])

            // Middleware khusus autentikasi — memastikan user sudah login sebelum akses panel
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
