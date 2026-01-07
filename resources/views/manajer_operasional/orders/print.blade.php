<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        
        .invoice-header {
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: start;
        }
        
        .company-logo h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .company-logo p {
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }
        
        .invoice-title {
            text-align: right;
        }
        
        .invoice-title h2 {
            font-size: 32px;
            color: #e74c3c;
            margin-bottom: 5px;
        }
        
        .invoice-title p {
            font-size: 11px;
            color: #666;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .detail-box {
            width: 48%;
        }
        
        .detail-box h3 {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #ddd;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        
        .detail-value {
            color: #333;
            text-align: right;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table thead {
            background: #34495e;
            color: white;
        }
        
        .items-table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }
        
        .items-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .total-box {
            width: 300px;
            border: 2px solid #34495e;
            padding: 15px;
            background: #ecf0f1;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
        }
        
        .total-row.grand-total {
            border-top: 2px solid #34495e;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background: #fff9e6;
            border-left: 4px solid #f39c12;
        }
        
        .notes-section h4 {
            margin-bottom: 10px;
            color: #f39c12;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .imei-code {
            font-family: 'Courier New', monospace;
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
            
            .invoice-container {
                max-width: 100%;
            }
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ Print Invoice
    </button>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-logo">
                    <h1>MULTICOM STORE</h1>
                    <p>
                        Jl. Contoh Alamat No. 123<br>
                        Kota, Provinsi 12345<br>
                        Telp: (021) 1234-5678<br>
                        Email: info@multicomstore.com
                    </p>
                </div>
                <div class="invoice-title">
                    <h2>INVOICE</h2>
                    <p>
                        <strong>No:</strong> {{ $order->order_number }}<br>
                        <strong>Date:</strong> {{ $order->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Customer & Order Details -->
        <div class="invoice-details">
            <div class="detail-box">
                <h3>Customer Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $order->customer->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $order->customer->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $order->customer->phone }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $order->customer->address ?? 'Not provided' }}</span>
                </div>
            </div>

            <div class="detail-box">
                <h3>Order Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-{{ $order->payment_status }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ $order->payment_method ? ucfirst($order->payment_method) : '-' }}</span>
                </div>
                @if($order->paid_at)
                <div class="detail-row">
                    <span class="detail-label">Paid At:</span>
                    <span class="detail-value">{{ $order->paid_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">Product</th>
                    <th style="width: 25%;">IMEI/Serial</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 15%;" class="text-right">Price</th>
                    <th style="width: 15%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->name }}</strong><br>
                        <small style="color: #666;">{{ $item->product->brand->name ?? '-' }}</small>
                    </td>
                    <td>
                        @if($item->inventoryItem)
                            <span class="imei-code">{{ $item->inventoryItem->imei }}</span>
                            @if($item->inventoryItem->sku)
                                <br><small style="color: #666;">SKU: {{ $item->inventoryItem->sku }}</small>
                            @endif
                        @else
                            <small style="color: #999;">-</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>Rp {{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span>Tax (0%):</span>
                    <span>Rp 0</span>
                </div>
                <div class="total-row grand-total">
                    <span>GRAND TOTAL:</span>
                    <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($order->notes)
        <div class="notes-section">
            <h4>📝 Notes:</h4>
            <p>{{ $order->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>
                Thank you for your purchase!<br>
                <strong>MULTICOM STORE</strong> - Your Trusted Electronics Partner<br>
                For inquiries, please contact us at info@multicomstore.com or (021) 1234-5678
            </p>
            <p style="margin-top: 15px; font-size: 10px; color: #999;">
                This is a computer-generated invoice and does not require a signature.<br>
                Invoice generated on {{ now()->format('d/m/Y H:i:s') }}
            </p>
        </div>
    </div>

    <script>
        // Auto print dialog (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>