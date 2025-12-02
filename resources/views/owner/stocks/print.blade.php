<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok - {{ $branch->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0.5cm;
        }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 10px; 
            margin: 0;
            padding: 0;
        }
        h4 { 
            margin: 0 0 5px 0; 
            font-size: 12px;
        }
        p {
            margin: 0 0 10px 0;
            font-size: 10px;
        }
        .brand-title {
            font-weight: bold;
            font-size: 11px;
            margin-top: 8px;
        }
        .item-line {
            margin-left: 10px;
            font-size: 15px;
        }
    </style>
</head>
<body>
    <h4>Laporan Stok Cabang: {{ $branch->name }}</h4>
    <p>Tanggal: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>

    @php 
        $totalQty = 0; 
        $totalHarga = 0; 
        $grouped = $branch->inventoryItems->groupBy(fn($i) => $i->product->brand->name ?? 'Lainnya');
    @endphp

    @forelse($grouped as $brand => $items)
        <div class="brand-title">{{ $brand }}</div>
        @foreach($items as $item)
            @php
                $harga = $item->purchaseItem->price ?? 0;
                $totalQty++;
                $totalHarga += $harga;
            @endphp
            <div class="item-line">
                {{ $item->imei }} &nbsp;&nbsp; {{ $item->product->name }} &nbsp;&nbsp; Rp {{ number_format($harga, 0, ',', '.') }}
            </div>
        @endforeach
    @empty
        <p>Tidak ada stok</p>
    @endforelse

    <br>
    <p><strong>Total:</strong> Rp {{ number_format($totalHarga, 0, ',', '.') }} ({{ $totalQty }} Unit)</p>
</body>
</html>
