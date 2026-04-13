<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_stocks', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke kategori barang
            $table->foreignId('category_id')->constrained('item_categories')->cascadeOnDelete();
            // Nama barang secara spesifik
            $table->string('item_name');
            // Jumlah stok yang tersedia saat ini
            $table->integer('total_stock')->default(0);
            // Jumlah barang yang dalam kondisi rusak (di luar stok tersedia)
            $table->integer('total_repaired')->default(0);
            // Jumlah barang yang saat ini sedang dipinjam (otomatis diupdate oleh sistem)
            $table->integer('total_borrowed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_stocks');
    }
};
