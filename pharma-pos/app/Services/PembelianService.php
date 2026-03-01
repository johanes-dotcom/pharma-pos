<?php

namespace App\Services;

use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Obat;
use App\Models\StokLog;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class PembelianService
{
    /**
     * Proses transaksi pembelian dengan database transaction
     */
    public function prosesPembelian(array $data, array $items, int $userId): Pembelian
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            // Generate kode pembelian
            $kodePembelian = Pembelian::generateKode();

            // Hitung total
            $total = 0;
            foreach ($items as $item) {
                $total += $item['jumlah'] * $item['harga_satuan'];
            }

            // Buat pembelian
            $pembelian = Pembelian::create([
                'kode_pembelian' => $kodePembelian,
                'supplier_id' => $data['supplier_id'],
                'user_id' => $userId,
                'tanggal_pembelian' => now(),
                'total' => $total,
                'status' => 'diterima',
                'deskripsi' => $data['deskripsi'] ?? null,
            ]);

            // Proses setiap item
            foreach ($items as $item) {
                $obat = Obat::findOrFail($item['obat_id']);
                $stokSebelum = $obat->stok;

                $subtotal = $item['jumlah'] * $item['harga_satuan'];

                // Tambah stok
                $obat->stok += $item['jumlah'];
                
                // Update harga beli jika berbeda
                if ($obat->harga_beli != $item['harga_satuan']) {
                    $obat->harga_beli = $item['harga_satuan'];
                }
                
                $obat->save();

                // Buat detail pembelian
                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'obat_id' => $item['obat_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $subtotal,
                ]);

                // Catat ke stok log
                StokLog::create([
                    'obat_id' => $item['obat_id'],
                    'user_id' => $userId,
                    'jenis_transaksi' => StokLog::TYPE_PEMBELIAN,
                    'jumlah' => $item['jumlah'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $obat->stok,
                    'referensi_id' => $pembelian->id,
                    'referensi_type' => Pembelian::class,
                    'deskripsi' => "Pembelian {$kodePembelian}",
                ]);
            }

            return $pembelian;
        });
    }

    /**
     * Get semua pembelian
     */
    public function getAllPembelian($tanggalAwal = null, $tanggalAkhir = null)
    {
        $query = Pembelian::with(['user', 'supplier', 'detailPembelians.obat'])
            ->orderBy('tanggal_pembelian', 'desc');

        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('tanggal_pembelian', [$tanggalAwal, $tanggalAkhir]);
        }

        return $query->get();
    }

    /**
     * Get pembelian berdasarkan ID
     */
    public function getPembelianById(int $id): ?Pembelian
    {
        return Pembelian::with(['user', 'supplier', 'detailPembelians.obat'])
            ->findOrFail($id);
    }

    /**
     * Batalkan pembelian
     */
    public function batalkanPembelian(Pembelian $pembelian, int $userId): bool
    {
        return DB::transaction(function () use ($pembelian, $userId) {
            // Kurangi stok
            foreach ($pembelian->detailPembelians as $detail) {
                $obat = $detail->obat;
                $stokSebelum = $obat->stok;

                $obat->stok -= $detail->jumlah;
                $obat->save();

                // Catat ke stok log
                StokLog::create([
                    'obat_id' => $obat->id,
                    'user_id' => $userId,
                    'jenis_transaksi' => StokLog::TYPE_RETUR,
                    'jumlah' => $detail->jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $obat->stok,
                    'referensi_id' => $pembelian->id,
                    'referensi_type' => Pembelian::class,
                    'deskripsi' => "Batal pembelian {$pembelian->kode_pembelian}",
                ]);
            }

            // Update status
            $pembelian->status = 'dibatalkan';
            $pembelian->save();

            return true;
        });
    }
}
