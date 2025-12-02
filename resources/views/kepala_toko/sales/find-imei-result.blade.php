@extends('layouts.app')

@section('title', 'Hasil Pencarian IMEI')

@section('content')
<div class="container">
    <h4 class="mb-3">Hasil Pencarian IMEI</h4>

    @if($results->isEmpty())
        <div class="alert alert-warning">Tidak ada history untuk IMEI ini.</div>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>IMEI</th>
                    <th>Produk</th>
                    <th>Sumber</th>
                    <th>Cabang / Supplier</th>
                    <th>ID Referensi</th>
                    <th>Tanggal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($results as $item)
                <tr>
                    <td>{{ $item->imei }}</td>
                    <td>{{ $item->product }}</td>

                    <td>
                        @if($item->source == 'penjualan')
                            <span class="badge bg-danger">Penjualan</span>
                        @elseif($item->source == 'pembelian')
                            <span class="badge bg-success">Pembelian</span>
                        @else
                            <span class="badge bg-info">Transfer</span>
                        @endif
                    </td>

                    <td>{{ $item->branch }}</td>

                    <td>
                        @if($item->source == 'penjualan')
                            <a href="{{ url('sales/'.$item->ref_id) }}">#{{ $item->ref_id }}</a>
                        @elseif($item->source == 'pembelian')
                            <a href="{{ url('purchases/'.$item->ref_id) }}">#{{ $item->ref_id }}</a>
                        @else
                            <a href="{{ url('stock-transfers/'.$item->ref_id) }}">#{{ $item->ref_id }}</a>
                        @endif
                    </td>

                    <td>{{ $item->date }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Hasil Pencarian IMEI')

@section('content')
<div class="container">
    <h4 class="mb-3">Hasil Pencarian IMEI</h4>

    @if($results->isEmpty())
        <div class="alert alert-warning">Tidak ada history untuk IMEI ini.</div>
    @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>IMEI</th>
                    <th>Produk</th>
                    <th>Sumber</th>
                    <th>Cabang / Supplier</th>
                    <th>ID Referensi</th>
                    <th>Tanggal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($results as $item)
                <tr>
                    <td>{{ $item->imei }}</td>
                    <td>{{ $item->product }}</td>

                    <td>
                        @if($item->source == 'penjualan')
                            <span class="badge bg-danger">Penjualan</span>
                        @elseif($item->source == 'pembelian')
                            <span class="badge bg-success">Pembelian</span>
                        @else
                            <span class="badge bg-info">Transfer</span>
                        @endif
                    </td>

                    <td>{{ $item->branch }}</td>

                    <td>
                        @if($item->source == 'penjualan')
                            <a href="{{ url('sales/'.$item->ref_id) }}">#{{ $item->ref_id }}</a>
                        @elseif($item->source == 'pembelian')
                            <a href="{{ url('purchases/'.$item->ref_id) }}">#{{ $item->ref_id }}</a>
                        @else
                            <a href="{{ url('stock-transfers/'.$item->ref_id) }}">#{{ $item->ref_id }}</a>
                        @endif
                    </td>

                    <td>{{ $item->date }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
