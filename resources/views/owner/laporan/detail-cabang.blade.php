@extends('layouts.app')

@section('title', 'Detail Penjualan Cabang')

@section('content')
<div class="container-fluid">
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-primary mb-1">
                <i class="fas fa-store me-2"></i>Detail Penjualan Cabang
            </h4>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <i class="fas fa-building me-1"></i> {{ $branch->name }}
                </span>
                <span class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y') }} - 
                    {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') }}
                </span>
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
        @if($sales->count() > 0)
        <form method="GET" action="{{ route('owner.sales.export-pdf') }}" class="d-inline">
            <input type="hidden" name="tanggal_awal" value="{{ $tanggalAwal }}">
            <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
            <button type="submit" class="btn btn-danger shadow-sm">
                <i class="fas fa-file-pdf me-2"></i> Export PDF
            </button>
        </form>
        @endif
    </div>

    <!-- SUMMARY CARDS -->
    @if($sales->count() > 0)
        @php
            $totalPendapatan = 0;
            $totalLaba = 0;
            $totalItem = 0;
            $customers = collect();
            $brandsSummary = [];
            
            foreach($sales as $sale) {
                // Produk HP
                foreach($sale->items ?? [] as $item) {
                    $hargaJual = floatval($item->price ?? 0);
                    $hargaBeli = floatval($item->inventoryItem->purchase_price ?? 0);
                    $totalPendapatan += $hargaJual;
                    $totalLaba += ($hargaJual - $hargaBeli);
                    $totalItem++;
                    
                    // Hitung per brand
                    $brandName = $item->product->brand->name ?? 'Tidak Ada Merek';
                    if(!isset($brandsSummary[$brandName])) {
                        $brandsSummary[$brandName] = ['qty' => 0, 'pendapatan' => 0];
                    }
                    $brandsSummary[$brandName]['qty']++;
                    $brandsSummary[$brandName]['pendapatan'] += $hargaJual;
                }
                
                // Accessories
                foreach($sale->accessories ?? [] as $acc) {
                    $hargaJual = floatval($acc->price ?? 0);
                    $hargaBeli = floatval($acc->purchaseAccessory->price ?? 0);
                    $totalPendapatan += $hargaJual;
                    $totalLaba += ($hargaJual - $hargaBeli);
                    $totalItem++;
                }
                
                // Kumpulkan customer
                if($sale->customer) {
                    $customers->push($sale->customer->id);
                }
            }
            
            $totalMargin = $totalPendapatan > 0 ? ($totalLaba / $totalPendapatan) * 100 : 0;
            $avgTransaction = $sales->count() > 0 ? $totalPendapatan / $sales->count() : 0;
        @endphp
        
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card summary-card bg-primary bg-opacity-10 border-primary border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-primary text-white me-3">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Pendapatan</h6>
                                <h3 class="fw-bold mb-1">Rp{{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-receipt me-1"></i>
                                    {{ $sales->count() }} transaksi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card summary-card bg-success bg-opacity-10 border-success border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-success text-white me-3">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Laba</h6>
                                <h3 class="fw-bold mb-1">Rp{{ number_format($totalLaba, 0, ',', '.') }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-percentage me-1"></i>
                                    {{ number_format($totalMargin, 1) }}% margin
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 mb-3">
                <div class="card summary-card bg-info bg-opacity-10 border-info border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-info text-white me-3">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Item</h6>
                                <h3 class="fw-bold mb-1">{{ $totalItem }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-chart-pie me-1"></i>
                                    {{ $sales->count() > 0 ? number_format($totalItem / $sales->count(), 1) : 0 }} item/transaksi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 mb-3">
                <div class="card summary-card bg-warning bg-opacity-10 border-warning border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-warning text-white me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Customer</h6>
                                <h3 class="fw-bold mb-1">{{ $customers->unique()->count() }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-user-check me-1"></i>
                                    {{ $sales->count() > 0 ? number_format($customers->unique()->count() / $sales->count() * 100, 0) : 0 }}% repeat rate
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 mb-3">
                <div class="card summary-card bg-secondary bg-opacity-10 border-secondary border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-secondary text-white me-3">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Avg/Transaksi</h6>
                                <h3 class="fw-bold mb-1">Rp{{ number_format($avgTransaction, 0, ',', '.') }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-balance-scale me-1"></i>
                                    Rata-rata nilai
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BRAND SUMMARY -->
        @if(count($brandsSummary) > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-tags me-2"></i>Ringkasan per Merek
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($brandsSummary as $brandName => $data)
                    <div class="col-md-3 mb-3">
                        <div class="border rounded p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold">{{ $brandName }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $data['qty'] }} item</span>
                            </div>
                            <h5 class="fw-bold text-primary mb-0">
                                Rp{{ number_format($data['pendapatan'], 0, ',', '.') }}
                            </h5>
                            <small class="text-muted">
                                {{ $totalPendapatan > 0 ? number_format(($data['pendapatan'] / $totalPendapatan) * 100, 1) : 0 }}% dari total
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- DETAIL TABLE -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-3">
                <div>
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-list me-2"></i>
                        Detail Transaksi
                    </h5>
                    <small class="text-muted">
                        Total {{ $sales->count() }} transaksi, {{ $totalItem }} item
                    </small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAllDetails()">
                        <i class="fas fa-expand me-1"></i> Tampilkan Semua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="collapseAllDetails()">
                        <i class="fas fa-compress me-1"></i> Sembunyikan Semua
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="width: 120px;">Tanggal</th>
                                <th style="min-width: 150px;">Customer</th>
                                <th style="width: 120px;">Nota ID</th>
                                <th style="width: 100px;" class="text-center">Jumlah Item</th>
                                <th style="width: 150px;" class="text-end">Total Jual</th>
                                <th style="width: 150px;" class="text-end">Total Laba</th>
                                <th style="width: 100px;" class="text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $index => $sale)
                                @php
                                    $saleTotalJual = 0;
                                    $saleTotalLaba = 0;
                                    $saleTotalItem = 0;
                                    
                                    // Hitung untuk produk HP
                                    foreach($sale->items ?? [] as $item) {
                                        $hargaJual = floatval($item->price ?? 0);
                                        $hargaBeli = floatval($item->inventoryItem->purchase_price ?? 0);
                                        $saleTotalJual += $hargaJual;
                                        $saleTotalLaba += ($hargaJual - $hargaBeli);
                                        $saleTotalItem++;
                                    }
                                    
                                    // Hitung untuk accessories
                                    foreach($sale->accessories ?? [] as $acc) {
                                        $hargaJual = floatval($acc->price ?? 0);
                                        $hargaBeli = floatval($acc->purchaseAccessory->price ?? 0);
                                        $saleTotalJual += $hargaJual;
                                        $saleTotalLaba += ($hargaJual - $hargaBeli);
                                        $saleTotalItem++;
                                    }
                                    
                                    $saleMargin = $saleTotalJual > 0 ? ($saleTotalLaba / $saleTotalJual) * 100 : 0;
                                @endphp
                                
                                <tr class="sale-header" data-sale-id="{{ $sale->id }}">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="fw-semibold">
                                            {{ $sale->created_at->format('d/m/Y') }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $sale->created_at->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="customer-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ substr($sale->customer->name ?? 'GU', 0, 2) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $sale->customer->name ?? 'Tanpa Customer' }}</div>
                                                <small class="text-muted">{{ $sale->customer->phone ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill">
                                            {{ $saleTotalItem }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-primary">
                                            Rp{{ number_format($saleTotalJual, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="fw-bold {{ $saleTotalLaba >= 0 ? 'text-success' : 'text-danger' }}">
                                                Rp{{ number_format($saleTotalLaba, 0, ',', '.') }}
                                            </span>
                                            <small class="text-muted">
                                                {{ number_format($saleMargin, 1) }}%
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary toggle-detail" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#detail-{{ $sale->id }}"
                                                aria-expanded="false">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Detail Items -->
                                <tr class="detail-row">
                                    <td colspan="8" class="p-0 border-0">
                                        <div class="collapse" id="detail-{{ $sale->id }}">
                                            <div class="p-3 bg-light border-top">
                                                <h6 class="fw-semibold mb-3">
                                                    <i class="fas fa-box-open me-2"></i>
                                                    Detail Item Transaksi #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                                                </h6>
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th class="text-center">#</th>
                                                                <th>Jenis</th>
                                                                <th>Produk/Aksesoris</th>
                                                                <th>IMEI/Serial</th>
                                                                <th>Merek</th>
                                                                <th class="text-end">Harga Beli</th>
                                                                <th class="text-end">Harga Jual</th>
                                                                <th class="text-end">Laba</th>
                                                                <th class="text-center">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php $itemNo = 1; @endphp
                                                            
                                                            <!-- Produk HP -->
                                                            @foreach($sale->items ?? [] as $item)
                                                                @php
                                                                    $hargaJual = floatval($item->price ?? 0);
                                                                    $hargaBeli = floatval($item->inventoryItem->purchase_price ?? 0);
                                                                    $laba = $hargaJual - $hargaBeli;
                                                                    $margin = $hargaJual > 0 ? ($laba / $hargaJual) * 100 : 0;
                                                                @endphp
                                                                <tr>
                                                                    <td class="text-center">{{ $itemNo++ }}</td>
                                                                    <td>
                                                                        <span class="badge bg-primary rounded-pill">HP</span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="fw-semibold">{{ $item->product->name ?? '-' }}</div>
                                                                        @if($item->product && $item->product->color)
                                                                            <small class="text-muted">Color: {{ $item->product->color }}</small>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <code class="text-dark">{{ $item->imei ?? '-' }}</code>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-info bg-opacity-10 text-info">
                                                                            {{ $item->product->brand->name ?? '-' }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-muted">Rp{{ number_format($hargaBeli, 0, ',', '.') }}</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="fw-semibold">Rp{{ number_format($hargaJual, 0, ',', '.') }}</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="fw-bold {{ $laba >= 0 ? 'text-success' : 'text-danger' }}">
                                                                            Rp{{ number_format($laba, 0, ',', '.') }}
                                                                        </span>
                                                                        <br>
                                                                        <small class="text-muted">{{ number_format($margin, 1) }}%</small>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-success rounded-pill">Terjual</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            
                                                            <!-- Accessories -->
                                                            @foreach($sale->accessories ?? [] as $acc)
                                                                @php
                                                                    $hargaJual = floatval($acc->price ?? 0);
                                                                    $hargaBeli = floatval($acc->purchaseAccessory->price ?? 0);
                                                                    $laba = $hargaJual - $hargaBeli;
                                                                    $margin = $hargaJual > 0 ? ($laba / $hargaJual) * 100 : 0;
                                                                @endphp
                                                                <tr>
                                                                    <td class="text-center">{{ $itemNo++ }}</td>
                                                                    <td>
                                                                        <span class="badge bg-warning rounded-pill">Aksesoris</span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="fw-semibold">{{ $acc->accessory->name ?? '-' }}</div>
                                                                        <small class="text-muted">Qty: {{ $acc->qty ?? 1 }}</small>
                                                                    </td>
                                                                    <td>
                                                                        <span class="text-muted">-</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                                            Aksesoris
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="text-muted">Rp{{ number_format($hargaBeli, 0, ',', '.') }}</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="fw-semibold">Rp{{ number_format($hargaJual, 0, ',', '.') }}</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span class="fw-bold {{ $laba >= 0 ? 'text-success' : 'text-danger' }}">
                                                                            Rp{{ number_format($laba, 0, ',', '.') }}
                                                                        </span>
                                                                        <br>
                                                                        <small class="text-muted">{{ number_format($margin, 1) }}%</small>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-success rounded-pill">Terjual</span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            
                                                            @if(count($sale->items ?? []) == 0 && count($sale->accessories ?? []) == 0)
                                                                <tr>
                                                                    <td colspan="9" class="text-center text-muted py-3">
                                                                        <i class="fas fa-exclamation-circle me-2"></i>
                                                                        Tidak ada item dalam transaksi ini
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                        <tfoot class="table-light">
                                                            <tr>
                                                                <th colspan="5" class="text-end">Subtotal Transaksi:</th>
                                                                <th class="text-end">
                                                                    @php
                                                                        $totalBeli = 0;
                                                                        foreach($sale->items ?? [] as $item) {
                                                                            $totalBeli += floatval($item->inventoryItem->purchase_price ?? 0);
                                                                        }
                                                                        foreach($sale->accessories ?? [] as $acc) {
                                                                            $totalBeli += floatval($acc->purchaseAccessory->price ?? 0);
                                                                        }
                                                                    @endphp
                                                                    Rp{{ number_format($totalBeli, 0, ',', '.') }}
                                                                </th>
                                                                <th class="text-end">Rp{{ number_format($saleTotalJual, 0, ',', '.') }}</th>
                                                                <th class="text-end">
                                                                    <span class="fw-bold {{ $saleTotalLaba >= 0 ? 'text-success' : 'text-danger' }}">
                                                                        Rp{{ number_format($saleTotalLaba, 0, ',', '.') }}
                                                                    </span>
                                                                </th>
                                                                <th class="text-center">
                                                                    {{ number_format($saleMargin, 1) }}%
                                                                </th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="5" class="text-end">TOTAL KESELURUHAN:</th>
                                <th class="text-center">{{ $totalItem }} Item</th>
                                <th class="text-end fw-bold text-primary">
                                    Rp{{ number_format($totalPendapatan, 0, ',', '.') }}
                                </th>
                                <th class="text-end fw-bold {{ $totalLaba >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp{{ number_format($totalLaba, 0, ',', '.') }}
                                </th>
                                <th class="text-center fw-bold">
                                    {{ number_format($totalMargin, 1) }}%
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- CUSTOMER SUMMARY -->
        @php
            $customerTransactions = [];
            foreach($sales as $sale) {
                if($sale->customer) {
                    $customerId = $sale->customer->id;
                    if(!isset($customerTransactions[$customerId])) {
                        $customerTransactions[$customerId] = [
                            'name' => $sale->customer->name,
                            'phone' => $sale->customer->phone,
                            'transactions' => 0,
                            'total' => 0
                        ];
                    }
                    // Hitung total per transaksi
                    $transactionTotal = 0;
                    foreach($sale->items ?? [] as $item) {
                        $transactionTotal += floatval($item->price ?? 0);
                    }
                    foreach($sale->accessories ?? [] as $acc) {
                        $transactionTotal += floatval($acc->price ?? 0);
                    }
                    
                    $customerTransactions[$customerId]['transactions']++;
                    $customerTransactions[$customerId]['total'] += $transactionTotal;
                }
            }
        @endphp
        
        @if(count($customerTransactions) > 0)
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="fas fa-users me-2"></i>Ringkasan Customer
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($customerTransactions as $customer)
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="customer-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 40px; height: 40px; font-size: 1rem;">
                                    {{ substr($customer['name'], 0, 2) }}
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-0">{{ $customer['name'] }}</h6>
                                    <small class="text-muted">{{ $customer['phone'] ?? '-' }}</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <div class="text-center">
                                    <div class="text-muted">Transaksi</div>
                                    <div class="fw-bold">{{ $customer['transactions'] }}</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-muted">Total Belanja</div>
                                    <div class="fw-bold text-primary">
                                        Rp{{ number_format($customer['total'], 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div class="text-muted">Avg/Transaksi</div>
                                    <div class="fw-bold">
                                        Rp{{ number_format($customer['total'] / $customer['transactions'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    @else
        <!-- EMPTY STATE -->
        <div class="text-center py-5 my-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-store-alt fa-5x text-light" style="opacity: 0.3;"></i>
            </div>
            <h4 class="text-muted mb-3">Tidak ada data penjualan</h4>
            <p class="text-muted mb-4">
                Cabang {{ $branch->name }} tidak memiliki transaksi 
                pada periode {{ \Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y') }} - 
                {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') }}
            </p>
            <a href="{{ url()->previous() }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    @endif
</div>

<style>
    /* Custom CSS */
    .summary-card {
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
        border-left-width: 4px !important;
    }
    
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
    }
    
    .summary-card .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .sale-header {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .sale-header:hover {
        background-color: rgba(0,0,0,0.02) !important;
    }
    
    .detail-row > td {
        border-top: none !important;
        padding: 0 !important;
    }
    
    .customer-avatar {
        font-weight: bold;
        text-transform: uppercase;
    }
    
    /* Table styling */
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        color: #6c757d;
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table tbody tr {
        border-bottom: 1px solid #f1f1f1;
    }
    
    code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }
    
    .badge.rounded-pill {
        font-weight: 500;
        padding: 4px 10px;
    }
    
    .empty-state-icon {
        opacity: 0.1;
    }
    
    /* Toggle button animation */
    .toggle-detail i {
        transition: transform 0.3s ease;
    }
    
    .toggle-detail[aria-expanded="true"] i {
        transform: rotate(180deg);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.9rem;
        }
        
        .customer-avatar {
            width: 28px !important;
            height: 28px !important;
            font-size: 0.7rem !important;
        }
    }
</style>

<script>
    // JavaScript untuk toggle detail yang FIXED
    document.addEventListener('DOMContentLoaded', function() {
        // Setup toggle buttons
        const toggleButtons = document.querySelectorAll('.toggle-detail');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                
                const targetId = this.getAttribute('data-bs-target');
                const target = document.querySelector(targetId);
                
                // Toggle aria-expanded attribute
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                
                // Rotate icon
                const icon = this.querySelector('i');
                if (!isExpanded) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });
        
        // Toggle all details
        window.toggleAllDetails = function() {
            const buttons = document.querySelectorAll('.toggle-detail');
            const collapses = document.querySelectorAll('.collapse');
            
            // Check if all are expanded
            const allExpanded = Array.from(buttons).every(button => 
                button.getAttribute('aria-expanded') === 'true'
            );
            
            // Toggle all
            buttons.forEach(button => {
                const targetId = button.getAttribute('data-bs-target');
                const target = document.querySelector(targetId);
                
                if (allExpanded) {
                    // Collapse all
                    target.classList.remove('show');
                    button.setAttribute('aria-expanded', 'false');
                    button.querySelector('i').style.transform = 'rotate(0deg)';
                } else {
                    // Expand all
                    target.classList.add('show');
                    button.setAttribute('aria-expanded', 'true');
                    button.querySelector('i').style.transform = 'rotate(180deg)';
                }
            });
        };
        
        // Collapse all details
        window.collapseAllDetails = function() {
            const buttons = document.querySelectorAll('.toggle-detail');
            const collapses = document.querySelectorAll('.collapse');
            
            buttons.forEach(button => {
                const targetId = button.getAttribute('data-bs-target');
                const target = document.querySelector(targetId);
                
                // Collapse
                target.classList.remove('show');
                button.setAttribute('aria-expanded', 'false');
                button.querySelector('i').style.transform = 'rotate(0deg)';
            });
        };
        
        // Click on sale header to toggle details
        const saleHeaders = document.querySelectorAll('.sale-header');
        saleHeaders.forEach(header => {
            header.addEventListener('click', function(e) {
                // Don't trigger if clicking on button or link
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || 
                    e.target.closest('button') || e.target.closest('a')) {
                    return;
                }
                
                const toggleButton = this.querySelector('.toggle-detail');
                if (toggleButton) {
                    toggleButton.click();
                }
            });
        });
    });
</script>

<!-- Include Bootstrap JS for collapse functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection