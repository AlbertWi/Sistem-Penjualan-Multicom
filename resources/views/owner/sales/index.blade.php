@extends('layouts.app')
@section('title', 'Pelunasan Penjualan')
@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-money-bill-wave"></i> Pelunasan Penjualan</h4>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="branch_id" class="form-label fw-bold">Filter Cabang</label>
                        <select name="branch_id" id="branch_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Cabang --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status_filter" class="form-label fw-bold">Filter Status</label>
                        <select name="status_filter" id="status_filter" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="blm_lunas" {{ request('status_filter') == 'blm_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="lunas" {{ request('status_filter') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <a href="{{ route('owner.sales.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-redo"></i> Reset Filter
                        </a>
                    </div>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Seluruh Penjualan</h6>
                            <h3 class="text-primary fw-bold">Rp {{ number_format($totalSeluruh, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Belum Lunas</h6>
                            <h3 class="text-danger fw-bold">Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h6 class="text-muted">Total Sudah Lunas</h6>
                            <h3 class="text-success fw-bold">Rp {{ number_format($totalLunas, 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="12%">Tanggal</th>
                            <th width="15%">Nama Customer</th>
                            <th width="15%">Cabang</th>
                            <th class="text-end" width="15%">Total</th>
                            <th class="text-end" width="15%">Belum Lunas</th>
                            <th class="text-center" width="13%">Status</th>
                            <th class="text-center" width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $i => $sale)
                            <tr>
                                <td class="text-center">{{ $sales->firstItem() + $i }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $sale->customer->name ?? '-' }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $sale->branch->name }}</span>
                                </td>
                                <td class="text-end">
                                    <strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong>
                                </td>
                                <td class="text-end">
                                    @if($sale->status == 'blm lunas')
                                        <span class="text-danger fw-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">Rp 0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($sale->status == 'lunas')
                                        <span class="badge bg-success px-3 py-2">
                                            <i class="fas fa-check-circle"></i> LUNAS
                                        </span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2">
                                            <i class="fas fa-times-circle"></i> BELUM LUNAS
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($sale->status == 'blm lunas')
                                        <!-- Tombol Detail -->
                                        <button type="button" class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#detailModal{{ $sale->id }}">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                        
                                        <!-- Tombol Lunasi -->
                                        <form method="POST" action="{{ route('owner.sales.pelunasan', $sale->id) }}" 
                                              onsubmit="return confirm('Yakin ingin melunasi penjualan ini?')" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Lunasi
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#detailModal{{ $sale->id }}">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            <i class="fas fa-lock"></i> Sudah Lunas
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada data penjualan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Menampilkan {{ $sales->firstItem() ?? 0 }} - {{ $sales->lastItem() ?? 0 }} dari {{ $sales->total() }} data
                </div>
                <div>
                    {{ $sales->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail untuk setiap penjualan -->
@foreach($sales as $sale)
<div class="modal fade" id="detailModal{{ $sale->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $sale->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="detailModalLabel{{ $sale->id }}">
                    <i class="fas fa-receipt"></i> Detail Penjualan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Info Penjualan -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</p>
                                <p class="mb-2"><strong>Customer:</strong> {{ $sale->customer->name ?? '-' }}</p>
                                <p class="mb-2"><strong>Cabang:</strong> <span class="badge bg-info">{{ $sale->branch->name }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Total:</strong> <span class="text-primary fw-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</span></p>
                                <p class="mb-2"><strong>Status:</strong> 
                                    @if($sale->status == 'lunas')
                                        <span class="badge bg-success">LUNAS</span>
                                    @else
                                        <span class="badge bg-danger">BELUM LUNAS</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Produk/HP -->
                <h6 class="mb-3"><i class="fas fa-mobile-alt"></i> Daftar Produk (HP)</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="35%">Nama Produk</th>
                                <th width="30%">IMEI</th>
                                <th width="30%" class="text-end">Harga Jual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sale->saleItems as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $item->product->name ?? '-' }}</td>
                                    <td>
                                        <code>{{ $item->imei ?? '-' }}</code>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada produk HP</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($sale->saleItems->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal Produk:</strong></td>
                                <td class="text-end">
                                    <strong class="text-success">Rp {{ number_format($sale->saleItems->sum('price'), 0, ',', '.') }}</strong>
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Tabel Aksesoris -->
                @if($sale->saleAccessories->count() > 0)
                <h6 class="mb-3 mt-4"><i class="fas fa-headphones"></i> Daftar Aksesoris</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="40%">Nama Aksesoris</th>
                                <th width="15%" class="text-center">Qty</th>
                                <th width="20%" class="text-end">Harga Satuan</th>
                                <th width="20%" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleAccessories as $index => $acc)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $acc->accessory->name ?? '-' }}</td>
                                    <td class="text-center">{{ $acc->qty }}</td>
                                    <td class="text-end">
                                        Rp {{ number_format($acc->price ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">Rp {{ number_format(($acc->price * $acc->qty) ?? 0, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal Aksesoris:</strong></td>
                                <td class="text-end">
                                    <strong class="text-success">
                                        Rp {{ number_format($sale->saleAccessories->sum(function($acc) { return $acc->price * $acc->qty; }), 0, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif

                <!-- Total Keseluruhan -->
                <div class="card bg-light mt-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 text-end">
                                <h5 class="mb-0"><strong>TOTAL KESELURUHAN:</strong></h5>
                            </div>
                            <div class="col-md-4 text-end">
                                <h5 class="mb-0 text-primary"><strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@if(session('success'))
<script>
    alert('{{ session('success') }}');
</script>
@endif
@endsection