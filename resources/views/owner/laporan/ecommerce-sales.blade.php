@extends('layouts.app')

@section('title', 'Laporan Penjualan E-Commerce')

@section('content')
<div class="container-fluid">

    {{-- ===================== --}}
    {{-- HEADER --}}
    {{-- ===================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Laporan Penjualan E-Commerce</h3>

        <form method="GET" class="d-flex gap-2">
            <input type="date" name="start_date" class="form-control"
                   value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
            <input type="date" name="end_date" class="form-control"
                   value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
            <button class="btn btn-primary">Filter</button>
        </form>
    </div>

    {{-- ===================== --}}
    {{-- SUMMARY CARD --}}
    {{-- ===================== --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Order</h6>
                    <h3 class="fw-bold">{{ $totalOrders }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Produk Terjual</h6>
                    <h3 class="fw-bold">{{ $totalItemsSold }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Pendapatan</h6>
                    <h3 class="fw-bold text-success">
                        Rp {{ number_format($totalRevenue, 2, ',', '.') }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== --}}
    {{-- TABEL ORDER --}}
    {{-- ===================== --}}
    <div class="card shadow-sm">
        <div class="card-header fw-bold">
            Detail Order E-Commerce
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No Order</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Cabang</th>
                        <th>Total Item</th>
                        <th>Total Bayar</th>
                        <th>Status Order</th>
                        <th>Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->order_date->format('d M Y') }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ $order->branch->name ?? '-' }}</td>
                            <td class="text-center">
                                {{ $order->items->sum('quantity') }}
                            </td>
                            <td class="fw-semibold">
                                Rp {{ number_format($order->total_amount, 2, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    {{ $order->payment_status === 'paid' ? 'bg-primary' : 'bg-warning' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Tidak ada data penjualan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
