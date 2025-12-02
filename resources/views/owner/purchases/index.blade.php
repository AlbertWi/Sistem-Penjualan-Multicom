@extends('layouts.app')

@section('title', 'Pelunasan Pembelian')

@section('content')
<div class="container-fluid">
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
    <h4 class="mb-4">Pelunasan Pembelian</h4>

    <!-- Filter Cabang dan Status -->
    <form method="GET" action="{{ route('owner.purchases.index') }}" class="mb-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Filter Cabang</label>
                <select name="branch_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Cabang --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Filter Status</label>
                <select name="status_filter" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="blm_lunas" {{ request('status_filter') == 'blm_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    <option value="lunas" {{ request('status_filter') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <a href="{{ route('owner.purchases.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-redo"></i> Reset Filter
                </a>
            </div>
        </div>
    </form>

    <!-- Rekap Total -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h6 class="text-white-50 mb-2">Total Semua</h6>
                    <h4 class="mb-0 fw-bold">Rp {{ number_format($totalSemua, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <h6 class="text-white-50 mb-2">Total Belum Lunas</h6>
                    <h4 class="mb-0 fw-bold">Rp {{ number_format($totalBlmLunas, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                    <h6 class="text-white-50 mb-2">Total Lunas</h6>
                    <h4 class="mb-0 fw-bold">Rp {{ number_format($totalSemua - $totalBlmLunas, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Pembelian -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Daftar Pembelian</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th class="px-4 py-3" style="width: 60px;">No</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Cabang</th>
                            <th class="py-3">Supplier</th>
                            <th class="py-3">Total</th>
                            <th class="py-3" style="width: 120px;">Status</th>
                            <th class="py-3" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td class="px-4 text-muted">
                                {{ $loop->iteration + ($purchases->currentPage()-1) * $purchases->perPage() }}
                            </td>
                            <td>{{ $purchase->created_at->format('d-m-Y') }}</td>
                            <td class="fw-medium">{{ $purchase->branch->name }}</td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td class="fw-bold text-primary">
                                Rp {{ number_format($purchase->total, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($purchase->status == 'blm lunas')
                                    <span class="badge bg-danger px-3 py-2">BELUM LUNAS</span>
                                @else
                                    <span class="badge bg-success px-3 py-2">LUNAS</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $purchase->id }}">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </button>

                                   @if($purchase->status == 'blm lunas')
                                        <form action="{{ route('owner.purchases.pelunasan', $purchase->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Yakin ingin melunasi pembelian ini?')">
                                                <i class="fas fa-check-circle me-1"></i> Lunasi
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                Tidak ada data pembelian
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($purchases->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted small">
                    Menampilkan {{ $purchases->firstItem() ?? 0 }} - {{ $purchases->lastItem() ?? 0 }} dari {{ $purchases->total() }} data
                </div>
                <div>
                    {{ $purchases->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Detail -->
@foreach($purchases as $purchase)
@php
    // Hitung total harga jual produk dari sale_items berdasarkan IMEI
    $totalJualProduk = 0;
    foreach($purchase->items as $item) {
        foreach($item->inventoryItems as $inv) {
            $saleItem = DB::table('sale_items')->where('imei', $inv->imei)->first();
            if($saleItem) {
                $totalJualProduk += $saleItem->price;
            }
        }
    }
    
    // Hitung total harga jual accessories dari sale_accessories
    $totalJualAccessories = 0;
    foreach($purchase->accessories as $acc) {
        $saleAcc = DB::table('sale_accessories')
            ->where('accessory_id', $acc->accessory_id)
            ->first();
        if($saleAcc) {
            $totalJualAccessories += ($saleAcc->price * $acc->qty);
        }
    }
@endphp
<div class="modal fade" id="detailModal{{ $purchase->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $purchase->id }}" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h5 class="modal-title" id="detailModalLabel{{ $purchase->id }}">
            <i class="fas fa-list-alt me-2"></i>Detail Pembelian
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Cabang</p>
                <p class="fw-bold">{{ $purchase->branch->name }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1 text-muted small">Supplier</p>
                <p class="fw-bold">{{ $purchase->supplier->name }}</p>
            </div>
        </div>
        
        <hr>
        
        <!-- Items Produk -->
        @if($purchase->items->count() > 0)
        <h6 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>Daftar Produk</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered align-middle">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="py-2">Produk</th>
                        <th class="py-2">IMEI</th>
                        <th class="py-2 text-end">Harga Modal</th>
                        <th class="py-2 text-end">Harga Jual</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotalModalProduk = 0; $subtotalJualProduk = 0; @endphp
                    @foreach($purchase->items as $item)
                        @foreach($item->inventoryItems as $inv)
                            @php
                                $saleItem = DB::table('sale_items')->where('imei', $inv->imei)->first();
                                $hargaJual = $saleItem ? $saleItem->price : 0;
                                $subtotalModalProduk += $item->price;
                                $subtotalJualProduk += $hargaJual;
                            @endphp
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $inv->imei }}</td>
                                <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($hargaJual, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
                <tfoot style="background-color: #f8f9fa;">
                    <tr>
                        <td colspan="2" class="text-end fw-bold">SUBTOTAL PRODUK:</td>
                        <td class="text-end fw-bold">Rp {{ number_format($subtotalModalProduk, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-success">Rp {{ number_format($subtotalJualProduk, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- Accessories -->
        @if($purchase->accessories->count() > 0)
        <h6 class="mb-3"><i class="fas fa-box me-2"></i>Daftar Accessories</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-bordered align-middle">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="py-2">Nama Accessories</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-end">Harga Modal/pcs</th>
                        <th class="py-2 text-end">Harga Jual/pcs</th>
                        <th class="py-2 text-end">Total Modal</th>
                        <th class="py-2 text-end">Total Jual</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotalModalAcc = 0; $subtotalJualAcc = 0; @endphp
                    @foreach($purchase->accessories as $acc)
                        @php
                            $saleAcc = DB::table('sale_accessories')
                                ->where('accessory_id', $acc->accessory_id)
                                ->first();
                            $hargaJualPerPcs = $saleAcc ? $saleAcc->price : 0;
                            $totalModal = $acc->qty * $acc->price;
                            $totalJual = $acc->qty * $hargaJualPerPcs;
                            $subtotalModalAcc += $totalModal;
                            $subtotalJualAcc += $totalJual;
                        @endphp
                        <tr>
                            <td>{{ $acc->accessory->name }}</td>
                            <td class="text-center">{{ $acc->qty }}</td>
                            <td class="text-end">Rp {{ number_format($acc->price, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($hargaJualPerPcs, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($totalModal, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($totalJual, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background-color: #f8f9fa;">
                    <tr>
                        <td colspan="4" class="text-end fw-bold">SUBTOTAL ACCESSORIES:</td>
                        <td class="text-end fw-bold">Rp {{ number_format($subtotalModalAcc, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-success">Rp {{ number_format($subtotalJualAcc, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- Grand Total -->
        <div class="card border-primary">
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-6 text-end">
                        <h5 class="mb-0">GRAND TOTAL MODAL:</h5>
                    </div>
                    <div class="col-6">
                        <h5 class="mb-0 text-primary fw-bold">Rp {{ number_format($purchase->total, 0, ',', '.') }}</h5>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row">
                    <div class="col-6 text-end">
                        <h5 class="mb-0">GRAND TOTAL JUAL:</h5>
                    </div>
                    <div class="col-6">
                        <h5 class="mb-0 text-success fw-bold">
                            Rp {{ number_format($totalJualProduk + $totalJualAccessories, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row">
                    <div class="col-6 text-end">
                        <h5 class="mb-0 text-info">ESTIMASI PROFIT:</h5>
                    </div>
                    <div class="col-6">
                        <h5 class="mb-0 fw-bold text-info">
                            Rp {{ number_format(($totalJualProduk + $totalJualAccessories) - $purchase->total, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endforeach

<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }
    
    .btn-group .btn {
        border-radius: 0;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 0.25rem;
        border-bottom-left-radius: 0.25rem;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
    
    .card {
        border-radius: 0.5rem;
    }
    
    .badge {
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .modal-xl {
        max-width: 1200px;
    }
</style>
@endsection