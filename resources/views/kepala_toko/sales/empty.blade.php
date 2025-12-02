@extends('layouts.app')

@section('title', 'Barang Keluar')

@section('content')
<div class="container text-center mt-5">
    <h2 class="mb-4">Belum Ada Penjualan</h2>
    <p class="text-muted mb-4">Saat ini belum ada data penjualan di cabang ini.</p>

    <a href="{{ route('sales.create') }}" class="btn btn-primary btn-lg">
        + Tambah Penjualan Pertama
    </a>
</div>
@endsection
