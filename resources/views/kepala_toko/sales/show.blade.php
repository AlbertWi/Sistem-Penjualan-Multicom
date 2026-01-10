@extends('layouts.app')

@section('title', 'Detail Barang Keluar')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detail Barang Keluar #{{ $sale->id }}</h1>

        <div class="d-flex">
            <!-- Tombol Tambah Penjualan -->
            <a href="{{ route('sales.create') }}" class="btn btn-primary me-2">+ Tambah Penjualan</a>

            <!-- Tombol Print -->
            <a href="{{ route('sales.print', $sale->id) }}" target="_blank" class="btn btn-success me-2">
                🖨️ Print Nota
            </a>
        </div>
    </div>

    {{-- Navigasi Previous & Next --}}
    <div class="mb-3 d-flex justify-content-between">
        @php
            $prev = \App\Models\Sale::where('id', '<', $sale->id)
                ->where('branch_id', auth()->user()->branch_id)
                ->orderBy('id', 'desc')
                ->first();

            $next = \App\Models\Sale::where('id', '>', $sale->id)
                ->where('branch_id', auth()->user()->branch_id)
                ->orderBy('id', 'asc')
                ->first();
        @endphp

        <div>
            @if($prev)
                <a href="{{ route('sales.show', $prev->id) }}" class="btn btn-outline-secondary">← Previous</a>
            @endif
        </div>

        <div>
            @if($next)
                <a href="{{ route('sales.show', $next->id) }}" class="btn btn-outline-secondary">Next →</a>
            @endif
        </div>
    </div>

    {{-- Detail Info --}}
    <div class="mb-2"><strong>Tanggal:</strong> {{ $sale->created_at->format('d-m-Y H:i') }}</div>
    <div class="mb-2"><strong>Cabang:</strong> {{ $sale->branch->name ?? '-' }}</div>
    <div class="mb-2"><strong>Jumlah HP:</strong> {{ $sale->items->whereNotNull('imei')->count() }}</div>
    <div class="mb-2"><strong>Customer:</strong> {{ $sale->customer->name ?? '-' }}</div>
    <div class="mb-3"><strong>Total Harga:</strong> Rp{{ number_format($sale->total, 0, ',', '.') }}</div>

    {{-- Tabel HP --}}
    <h4 class="mt-4">Daftar HP Terjual</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-secondary">
            <tr>
                <th>ID Produk</th>
                <th>Nama Produk</th>
                <th>IMEI</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sale->items->whereNotNull('imei') as $item)
                <tr>
                    <td>{{ $item->product->id ?? '-' }}</td>
                    <td>{{ $item->product->brand->name ?? '' }} {{ $item->product->model ?? $item->product->name }}</td>
                    <td>{{ $item->imei }}</td>
                    <td>Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Tidak ada HP dalam Barang Keluar ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tabel Aksesoris --}}
    <h4 class="mt-4">Daftar Aksesoris Terjual</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-secondary">
            <tr>
                <th>ID Aksesoris</th>
                <th>Nama Aksesoris</th>
                <th>Qty</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sale->accessories as $acc)
                <tr>
                    <td>{{ $acc->accessory->id ?? '-' }}</td>
                    <td>{{ $acc->accessory->name ?? '-' }}</td>
                    <td>{{ $acc->qty }}</td>
                    <td>Rp{{ number_format($acc->price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Tidak ada aksesoris dalam Barang Keluar ini.</td></tr>
            @endforelse
        </tbody>
    </table>

</div>

<!-- Modal Search Nota -->
<div class="modal fade" id="searchNotaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cari Nota Penjualan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('sales.searchNota') }}" method="GET" class="mb-3 d-flex">
          <input type="text" name="q" class="form-control" placeholder="Masukkan ID Nota">
          <button type="submit" class="btn btn-secondary ms-2">Search</button>
        </form>
        <div id="searchNotaResult">
          {{-- hasil pencarian nota muncul di sini --}}
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Find IMEI -->
<div class="modal fade" id="findImeiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cari History IMEI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('sales.findImei') }}" method="GET" class="mb-3 d-flex">
          <input type="text" name="imei" class="form-control" placeholder="Masukkan IMEI / gunakan % untuk sebagian angka">
          <button type="submit" class="btn btn-info ms-2">Find</button>
        </form>
        <div id="findImeiResult">
          {{-- hasil pencarian imei muncul di sini --}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
