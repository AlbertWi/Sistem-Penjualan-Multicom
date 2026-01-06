@extends('layouts.app')

@section('title', 'Penjualan - Owner')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Daftar Penjualan (Owner)</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <strong>Data Penjualan</strong>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>No Invoice</th>
                        <th>Konsumen</th>
                        <th>Cabang</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $sale->created_at->format('d-m-Y H:i') }}</td>
                            <td>{{ $sale->invoice_number }}</td>
                            <td>{{ $sale->customer->name ?? '-' }}</td>
                            <td>{{ $sale->branch->name ?? '-' }}</td>
                            <td>Rp {{ number_format($sale->total_price) }}</td>
                            <td>
                                @if($sale->is_paid)
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-warning text-dark">Belum Lunas</span>
                                @endif
                            </td>
                            <td>
                                {{-- EDIT PENJUALAN --}}
                                <a href="{{ route('owner.sales.edit', $sale->id) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                {{-- DETAIL (opsional) --}}
                                <a href="{{ route('sales.show', $sale->id) }}"
                                   class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Data penjualan belum ada</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $sales->links() }}
        </div>
    </div>
</div>
@endsection
