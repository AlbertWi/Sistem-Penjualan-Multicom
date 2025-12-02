@extends('layouts.app')

@section('title', 'Laporan Stok')

@section('content')
<div class="container">
    <h4 class="mb-4">Laporan Stok</h4>

    {{-- Filter cabang --}}
    <form method="GET" action="{{ route('owner.stocksReport.index') }}" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <select name="branch_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($allBranches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                @if($branchId)
                    <a href="{{ route('owner.stocksReport.print', ['branch_id' => $branchId]) }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-print"></i> Print
                    </a>
                @endif
            </div>
        </div>
    </form>

    @foreach($branches as $branch)
        <div class="card mb-4">
            <div class="card-header">
                <strong>Cabang: {{ $branch->name }}</strong>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>IMEI</th>
                            <th>Brand</th>
                            <th>Type</th>
                            <th>Harga Modal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $totalQty = 0; 
                            $totalHarga = 0; 
                        @endphp
                        @forelse($branch->inventoryItems as $item)
                            @php
                                $harga = $item->purchaseItem->price ?? 0;
                                $totalQty++;
                                $totalHarga += $harga;
                            @endphp
                            <tr>
                                <td>{{ $item->imei }}</td>
                                <td>{{ $item->product->brand->name ?? '-' }}</td>
                                <td>{{ $item->product->type->name ?? '-' }}</td>
                                <td>Rp {{ number_format($harga, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada stok</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total</th>
                            <th>Rp {{ number_format($totalHarga, 0, ',', '.') }} ({{ $totalQty }} Unit)</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
