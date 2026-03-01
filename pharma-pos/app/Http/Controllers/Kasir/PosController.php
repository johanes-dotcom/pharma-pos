<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Obat;
use App\Models\Pelanggan;
use App\Services\PenjualanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    protected $penjualanService;

    public function __construct(PenjualanService $penjualanService)
    {
        $this->penjualanService = $penjualanService;
    }

    /**
     * Tampilkan halaman POS
     */
    public function index()
    {
        $obats = Obat::where('is_active', true)
            ->where('stok', '>', 0)
            ->with('kategori')
            ->orderBy('nama_obat')
            ->get();

        $pelanggans = Pelanggan::where('is_active', true)
            ->orderBy('nama')
            ->get();

        $penjualanHariIni = $this->penjualanService->getPenjualanHariIni();

        return view('kasir.pos', compact('obats', 'pelanggans', 'penjualanHariIni'));
    }

    /**
     * Cari obat berdasarkan barcode
     */
    public function cariObat(Request $request)
    {
        $obat = Obat::where('kode_barcode', $request->barcode)
            ->where('is_active', true)
            ->with('kategori')
            ->first();

        if (!$obat) {
            return response()->json([
                'success' => false,
                'message' => 'Obat tidak ditemukan'
            ]);
        }

        if ($obat->stok <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Stok obat habis'
            ]);
        }

        return response()->json([
            'success' => true,
            'obat' => $obat
        ]);
    }

    /**
     * Proses transaksi penjualan
     */
    public function prosesPenjualan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.obat_id' => 'required|integer',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'bayar' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $items = $request->items;
            $data = [
                'pelanggan_id' => $request->pelanggan_id,
                'bayar' => $request->bayar,
                'diskon' => $request->diskon ?? 0,
            ];

            $penjualan = $this->penjualanService->prosesPenjualan(
                $data,
                $items,
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'penjualan' => $penjualan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cetak struk
     */
    public function cetakStruk($id)
    {
        $penjualan = $this->penjualanService->getPenjualanById($id);
        
        if (!$penjualan) {
            return redirect()->back()->with('error', 'Penjualan tidak ditemukan');
        }

        return view('kasir.struk', compact('penjualan'));
    }
}
