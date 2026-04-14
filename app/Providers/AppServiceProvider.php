<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider — Service provider utama aplikasi.
 *
 * Tempat mendaftarkan binding service container (register)
 * dan menjalankan logika bootstrap awal aplikasi (boot).
 *
 * Saat ini tidak ada konfigurasi khusus karena Filament
 * mengelola kebutuhan bootstrap-nya sendiri melalui AdminPanelProvider.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan service atau binding ke dalam service container Laravel.
     * Contoh penggunaan: $this->app->bind(Interface::class, Implementation::class);
     */
    public function register(): void
    {
        //
    }

    /**
     * Menjalankan logika setelah semua service provider terdaftar.
     * Contoh penggunaan: Gate::define(), View::composer(), Model::preventLazyLoading().
     */
    public function boot(): void
    {
        //
    }
}
