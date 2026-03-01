@extends('layouts.app')

@section('title', 'Dashboard - PharmaPOS')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h4 class="fw-bold">Dashboard</h4>
        <p class="text-muted">Selamat datang, {{ Auth::user()->name }}</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Obat</h6>
                        <h3 class="mb-0">{{ number_format($rekapStok['total_obat'] ?? 0) }}</h3>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-capsules fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Penjualan Hari Ini</h6>
                        <h3 class="mb-0">Rp {{ number_format($penjualanHariIni['total_penjualan'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Transaksi Hari Ini</h6>
                        <h3 class="mb-0">{{ number_format($penjualanHariIni['jumlah_transaksi'] ?? 0) }}</h3>
                    </div>
                    <div class="text-warning">
                        <i class="fas fa-receipt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Nilai Stok</h6>
                        <h3 class="mb-0">Rp {{ number_format($rekapStok['nilai_stok'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="text-danger">
                        <i class="fas fa-warehouse fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts -->
<div class="row mb-4">
    @if($obatExpiring->count() > 0)
    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning text-white">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Obat Akan Kadaluarsa (30 hari)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Nama Obat</th>
                                <th>Stok</th>
                                <th>Tgl Kadaluarsa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($obatExpiring->take(5) as $obat)
                            <tr>
                                <td>{{ $obat->nama_obat }}</td>
                                <td>{{ $obat->stok }}</td>
                                <td>{{ \Carbon\Carbon::parse($obat->tanggal_kadaluarsa)->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @if($obatStokMinimum->count() > 0)
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                Stok Minimum
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Nama Obat</th>
                                <th>Stok</th>
                                <th>Min</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($obatStokMinimum->take(5) as $obat)
                            <tr>
                                <td>{{ $obat->nama_obat }}</td>
                                <td class="text-danger fw-bold">{{ $obat->stok }}</td>
                                <td>{{ $obat->stok_minimum }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Produk Terlaris -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-fire me-2"></i>
                Produk Terlaris (5 Besar)
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Nama Obat</th>
                                <th>Kategori</th>
                                <th>Total Terjual</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produkTerlaris as $index => $item)
                            <tr>
                                <td>
                                    @if($index == 0)
                                    <span class="badge bg-warning">1</span>
                                    @elseif($index == 1)
                                    <span class="badge bg-secondary">2</span>
                                    @elseif($index == 2)
                                    <span class="badge bg-danger">3</span>
                                    @else
                                    {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>{{ $item->obat->nama_obat ?? '-' }}</td>
                                <td>{{ $item->obat->kategori->nama_kategori ?? '-' }}</td>
                                <td>{{ number_format($item->total_terjual) }}</td>
                                <td>Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data penjualan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
