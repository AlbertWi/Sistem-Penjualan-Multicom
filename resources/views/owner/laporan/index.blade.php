@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Laporan Penjualan Cabang</h4>
        @if (!empty($penjualan) && $penjualan->count())
            <form method="GET" action="{{ route('sales.export-pdf') }}" style="display: inline;">
                <input type="hidden" name="tanggal_awal" value="{{ $tanggalAwal }}">
                <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                <input type="hidden" name="branch_id" value="{{ $branchId ?? '' }}">
                <input type="hidden" name="customer_id" value="{{ $customerId ?? '' }}">
                <input type="hidden" name="brand_id" value="{{ $brandId ?? '' }}">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </form>
        @endif
    </div>

    {{-- FILTER --}}
    <form method="GET" class="row mb-3">
        <div class="col-md-2">
            <label>Tanggal Awal</label>
            <input type="date" name="tanggal_awal" class="form-control" value="{{ $tanggalAwal }}">
        </div>
        <div class="col-md-2">
            <label>Tanggal Akhir</label>
            <input type="date" name="tanggal_akhir" class="form-control" value="{{ $tanggalAkhir }}">
        </div>
        <div class="col-md-2">
            <label>Cabang</label>
            <select name="branch_id" class="form-control">
                <option value="">Semua Cabang</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (isset($branchId) && $branchId == $branch->id) ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Customer</label>
            <select name="customer_id" class="form-control">
                <option value="">Semua Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ (isset($customerId) && $customerId == $customer->id) ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label>Merek</label>
            <select name="brand_id" class="form-control">
                <option value="">Semua Merek</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ (isset($brandId) && $brandId == $brand->id) ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button class="btn btn-primary w-100">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    @if (!empty($penjualan) && $penjualan->count())
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pendapatan</h5>
                    <h3>Rp{{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Laba</h5>
                    <h3>Rp{{ number_format($totalLaba ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Data grouped by customer --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Data Penjualan
                @if($tanggalAwal || $tanggalAkhir)
                    - {{ $tanggalAwal ? \Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y') : 'Awal' }} 
                    s/d {{ $tanggalAkhir ? \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') : 'Sekarang' }}
                @endif
                @if($branchId)
                    - {{ $branches->find($branchId)->name ?? 'Cabang' }}
                @endif
            </h5>
        </div>

        <div class="card-body">
            @php
                // fallback grouping jika controller belum mengirim penjualanGrouped
                $grouped = $penjualanGrouped ?? ($penjualan->groupBy(function($sale) { return $sale->customer_id ?? 'no_customer'; }));
            @endphp

            @foreach($grouped as $customerKey => $salesByCustomer)
                @php
                    $firstSale = $salesByCustomer->first();
                    $customerName = $firstSale->customer->name ?? 'Umum / Tanpa Customer';
                    // subtotal per customer
                    $subtotalPendapatan = 0;
                    $subtotalLaba = 0;
                @endphp

                <div class="mb-3">
                    <h5 class="mb-1">
                        Customer: <strong>{{ $customerName }}</strong>
                        <small class="text-muted"> ({{ $salesByCustomer->count() }} nota)</small>
                    </h5>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Cabang</th>
                                    <th>Produk</th>
                                    <th>IMEI</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Laba</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach ($salesByCustomer as $sale)
                                    {{-- Produk HP --}}
                                    @foreach ($sale->items ?? [] as $item)
                                        @php
                                            $hargaJual = floatval($item->price ?? 0);
                                            $hargaBeli = floatval($item->inventoryItem->purchase_price ?? 0);
                                            $laba = $hargaJual - $hargaBeli;
                                            $subtotalPendapatan += $hargaJual;
                                            $subtotalLaba += $laba;
                                        @endphp
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                                            <td>{{ $sale->branch->name ?? '-' }}</td>
                                            <td>{{ $item->product->name ?? '-' }}</td>
                                            <td>{{ $item->imei }}</td>
                                            <td>Rp{{ number_format($hargaBeli, 0, ',', '.') }}</td>
                                            <td>Rp{{ number_format($hargaJual, 0, ',', '.') }}</td>
                                            <td class="{{ $laba >= 0 ? 'text-success' : 'text-danger' }}">
                                                Rp{{ number_format($laba, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Accessories --}}
                                    @foreach ($sale->accessories ?? [] as $acc)
                                        @php
                                            $hargaJual = floatval($acc->price ?? 0);
                                            $hargaBeli = floatval($acc->purchaseAccessory->price ?? 0);
                                            $laba = $hargaJual - $hargaBeli;
                                            $subtotalPendapatan += $hargaJual;
                                            $subtotalLaba += $laba;
                                        @endphp
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                                            <td>{{ $sale->branch->name ?? '-' }}</td>
                                            <td>{{ $acc->accessory->name ?? '-' }}</td>
                                            <td>-</td>
                                            <td>Rp{{ number_format($hargaBeli, 0, ',', '.') }}</td>
                                            <td>Rp{{ number_format($hargaJual, 0, ',', '.') }}</td>
                                            <td class="{{ $laba >= 0 ? 'text-success' : 'text-danger' }}">
                                                Rp{{ number_format($laba, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <th colspan="5" class="text-end">Subtotal {{ $customerName }}</th>
                                    <th> - </th>
                                    <th>Rp{{ number_format($subtotalPendapatan, 0, ',', '.') }}</th>
                                    <th class="{{ $subtotalLaba >= 0 ? 'text-success' : 'text-danger' }}">
                                        Rp{{ number_format($subtotalLaba, 0, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @else
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <h5>Tidak ada data penjualan</h5>
            <p class="mb-0">Tidak ada data penjualan untuk filter yang dipilih.</p>
        </div>
    @endif
</div>
@endsection
