<?php

namespace App\Services;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Obat;
use App\Models\StokLog;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class PenjualanService
{
    /**
     * Proses transaksi penjualan dengan database transaction
     */
    public function prosesPenjualan(array $data, array $items, int $userId): Penjualan
    {
        return DB::transaction(function () use ($data, $items, $userId) {
            // Generate kode penjualan
            $kodePenjualan = Penjualan::generateKode();

            // Hitung total
            $total = 0;
            foreach ($items as $item) {
                $total += $item['jumlah'] * $item['harga_satuan'];
            }

            // Kurangi diskon jika ada
            $total = $total - ($data['diskon'] ?? 0);

            // Hitung kembalian
            $kembalian = ($data['bayar'] ?? 0) - $total;

            // Buat penjualan
            $penjualan = Penjualan::create([
                'kode_penjualan' => $kodePenjualan,
                'user_id' => $userId,
                'pelanggan_id' => $data['pelanggan_id'] ?? null,
                'tanggal_penjualan' => now(),
                'total' => $total,
                'bayar' => $data['bayar'] ?? 0,
                'kembalian' => max(0, $kembalian),
                'diskon' => $data['diskon'] ?? 0,
                'status' => 'selesai',
            ]);

            // Proses setiap item
            foreach ($items as $item) {
                $obat = Obat::findOrFail($item['obat_id']);

                // Cek stok cukup
                if ($obat->stok < $item['jumlah']) {
                    throw new \Exception("Stok obat {$obat->nama_obat} tidak cukup. Stok tersedia: {$obat->stok}");
                }

                $subtotal = $item['jumlah'] * $item['harga_satuan'];
                $stokSebelum = $obat->stok;

                // Kurangi stok
                $obat->stok -= $item['jumlah'];
                $obat->save();

                // Buat detail penjualan
                DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'obat_id' => $item['obat_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_satuan'],
                    'subtotal' => $subtotal,
                ]);

                // Catat ke stok log
                StokLog::create([
                    'obat_id' => $item['obat_id'],
                    'user_id' => $userId,
                    'jenis_transaksi' => StokLog::TYPE_PENJUALAN,
                    'jumlah' => $item['jumlah'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $obat->stok,
                    'referensi_id' => $penjualan->id,
                    'referensi_type' => Penjualan::class,
                    'deskripsi' => "Penjualan {$kodePenjualan}",
                ]);
            }

            return $penjualan;
        });
    }

    /**
     * Get penjualan by ID
     */
    public function getPenjualanById(int $id): ?Penjualan
    {
        return Penjualan::with(['user', 'pelanggan', 'detailPenjualans.obat'])
            ->find($id);
    }

    /**
     * Get penjualan hari ini
     */
    public function getPenjualanHariIni()
    {
        return Penjualan::whereDate('tanggal_penjualan', today())
            ->with(['user', 'pelanggan', 'detailPenjualans.obat'])
            ->orderBy('tanggal_penjualan', 'desc')
            ->get();
    }

    /**
     * Get penjualan berdasarkan tanggal
     */
    public function getPenjualanByDate($tanggalAwal, $tanggalAkhir)
    {
        return Penjualan::whereBetween('tanggal_penjualan', [$tanggalAwal, $tanggalAkhir])
            ->with(['user', 'pelanggan', 'detailPenjualans.obat'])
            ->orderBy('tanggal_penjualan', 'desc')
            ->get();
    }

    /**
     * Get total penjualan hari ini
     */
    public function getTotalPenjualanHariIni(): float
    {
        return Penjualan::whereDate('tanggal_penjualan', today())
            ->where('status', 'selesai')
            ->sum('total');
    }

    /**
     * Get jumlah transaksi hari ini
     */
    public function getJumlahTransaksiHariIni(): int
    {
        return Penjualan::whereDate('tanggal_penjualan', today())
            ->where('status', 'selesai')
            ->count();
    }

    /**
     * Get produk terlaris
     */
    public function getProdukTerlaris(int $limit = 10, $tanggalAwal = null, $tanggalAkhir = null)
    {
        $query = DetailPenjualan::selectRaw('obat_id, SUM(jumlah) as total_terjual, SUM(subtotal) as total_pendapatan')
            ->groupBy('obat_id');

        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereHas('penjualan', function ($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('tanggal_penjualan', [$tanggalAwal, $tanggalAkhir]);
            });
        }

        return $query->with('obat')
            ->orderByDesc('total_terjual')
            ->limit($limit)
            ->get();
    }

    /**
     * Batalkan penjualan
     */
    public function batalkanPenjualan(Penjualan $penjualan, int $userId): bool
    {
        return DB::transaction(function () use ($penjualan, $userId) {
            // Kembalikan stok
            foreach ($penjualan->detailPenjualans as $detail) {
                $obat = $detail->obat;
                $stokSebelum = $obat->stok;

                $obat->stok += $detail->jumlah;
                $obat->save();

                // Catat ke stok log
                StokLog::create([
                    'obat_id' => $obat->id,
                    'user_id' => $userId,
                    'jenis_transaksi' => StokLog::TYPE_RETUR,
                    'jumlah' => $detail->jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $obat->stok,
                    'referensi_id' => $penjualan->id,
                    'referensi_type' => Penjualan::class,
                    'deskripsi' => "Batal penjualan {$penjualan->kode_penjualan}",
                ]);
            }

            // Update status
            $penjualan->status = 'dibatalkan';
            $penjualan->save();

            return true;
        });
    }
}
