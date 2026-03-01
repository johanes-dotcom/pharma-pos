<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_penjualan',
        'user_id',
        'pelanggan_id',
        'tanggal_penjualan',
        'total',
        'bayar',
        'kembalian',
        'diskon',
        'status',
    ];

    protected $casts = [
        'tanggal_penjualan' => 'datetime',
        'total' => 'decimal:2',
        'bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'diskon' => 'decimal:2',
    ];

    /**
     * Relasi ke User (kasir)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Pelanggan
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * Relasi ke Detail Penjualan
     */
    public function detailPenjualans(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'penjualan_id');
    }

    /**
     * Generate kode penjualan otomatis
     */
    public static function generateKode()
    {
        $prefix = 'TRX';
        $date = now()->format('Ymd');
        $lastSale = self::whereDate('created_at', today())->latest()->first();
        
        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->kode_penjualan, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $date . $newNumber;
    }
}

