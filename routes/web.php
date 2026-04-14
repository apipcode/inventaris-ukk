<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Rute Web Utama Aplikasi
|--------------------------------------------------------------------------
|
| Semua rute web yang dapat diakses melalui browser didefinisikan di sini.
|
| Catatan: Panel admin Filament memiliki routing sendiri yang dikonfigurasi
| di AdminPanelProvider (path: /admin). Tidak perlu didaftarkan di sini.
|
| Rute '/' saat ini mengarahkan ke welcome view bawaan Laravel.
| Ini bisa diubah menjadi redirect ke /admin jika diinginkan.
|
*/

Route::get('/', function () {
    return view('welcome');
});
