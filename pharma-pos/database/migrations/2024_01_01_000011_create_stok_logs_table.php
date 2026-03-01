<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obats')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('jenis_transaksi', ['pembelian', 'penjualan', 'retur', 'adjustment']);
            $table->integer('jumlah');
            $table->integer('stok_sebelum');
            $table->integer('stok_sesudah');
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->string('referensi_type')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            
            $table->index('obat_id');
            $table->index('referensi_id', 'referensi_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_logs');
    }
};
