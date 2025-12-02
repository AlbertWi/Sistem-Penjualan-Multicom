<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Penjualan</title>
    <style>
        @page {
            size: 9.5in 11in;
            margin: 0.3in;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            margin: 0;
            padding: 0.2in;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px dashed #000;
            padding-bottom: 5px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header small {
            font-size: 11px;
        }

        .info {
            margin-bottom: 10px;
        }
        .info td {
            padding: 2px 5px;
            font-size: 15px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.items th, table.items td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 12pt;
        }
        table.items th {
            background: #f2f2f2;
        }

        .total {
            text-align: right;
            margin-top: 10px;
            font-size: 13px;
        }
        .total strong {
            font-size: 15px;
        }

        .thanks {
            margin-top: 15px;
            text-align: center;
            font-size: 12px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Header Nota -->
    <div class="header">
    <h2>{{ $sale->branch->name ?? 'Cabang' }}</h2>
        <p><strong>NOTA PENJUALAN</strong></p>
    </div>

    <!-- Info Penjualan -->
    <table class="info" width="100%">
        <tr>
            <td><strong>No. Nota :</strong> {{ $sale->id }}</td>
            <td><strong>Tanggal  :</strong> {{ $sale->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td><strong>To    :{{ $sale->customer->name ?? '-' }}</strong> </td>
        </tr>
    </table>

    <!-- Tabel Item -->
    <table class="items">
        <thead>
            <tr>
                <th style="width: 40%;">Produk</th>
                <th style="width: 20%;">IMEI</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 15%;">Harga</th>
                <th style="width: 15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp

            {{-- Produk HP --}}
            @foreach ($sale->items as $item)
                @php $sub = $item->price; $grandTotal += $sub; @endphp
                <tr>
                    <td>{{ $item->product->brand->name ?? '' }} {{ $item->product->model ?? $item->product->name }}</td>
                    <td>{{ $item->imei ?? '-' }}</td>
                    <td>1</td>
                    <td>Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($sub, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            {{-- Aksesoris --}}
            @foreach ($sale->accessories as $acc)
                @php $sub = $acc->price * $acc->qty; $grandTotal += $sub; @endphp
                <tr>
                    <td>{{ $acc->accessory->name ?? '-' }}</td>
                    <td>-</td>
                    <td>{{ $acc->qty }}</td>
                    <td>Rp{{ number_format($acc->price, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($sub, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Grand Total -->
    <div class="total">
        <strong>Grand Total: Rp{{ number_format($grandTotal, 0, ',', '.') }}</strong>
    </div>

    <!-- Footer -->
    <div class="thanks">
        <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
    </div>

</body>
</html>
