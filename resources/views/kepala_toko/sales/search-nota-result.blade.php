@extends('layouts.app')

@section('title', 'Hasil Pencarian Nota')

@section('content')
<div class="container">
    <h4 class="mb-3">Hasil Pencarian Nota</h4>

    @if($sales->isEmpty())
        <div class="alert alert-warning">Tidak ada hasil pencarian.</div>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Nota</th>
                    <th>Cabang</th>
                    <th>Total</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->branch->name ?? '-' }}</td>
                    <td>{{ number_format($sale->total,0,',','.') }}</td>
                    <td>{{ $sale->created_at->format('d-m-Y H:i') }}</td>
                    <td>
                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-primary">
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('sales.index') }}" class="btn btn-secondary mt-3">Kembali</a>
</div>
@endsection
