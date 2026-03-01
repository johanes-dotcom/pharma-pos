<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penjualan')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggans')->onDelete('set null');
            $table->dateTime('tanggal_penjualan');
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('bayar', 12, 2)->default(0);
            $table->decimal('kembalian', 12, 2)->default(0);
            $table->decimal('diskon', 12, 2)->default(0);
            $table->enum('status', ['selesai', 'dikembalikan', 'dibatalkan'])->default('selesai');
            $table->timestamps();
            
            $table->index('kode_penjualan');
            $table->index('tanggal_penjualan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
