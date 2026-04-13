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
        Schema::create('returned_items', function (Blueprint $table) {
            $table->id();
            // Staff yang menerima atau menginput pengembalian
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            // Menghubungkan ke data peminjaman yang akan dikembalikan
            $table->foreignId('borrowed_item_id')->constrained('borrowed_items')->cascadeOnDelete();
            // Waktu pengembalian barang
            $table->dateTime('return_date');
            // Catatan kondisi barang saat dikembalikan
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returned_items');
    }
};
