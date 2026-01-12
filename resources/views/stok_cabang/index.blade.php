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

                    @if(request('branch_id') !== null)
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
            // $userBranchId sudah dikirim dari controller
            // $selectedBranchId adalah nilai dari request
            
            // LOGIKA FIXED: 
            // Jika $selectedBranchId === null (tidak ada parameter), maka "Semua Cabang"
            // Jika $selectedBranchId === '' (string kosong), maka "Semua Cabang"  
            // Jika $selectedBranchId adalah angka, maka cabang tertentu
            
            $activeBranchId = $selectedBranchId;
            
            // Untuk mengecek apakah saat ini "Semua Cabang"
            $isAllBranches = ($selectedBranchId === null || $selectedBranchId === '');
            
            // URL untuk "Semua Cabang" harus menghapus parameter branch_id
            $allBranchesUrl = route('stok-cabang', array_merge(request()->except(['branch_id', 'page'])));
        @endphp

        <!-- ===== TAB CABANG ===== -->
        <ul class="nav nav-tabs mb-4">
            <!-- Tab Semua Cabang -->
            <li class="nav-item">
                <a class="nav-link {{ $isAllBranches ? 'active' : '' }}"
                   href="{{ $allBranchesUrl }}">
                    Semua Cabang
                </a>
            </li>

            <!-- Tab per Cabang -->
            @foreach($branches as $b)
                <li class="nav-item">
                    <a class="nav-link {{ $activeBranchId == $b->id ? 'active' : '' }}"
                       href="{{ route('stok-cabang', array_merge(request()->except('page'), ['branch_id' => $b->id])) }}">
                        {{ $b->name }}
                        @if($b->isOnline())
                            (Online)
                        @endif

                        @if($userBranchId == $b->id)
                            <span class="badge bg-primary ml-1">Cabang Saya</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        @php
            // ===== FILTER CABANG UNTUK DITAMPILKAN =====
            if (!$isAllBranches && $activeBranchId) {
                // Tampilkan cabang tertentu
                $filteredBranches = $branches->filter(fn ($b) => $b->id == $activeBranchId);
            } else {
                // Tampilkan semua cabang (untuk tab "Semua Cabang")
                $filteredBranches = $branches;
            }
        @endphp

        <!-- ===== INFORMASI FILTER ===== -->
        @if(!$isAllBranches && $activeBranchId)
            @php
                $activeBranch = $branches->firstWhere('id', $activeBranchId);
            @endphp
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle"></i>
                Menampilkan stok untuk: 
                <strong>
                    {{ $activeBranch->name }}
                    @if($activeBranch->isOnline())
                        (Online)
                    @endif
                    @if($userBranchId == $activeBranchId)
                        <span class="badge bg-primary">Cabang Anda</span>
                    @endif
                </strong>
            </div>
        @else
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle"></i>
                Menampilkan stok untuk <strong>Semua Cabang</strong>
                @if($userBranchId)
                    @php
                        $userBranch = $branches->firstWhere('id', $userBranchId);
                    @endphp
                    <br>
                    <small class="text-muted">
                        Cabang Anda: {{ $userBranch->name }}
                        @if($userBranch->isOnline())
                            (Online)
                        @endif
                    </small>
                @endif
            </div>
        @endif

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

            <div class="branch-section mb-4 p-3 border rounded">
                <h5 class="mb-3">
                    <i class="fas fa-store"></i> {{ $branch->name }}
                    @if($branch->isOnline())
                        <span class="badge bg-success ml-2">Online</span>
                    @else
                        <span class="badge bg-secondary ml-2">Offline</span>
                    @endif

                    @if($isOwnBranch)
                        <span class="badge bg-primary ml-2">Cabang Anda</span>
                    @endif
                </h5>

                @if($grouped->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="40%">Produk</th>
                                    <th width="15%">Qty</th>
                                    <th width="25%">Harga Modal</th>
                                    <th width="20%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grouped as $productId => $items)
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
                                                Detail IMEI
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
                                                        Daftar IMEI - {{ $product->name }} 
                                                        ({{ $branch->name }}
                                                        @if($branch->isOnline())
                                                            - Online
                                                        @endif
                                                        )
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">
                                                    <ul class="list-group">
                                                        @foreach($items as $item)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <span class="font-monospace">{{ $item->imei }}</span>
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Tidak ada stok produk
                        @if($query)
                            untuk pencarian "{{ $query }}"
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Tidak ada cabang yang ditemukan.
            </div>
        @endforelse

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle form pencarian agar tidak mengirim branch_id jika pilih "Semua Cabang"
    const searchForm = document.querySelector('form[method="GET"]');
    if (searchForm) {
        const allBranchesTab = document.querySelector('a.nav-link[href*="branch_id="]');
        if (!allBranchesTab || window.location.href.indexOf('branch_id') === -1) {
            // Jika saat ini di tab "Semua Cabang", pastikan input hidden branch_id dihapus
            const branchIdInput = searchForm.querySelector('input[name="branch_id"]');
            if (branchIdInput) {
                branchIdInput.remove();
            }
        }
    }
});
</script>
@endsection