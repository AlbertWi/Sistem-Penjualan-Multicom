@extends('layouts.app')

@section('title', 'Stok Cabang')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Stok Produk Cabang</h3>

        <div class="card-tools">
            <form method="GET" action="{{ route('stok-cabang') }}">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text"
                           name="q"
                           class="form-control"
                           placeholder="Cari produk..."
                           value="{{ request('q') }}">

                    @if(request('branch_id'))
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                    @endif

                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body">

        @php
            $userBranchId = auth()->user()->branch_id;

            if (!request()->has('branch_id')) {
                // default pertama kali → cabang user
                $activeBranchId = $userBranchId;
            } elseif (request('branch_id') === 'all') {
                // klik Semua Cabang
                $activeBranchId = null;
            } else {
                // klik cabang tertentu
                $activeBranchId = request('branch_id');
            }
        @endphp


        <!-- ===== TAB CABANG ===== -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ request('branch_id') === 'all' ? 'active' : '' }}"
                href="{{ route('stok-cabang', ['branch_id' => 'all']) }}">
                    Semua Cabang
                </a>
            </li>
            @foreach($branches as $b)
                <li class="nav-item">
                    <a class="nav-link {{ (string)$activeBranchId === (string)$b->id ? 'active' : '' }}"
                    href="{{ route('stok-cabang', ['branch_id' => $b->id]) }}">
                        {{ $b->name }}
                        <span class="badge bg-secondary ml-1 text-uppercase">
                            {{ $b->branch_type }}
                        </span>
                        @if($userBranchId == $b->id)
                            <span class="badge bg-primary ml-1">Cabang Saya</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        @php
            // ===== FILTER CABANG =====
            if ($activeBranchId) {
                $filteredBranches = $branches->filter(fn ($b) => $b->id == $activeBranchId);
            } else {
                $filteredBranches = $branches;
            }
        @endphp

        <!-- ===== DATA CABANG ===== -->
        @forelse($filteredBranches as $branch)
            @php
                $isOwnBranch = $branch->id == $userBranchId;

                $grouped = $branch->inventoryItems->groupBy('product_id');

                if ($query = request('q')) {
                    $grouped = $grouped->filter(function ($items) use ($query) {
                        return stripos($items->first()->product->name ?? '', $query) !== false;
                    });
                }
            @endphp

            <h5 class="mb-3 mt-4">
                <i class="fas fa-store"></i> {{ $branch->name }}
                <span class="badge bg-secondary ml-1 text-uppercase">
                    {{ $branch->branch_type }}
                </span>
                @if($isOwnBranch)
                    <span class="badge bg-success ml-2">Stok Cabang Anda</span>
                @endif
            </h5>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Harga Modal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grouped as $productId => $items)
                            @php
                                $product   = $items->first()->product;
                                $firstItem = $items->first();
                                $modal     = $firstItem->purchase_price ?? 0;
                            @endphp

                            <tr class="{{ $isOwnBranch ? 'table-info' : '' }}">
                                <td>{{ $product->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        {{ $items->count() }}
                                    </span>
                                </td>
                                <td>
                                    Rp {{ number_format($modal, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info"
                                            data-toggle="modal"
                                            data-target="#modal-imei-{{ $branch->id }}-{{ $productId }}">
                                        Detail
                                    </button>
                                </td>
                            </tr>

                            <!-- ===== MODAL IMEI ===== -->
                            <div class="modal fade"
                                 id="modal-imei-{{ $branch->id }}-{{ $productId }}"
                                 tabindex="-1">
                                <div class="modal-dialog modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Daftar IMEI - {{ $product->name }} ({{ $branch->name }})
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <ul class="list-group">
                                                @foreach($items as $item)
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        {{ $item->imei }}
                                                        <span class="badge badge-secondary">
                                                            {{ $item->status }}
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button"
                                                    class="btn btn-secondary"
                                                    data-dismiss="modal">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Tidak ada stok produk
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <p class="text-muted">Cabang tidak ditemukan.</p>
        @endforelse

    </div>
</div>
@endsection