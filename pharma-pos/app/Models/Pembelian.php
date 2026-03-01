<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_pembelian',
        'supplier_id',
        'user_id',
        'tanggal_pembelian',
        'total',
        'status',
        'deskripsi',
    ];

    protected $casts = [
        'tanggal_pembelian' => 'datetime',
        'total' => 'decimal:2',
    ];

    /**
     * Relasi ke Supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relasi ke User (pembeli)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Detail Pembelian
     */
    public function detailPembelians(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'pembelian_id');
    }

    /**
     * Generate kode pembelian otomatis
     */
    public static function generateKode()
    {
        $prefix = 'PB';
        $date = now()->format('Ymd');
        $lastPurchase = self::whereDate('created_at', today())->latest()->first();
        
        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase->kode_pembelian, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $date . $newNumber;
    }
}
