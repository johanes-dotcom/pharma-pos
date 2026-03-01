<?php

namespace App\Services;

use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Obat;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    /**
     * Get laporan penjualan harian
     */
    public function getLaporanHarian($tanggal = null)
    {
        $tanggal = $tanggal ?? now()->toDateString();
        
        $penjualans = Penjualan::whereDate('tanggal_penjualan', $tanggal)
            ->where('status', 'selesai')
            ->with(['user', 'pelanggan', 'detailPenjualans.obat'])
            ->get();

        $totalPenjualan = $penjualans->sum('total');
        $jumlahTransaksi = $penjualans->count();

        return [
            'tanggal' => $tanggal,
            'penjualans' => $penjualans,
            'total_penjualan' => $totalPenjualan,
            'jumlah_transaksi' => $jumlahTransaksi,
        ];
    }

    /**
     * Get laporan penjualan bulanan
     */
    public function getLaporanBulanan($tahun, $bulan)
    {
        $penjualans = Penjualan::whereYear('tanggal_penjualan', $tahun)
            ->whereMonth('tanggal_penjualan', $bulan)
            ->where('status', 'selesai')
            ->with(['user', 'pelanggan'])
            ->orderBy('tanggal_penjualan', 'desc')
            ->get();

        $totalPenjualan = $penjualans->sum('total');
        $jumlahTransaksi = $penjualans->count();
        $totalDiskon = $penjualans->sum('diskon');

        return [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'penjualans' => $penjualans,
            'total_penjualan' => $totalPenjualan,
            'jumlah_transaksi' => $jumlahTransaksi,
            'total_diskon' => $totalDiskon,
        ];
    }

    /**
     * Get produk terlaris
     */
    public function getProdukTerlaris($tanggalAwal = null, $tanggalAkhir = null, $limit = 10)
    {
        $query = DetailPenjualan::selectRaw('
            obat_id,
            SUM(jumlah) as total_terjual,
            SUM(subtotal) as total_pendapatan
        ')
        ->groupBy('obat_id')
        ->with('obat.kategori')
        ->orderByDesc('total_terjual');

        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereHas('penjualan', function ($q) use ($tanggalAwal, $tanggalAkhir) {
                $q->whereBetween('tanggal_penjualan', [$tanggalAwal, $tanggalAkhir]);
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get laporan laba rugi
     */
    public function getLaporanLabaRugi($tanggalAwal, $tanggalAkhir)
    {
        // Total penjualan
        $totalPenjualan = Penjualan::whereBetween('tanggal_penjualan', [$tanggalAwal, $tanggalAkhir])
            ->where('status', 'selesai')
            ->sum('total');

        // Total pembelian
        $totalPembelian = Pembelian::whereBetween('tanggal_pembelian', [$tanggalAwal, $tanggalAkhir])
            ->where('status', 'diterima')
            ->sum('total');

        // Hitung harga pokok penjualan (COGS)
        $detailPenjualans = DetailPenjualan::whereHas('penjualan', function ($q) use ($tanggalAwal, $tanggalAkhir) {
            $q->whereBetween('tanggal_penjualan', [$tanggalAwal, $tanggalAkhir])
              ->where('status', 'selesai');
        })->with('obat')->get();

        $hargaPokokPenjualan = 0;
        foreach ($detailPenjualans as $detail) {
            $hargaBeli = $detail->obat->harga_beli ?? 0;
            $hargaPokokPenjualan += $detail->jumlah * $hargaBeli;
        }

        $labaKotor = $totalPenjualan - $hargaPokokPenjualan;
        $labaBersih = $labaKotor - $totalPembelian;

        return [
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
            'total_penjualan' => $totalPenjualan,
            'total_pembelian' => $totalPembelian,
            'harga_pokok_penjualan' => $hargaPokokPenjualan,
            'laba_kotor' => $labaKotor,
            'laba_bersih' => $labaBersih,
        ];
    }

    /**
     * Get obat expiring soon (akan expired dalam 30 hari)
     */
    public function getObatExpiringSoon()
    {
        $tanggalBatas = now()->addDays(30)->toDateString();
        
        return Obat::where('tanggal_kadaluarsa', '<=', $tanggalBatas)
            ->where('tanggal_kadaluarsa', '>=', now()->toDateString())
            ->where('stok', '>', 0)
            ->with('kategori')
            ->orderBy('tanggal_kadaluarsa', 'asc')
            ->get();
    }

    /**
     * Get obat stok minimum
     */
    public function getObatStokMinimum()
    {
        return Obat::whereRaw('stok <= stok_minimum')
            ->where('is_active', true)
            ->with('kategori')
            ->orderBy('stok', 'asc')
            ->get();
    }

    /**
     * Get rekap stok obat
     */
    public function getRekapStok()
    {
        $totalObat = Obat::where('is_active', true)->count();
        $totalStok = Obat::where('is_active', true)->sum('stok');
        $nilaiStok = Obat::where('is_active', true)
            ->selectRaw('SUM(stok * harga_beli) as total_nilai')
            ->value('total_nilai') ?? 0;

        $obatHabis = Obat::where('stok', 0)
            ->where('is_active', true)
            ->count();

        $obatMinimum = $this->getObatStokMinimum()->count();

        return [
            'total_obat' => $totalObat,
            'total_stok' => $totalStok,
            'nilai_stok' => $nilaiStok,
            'obat_habis' => $obatHabis,
            'obat_minimum' => $obatMinimum,
        ];
    }
}
