<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Obat extends Model
{
    use HasFactory;

    protected $fillable = [
        'kategori_id',
        'kode_barcode',
        'nama_obat',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimum',
        'tanggal_kadaluarsa',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'tanggal_kadaluarsa' => 'date',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke Kategori
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Relasi ke detail pembelian
     */
    public function detailPembelians(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'obat_id');
    }

    /**
     * Relasi ke detail penjualan
     */
    public function detailPenjualans(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'obat_id');
    }

    /**
     * Cek apakah obat hampir kadaluarsa (30 hari)
     */
    public function isHampirKadaluarsa(): bool
    {
        return $this->tanggal_kadaluarsa->diffInDays(now()) <= 30;
    }

    /**
     * Cek apakah obat sudah kadaluarsa
     */
    public function isKadaluarsa(): bool
    {
        return $this->tanggal_kadaluarsa->isPast();
    }

    /**
     * Cek apakah stok di bawah minimum
     */
    public function isStokMin(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    /**
     * Scope untuk obat kadaluarsa
     */
    public function scopeKadaluarsa($query)
    {
        return $query->where('tanggal_kadaluarsa', '<', now());
    }

    /**
     * Scope untuk obat hampir kadaluarsa
     */
    public function scopeHampirKadaluarsa($query)
    {
        return $query->whereBetween('tanggal_kadaluarsa', [now(), now()->addDays(30)]);
    }

    /**
     * Scope untuk obat stok minimum
     */
    public function scopeStokMinim($query)
    {
        return $query->whereRaw('stok <= stok_minimum');
    }
}
