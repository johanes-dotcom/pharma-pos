<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'obat_id',
        'user_id',
        'jenis_transaksi',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'referensi_id',
        'referensi_type',
        'deskripsi',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'stok_sebelum' => 'integer',
        'stok_sesudah' => 'integer',
    ];

    /**
     * Relasi ke Obat
     */
    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Konstanta jenis transaksi
     */
    const TYPE_PEMBELIAN = 'pembelian';
    const TYPE_PENJUALAN = 'penjualan';
    const TYPE_RETUR = 'retur';
    const TYPE_ADJUSTMENT = 'adjustment';
}
