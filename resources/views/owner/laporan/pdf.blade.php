<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            position: relative;
        }
        .logo-container {
            margin-right: 20px;
        }
        .logo-container img {
            height: 100px;
        }
        .company-info {
            flex: 1;
            text-align: center;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .report-info {
            margin-bottom: 20px;
        }
        .report-info table {
            width: 100%;
        }
        .report-info td {
            padding: 5px 0;
            vertical-align: top;
        }
        .summary {
            margin: 20px 0;
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-label {
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #e6f3ff !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
        }
        .page-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            @if(file_exists(public_path('images/logo.jpeg')))
                <img src="{{ public_path('images/logo.jpeg') }}" alt="Logo {{ $namaPerusahaan }}">
            @else
                <div style="width:100px;height:100px;border:1px dashed #ccc;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:10px;text-align:center;">
                    LOGO<br>COMPANY
                </div>
            @endif
        </div>
        <div class="company-info">
            <div class="company-name">{{ $namaPerusahaan }}</div>
        </div>
    </div>

    <div class="report-title">LAPORAN PENJUALAN</div>

    <div class="report-info">
        <table>
            <tr>
                <td width="150">Periode</td>
                <td>: {{ $tanggalAwal ? \Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y') : 'Semua' }} - {{ $tanggalAkhir ? \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') : 'Sekarang' }}</td>
            </tr>
            <tr>
                <td>Tanggal Cetak</td>
                <td>: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <td>Total Transaksi</td>
                <td>: {{ $penjualan->sum(fn($sale) => $sale->items->count()) }} item</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Total Pendapatan:</span>
            <span>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Laba:</span>
            <span>Rp {{ number_format($totalLaba, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Margin Laba:</span>
            <span>{{ $totalPendapatan > 0 ? number_format(($totalLaba / $totalPendapatan) * 100, 2) : 0 }}%</span>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="15%">Cabang</th>
                <th width="20%">Produk</th>
                <th width="18%">IMEI</th>
                <th width="12%">Harga Beli</th>
                <th width="12%">Harga Jual</th>
                <th width="12%">Laba</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($penjualan as $sale)
                @foreach ($sale->items as $item)
                    @php
                        $hargaJual = $item->price;
                        $hargaBeli = $item->inventoryItem->purchaseItem->price ?? 0;
                        $laba = $hargaJual - $hargaBeli;
                    @endphp
                    <tr class="page-break">
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="text-center">{{ $sale->created_at->format('d/m/Y') }}</td>
                        <td>{{ $sale->branch->name ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->imei }}</td>
                        <td class="text-right">Rp {{ number_format($hargaBeli, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($laba, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-center"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($totalLaba, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
