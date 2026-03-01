<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategoris')->onDelete('restrict');
            $table->string('kode_barcode')->unique();
            $table->string('nama_obat');
            $table->string('satuan')->default('pcs');
            $table->decimal('harga_beli', 12, 2);
            $table->decimal('harga_jual', 12, 2);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(10);
            $table->date('tanggal_kadaluarsa');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('kode_barcode');
            $table->index('tanggal_kadaluarsa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obats');
    }
};
