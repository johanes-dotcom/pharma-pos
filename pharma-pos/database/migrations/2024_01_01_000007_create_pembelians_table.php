<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pembelian')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->dateTime('tanggal_pembelian');
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status', ['pending', 'diterima', 'dibatalkan'])->default('pending');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            
            $table->index('kode_pembelian');
            $table->index('tanggal_pembelian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
