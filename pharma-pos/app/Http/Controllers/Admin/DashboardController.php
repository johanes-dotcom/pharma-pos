<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LaporanService;
use App\Services\PenjualanService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $laporanService;
    protected $penjualanService;

    public function __construct(LaporanService $laporanService, PenjualanService $penjualanService)
    {
        $this->laporanService = $laporanService;
        $this->penjualanService = $penjualanService;
    }

    /**
     * Dashboard utama (Admin)
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check if user has role loaded
        if (!$user->role) {
            Auth::logout();
            return redirect('/login')->with('error', 'Role tidak ditemukan');
        }
        
        // Data untuk dashboard
        $rekapStok = $this->laporanService->getRekapStok();
        $penjualanHariIni = $this->laporanService->getLaporanHarian();
        $produkTerlaris = $this->laporanService->getProdukTerlaris(5);
        $obatExpiring = $this->laporanService->getObatExpiringSoon();
        $obatStokMinimum = $this->laporanService->getObatStokMinimum();

        return view('admin.dashboard', compact(
            'user',
            'rekapStok',
            'penjualanHariIni',
            'produkTerlaris',
            'obatExpiring',
            'obatStokMinimum'
        ));
    }

    /**
     * Dashboard untuk Kasir (POS)
     */
    public function kasir()
    {
        $user = Auth::user();
        
        // Check if user has role loaded
        if (!$user->role) {
            Auth::logout();
            return redirect('/login')->with('error', 'Role tidak ditemukan');
        }
        
        $penjualanHariIni = $this->penjualanService->getPenjualanHariIni();
        $totalPenjualan = $this->penjualanService->getTotalPenjualanHariIni();
        $jumlahTransaksi = $this->penjualanService->getJumlahTransaksiHariIni();
        $totalObatTerjual = $penjualanHariIni->sum(function($p) {
            return $p->detailPenjualans->sum('jumlah');
        });

        return view('kasir.dashboard', compact(
            'user',
            'penjualanHariIni',
            'totalPenjualan',
            'jumlahTransaksi',
            'totalObatTerjual'
        ));
    }
}
