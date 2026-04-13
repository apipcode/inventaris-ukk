<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returned_items', function (Blueprint $table): void {
            $table->unsignedInteger('total_damaged')->default(0)->after('return_date');
        });
    }

    public function down(): void
    {
        Schema::table('returned_items', function (Blueprint $table): void {
            $table->dropColumn('total_damaged');
        });
    }
};
