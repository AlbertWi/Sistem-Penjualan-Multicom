@extends('layouts.app')

@section('title', 'Rekap Stok')

@section('content')
<div class="container">
    <h4 class="mb-4">Rekap Stok</h4>
        <form method="GET" action="">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select name="branch_id" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    {{ $branchId == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Produk</th>
                <th>Total Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $brand => $items)
                <tr class="table-secondary">
                    <th colspan="2" class="text-uppercase">{{ $brand }}</th>
                </tr>
                @foreach($items as $row)
                    <tr>
                        <td>{{ trim($row->base_name) }}</td>
                        <td>{{ $row->total_stok }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>Total Semua</th>
                <th>{{ $totalQty }}</th>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
