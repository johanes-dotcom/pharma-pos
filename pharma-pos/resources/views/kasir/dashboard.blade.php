@extends('layouts.app')

@section('title', 'Dashboard Kasir - PharmaPOS')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Dashboard Kasir</h2>
                <a href="{{ route('pos.index') }}" class="btn btn-primary">
                    <i class="fas fa-cash-register"></i> Buka Kasir (POS)
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Transaksi Hari Ini</h5>
                    <h2 class="mb-0">{{ $jumlahTransaksi ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Penjualan Hari Ini</h5>
                    <h2 class="mb-0">Rp {{ number_format($totalPenjualan ?? 0, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Obat Terjual Hari Ini</h5>
                    <h2 class="mb-0">{{ $totalObatTerjual ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Transaksi Terakhir</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($penjualanHariIni ?? [] as $penjualan)
                                <tr>
                                    <td>{{ $penjualan->kode_penjualan }}</td>
                                    <td>{{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d-m-Y H:i') }}</td>
                                    <td>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $penjualan->status == 'selesai' ? 'success' : 'danger' }}">
                                            {{ $penjualan->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('pos.cetak', $penjualan->id) }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-print"></i> Cetak
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada transaksi hari ini</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
