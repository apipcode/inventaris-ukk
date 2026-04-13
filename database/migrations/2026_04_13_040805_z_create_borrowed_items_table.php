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
        Schema::create('borrowed_items', function (Blueprint $table) {
            $table->id();
            // Staff yang mencatat transaksi peminjaman ini
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            // Barang yang dipinjam
            $table->foreignId('item_id')->constrained('item_stocks')->cascadeOnDelete();
            // Berapa jumlah item yang dipinjam
            $table->integer('total_item');
            // Nama orang yang meminjam barang
            $table->string('name_of_borrower');
            // Waktu atau tanggal dilakukannya peminjaman
            $table->dateTime('date');
            // Catatan tambahan mengenai peminjaman
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowed_items');
    }
};
