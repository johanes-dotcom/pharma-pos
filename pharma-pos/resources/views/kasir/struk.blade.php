<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - {{ $penjualan->kode_penjualan }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; }
        .total-row { font-weight: bold; border-top: 1px dashed #000; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center border-bottom">
        <h3>PharmaPOS</h3>
        <p>Jl. Contoh No. 123<br>Telp: 021-1234567</p>
    </div>

    <div class="border-bottom">
        <p>
            No: {{ $penjualan->kode_penjualan }}<br>
            Tanggal: {{ \Carbon\Carbon::parse($penjualan->tanggal_penjualan)->format('d-m-Y H:i') }}<br>
            Kasir: {{ $penjualan->user->name ?? 'N/A' }}
        </p>
        @if($penjualan->pelanggan)
        <p>Pelanggan: {{ $penjualan->pelanggan->nama }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan->detailPenjualans as $item)
            <tr>
                <td>{{ $item->obat->nama_obat ?? 'N/A' }}</td>
                <td class="text-right">{{ $item->jumlah }}</td>
                <td class="text-right">{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="border-bottom">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">{{ number_format($penjualan->total + $penjualan->diskon, 0, ',', '.') }}</td>
            </tr>
            @if($penjualan->diskon > 0)
            <tr>
                <td>Diskon:</td>
                <td class="text-right">-{{ number_format($penjualan->diskon, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL:</td>
                <td class="text-right">{{ number_format($penjualan->total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bayar:</td>
                <td class="text-right">{{ number_format($penjualan->bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kembalian:</td>
                <td class="text-right">{{ number_format($penjualan->kembalian, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Terima kasih atas kunjungan Anda<br>
        Jangan lupa datang kembali</p>
    </div>
</body>
</html>
