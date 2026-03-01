<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_pelanggan',
        'alamat',
        'no_telp',
        'email',
        'jenis_kelamin',
        'tanggal_lahir',
    ];

    /**
     * Relasi ke penjualan
     */
    public function penjualans(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'pelanggan_id');
    }
}
