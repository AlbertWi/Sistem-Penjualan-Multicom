@extends('layouts.app')

@section('title', 'Rekap Penjualan Per Cabang')

@section('content')
<div class="container-fluid">
    <!-- HEADER -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h4 class="fw-bold text-primary mb-1">
                <i class="fas fa-chart-bar me-2"></i>Rekap Penjualan Per Cabang
            </h4>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1"></i>
                Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y') }} - 
                {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') }}
            </p>
        </div>
        <div class="col-auto">
            @if(!empty($rekapCabang))
            <form method="GET" action="{{ route('owner.sales.export-pdf') }}" class="d-inline">
                <input type="hidden" name="tanggal_awal" value="{{ $tanggalAwal }}">
                <input type="hidden" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                <input type="hidden" name="branch_id" value="{{ $branchId ?? '' }}">
                <button type="submit" class="btn btn-danger shadow-sm">
                    <i class="fas fa-file-pdf me-2"></i> Export PDF
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- FILTER SECTION -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold"><i class="fas fa-filter me-2"></i>Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3" id="filterForm">
                <!-- Hidden fields untuk mempertahankan state -->
                <input type="hidden" name="preserve_state" value="1">
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Awal</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" name="tanggal_awal" class="form-control" value="{{ $tanggalAwal }}" required>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Akhir</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" name="tanggal_akhir" class="form-control" value="{{ $tanggalAkhir }}" required>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cabang</label>
                    <select name="branch_id" class="form-select">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Merek</label>
                    <select name="brand_id" class="form-select">
                        <option value="">Semua Merek</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-12">
                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-search me-2"></i> Terapkan Filter
                        </button>
                        <button type="button" onclick="resetFilter()" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-2"></i> Reset
                        </button>
                        @if(request()->hasAny(['tanggal_awal', 'tanggal_akhir', 'branch_id', 'brand_id']))
                        <span class="ms-auto badge bg-light text-dark border align-self-center">
                            <i class="fas fa-info-circle me-1"></i>
                            Filter aktif: 
                            @if(request('branch_id'))
                                Cabang {{ $branches->find(request('branch_id'))->name ?? '' }}
                            @else
                                Semua Cabang
                            @endif
                        </span>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(!empty($rekapCabang))
        <!-- SUMMARY CARDS -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card summary-card bg-primary bg-opacity-10 border-primary border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-primary text-white me-3">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Pendapatan</h6>
                                <h3 class="fw-bold mb-1">Rp{{ number_format($totalSemua['pendapatan'], 0, ',', '.') }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-receipt me-1"></i>
                                    {{ $totalSemua['jumlah_nota'] }} transaksi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card summary-card bg-success bg-opacity-10 border-success border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-success text-white me-3">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Laba</h6>
                                <h3 class="fw-bold mb-1">Rp{{ number_format($totalSemua['laba'], 0, ',', '.') }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-percentage me-1"></i>
                                    @php
                                        $totalMargin = $totalSemua['pendapatan'] > 0 ? ($totalSemua['laba'] / $totalSemua['pendapatan']) * 100 : 0;
                                    @endphp
                                    {{ number_format($totalMargin, 1) }}% margin
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 mb-3">
                <div class="card summary-card bg-info bg-opacity-10 border-info border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-info text-white me-3">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Nota</h6>
                                <h3 class="fw-bold mb-1">{{ $totalSemua['jumlah_nota'] }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-calculator me-1"></i>
                                    @php
                                        $avgTransaction = $totalSemua['jumlah_nota'] > 0 ? $totalSemua['pendapatan'] / $totalSemua['jumlah_nota'] : 0;
                                    @endphp
                                    Avg: Rp{{ number_format($avgTransaction, 0, ',', '.') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 mb-3">
                <div class="card summary-card bg-warning bg-opacity-10 border-warning border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-warning text-white me-3">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total Item</h6>
                                <h3 class="fw-bold mb-1">{{ $totalSemua['jumlah_item'] }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-chart-pie me-1"></i>
                                    {{ $totalSemua['jumlah_nota'] > 0 ? number_format($totalSemua['jumlah_item'] / $totalSemua['jumlah_nota'], 1) : 0 }} item/transaksi
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 mb-3">
                <div class="card summary-card bg-secondary bg-opacity-10 border-secondary border-start border-4 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle bg-secondary text-white me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Customer</h6>
                                <h3 class="fw-bold mb-1">{{ $totalSemua['jumlah_customer'] }}</h3>
                                <small class="text-muted">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    {{ $totalSemua['jumlah_nota'] > 0 ? number_format($totalSemua['jumlah_customer'] / $totalSemua['jumlah_nota'] * 100, 0) : 0 }}% repeat rate
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHARTS SECTION -->
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Pendapatan Per Cabang
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container" style="height: 280px; position: relative;">
                            <canvas id="pendapatanChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-coins text-success me-2"></i>
                            Laba Per Cabang
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="chart-container" style="height: 280px; position: relative;">
                            <canvas id="labaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REKAP TABLE -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom py-3">
                <div>
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-table me-2"></i>
                        Detail Rekap Per Cabang
                    </h5>
                    <small class="text-muted">
                        Urutkan berdasarkan pendapatan tertinggi
                    </small>
                </div>
                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <i class="fas fa-store me-1"></i> {{ count($rekapCabang) }} Cabang
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">Rank</th>
                                <th class="ps-4" style="min-width: 150px;">Cabang</th>
                                <th class="text-end" style="min-width: 130px;">Pendapatan</th>
                                <th class="text-end" style="min-width: 120px;">Laba</th>
                                <th class="text-center" style="width: 100px;">Margin</th>
                                <th class="text-center" style="width: 80px;">Nota</th>
                                <th class="text-center" style="width: 80px;">Item</th>
                                <th class="text-center" style="width: 90px;">Customer</th>
                                <th class="text-end" style="min-width: 120px;">Avg/Transaksi</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekapCabang as $index => $cabang)
                            <tr class="{{ $index == 0 ? 'top-performer' : '' }}">
                                <td class="text-center">
                                    @if($index < 3)
                                        <span class="rank-badge rank-{{ $index + 1 }}">
                                            <i class="fas fa-medal"></i> {{ $index + 1 }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </td>
                                <td class="ps-4 fw-semibold">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-store text-muted me-2"></i>
                                        {{ $cabang['branch_name'] }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-primary">
                                        Rp{{ number_format($cabang['pendapatan'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold {{ $cabang['laba'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        <i class="fas fa-{{ $cabang['laba'] >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                        Rp{{ number_format($cabang['laba'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <div class="progress me-2" style="height: 6px; width: 50px;">
                                            <div class="progress-bar bg-{{ $cabang['margin_laba'] >= 20 ? 'success' : ($cabang['margin_laba'] >= 10 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min(100, $cabang['margin_laba']) }}%">
                                            </div>
                                        </div>
                                        <span class="fw-semibold {{ $cabang['margin_laba'] >= 20 ? 'text-success' : ($cabang['margin_laba'] >= 10 ? 'text-warning' : 'text-danger') }}">
                                            {{ number_format($cabang['margin_laba'], 1) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill">
                                        {{ $cabang['jumlah_nota'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill">
                                        {{ $cabang['jumlah_item'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill">
                                        {{ $cabang['jumlah_customer'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold text-muted">
                                        Rp{{ number_format($cabang['avg_transaksi'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('owner.reports.sales.detail', ['branch_id' => $cabang['branch_id'], 'tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir]) }}" 
                                       class="btn btn-sm btn-outline-primary px-3" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2" class="ps-4">TOTAL SEMUA CABANG</th>
                                <th class="text-end fw-bold text-primary">
                                    Rp{{ number_format($totalSemua['pendapatan'], 0, ',', '.') }}
                                </th>
                                <th class="text-end fw-bold {{ $totalSemua['laba'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp{{ number_format($totalSemua['laba'], 0, ',', '.') }}
                                </th>
                                <th class="text-center fw-bold">
                                    {{ number_format($totalMargin, 1) }}%
                                </th>
                                <th class="text-center fw-bold">{{ $totalSemua['jumlah_nota'] }}</th>
                                <th class="text-center fw-bold">{{ $totalSemua['jumlah_item'] }}</th>
                                <th class="text-center fw-bold">{{ $totalSemua['jumlah_customer'] }}</th>
                                <th class="text-end fw-bold">
                                    Rp{{ number_format($avgTransaction, 0, ',', '.') }}
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- PERFORMANCE SUMMARY -->
        @if(count($rekapCabang) > 1)
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold text-success">
                            <i class="fas fa-trophy me-2"></i>Performance Terbaik
                        </h5>
                    </div>
                    <div class="card-body">
                        @php $topCabang = $rekapCabang[0]; @endphp
                        <div class="text-center mb-4">
                            <div class="performance-medal mb-3">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h4 class="fw-bold mb-1">{{ $topCabang['branch_name'] }}</h4>
                            <p class="text-muted">Cabang dengan pendapatan tertinggi</p>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="text-muted mb-2">Pendapatan</h6>
                                    <h5 class="fw-bold text-primary mb-0">
                                        Rp{{ number_format($topCabang['pendapatan'], 0, ',', '.') }}
                                    </h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-2">Laba</h6>
                                <h5 class="fw-bold text-success mb-0">
                                    Rp{{ number_format($topCabang['laba'], 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-semibold text-info">
                            <i class="fas fa-chart-line me-2"></i>Analisis Performa
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3">Distribusi Pendapatan</h6>
                            @php
                                $totalRevenue = $totalSemua['pendapatan'];
                                $top3Revenue = 0;
                                $otherRevenue = 0;
                                $topCabangs = array_slice($rekapCabang, 0, 3);
                                
                                foreach($topCabangs as $cabang) {
                                    $top3Revenue += $cabang['pendapatan'];
                                }
                                $otherRevenue = $totalRevenue - $top3Revenue;
                            @endphp
                            
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted me-2" style="width: 150px;">Top 3 Cabang</span>
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div class="progress-bar bg-success" 
                                         style="width: {{ $totalRevenue > 0 ? ($top3Revenue / $totalRevenue) * 100 : 0 }}%">
                                    </div>
                                </div>
                                <span class="ms-2 fw-semibold">
                                    {{ $totalRevenue > 0 ? number_format(($top3Revenue / $totalRevenue) * 100, 1) : 0 }}%
                                </span>
                            </div>
                            
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted me-2" style="width: 150px;">Lainnya</span>
                                <div class="progress flex-grow-1" style="height: 20px;">
                                    <div class="progress-bar bg-info" 
                                         style="width: {{ $totalRevenue > 0 ? ($otherRevenue / $totalRevenue) * 100 : 0 }}%">
                                    </div>
                                </div>
                                <span class="ms-2 fw-semibold">
                                    {{ $totalRevenue > 0 ? number_format(($otherRevenue / $totalRevenue) * 100, 1) : 0 }}%
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <h6 class="fw-semibold mb-3">Rata-rata per Cabang</h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border p-2 rounded">
                                        <small class="text-muted d-block">Avg Pendapatan</small>
                                        <span class="fw-bold">
                                            Rp{{ number_format($totalRevenue / count($rekapCabang), 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border p-2 rounded">
                                        <small class="text-muted d-block">Avg Laba</small>
                                        <span class="fw-bold">
                                            Rp{{ number_format($totalSemua['laba'] / count($rekapCabang), 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border p-2 rounded">
                                        <small class="text-muted d-block">Avg Nota</small>
                                        <span class="fw-bold">
                                            {{ number_format($totalSemua['jumlah_nota'] / count($rekapCabang), 1) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @else
        <!-- EMPTY STATE -->
        <div class="text-center py-5 my-5">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-chart-bar fa-5x text-light" style="opacity: 0.3;"></i>
            </div>
            <h4 class="text-muted mb-3">Tidak ada data penjualan</h4>
            <p class="text-muted mb-4">Tidak ada data penjualan untuk periode yang dipilih.</p>
            <button onclick="resetFilter()" class="btn btn-primary">
                <i class="fas fa-redo me-2"></i> Reset Filter
            </button>
        </div>
    @endif
</div>

@if(!empty($rekapCabang))
<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Helper function for currency formatting
    const formatCurrency = (value) => {
        return 'Rp' + Math.abs(value).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    };

    // Pendapatan Chart
    const pendapatanCtx = document.getElementById('pendapatanChart').getContext('2d');
    new Chart(pendapatanCtx, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Pendapatan',
                data: @json($chartData['pendapatan']),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            return `Pendapatan: ${formatCurrency(context.raw)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        },
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

    // Laba Chart
    const labaCtx = document.getElementById('labaChart').getContext('2d');
    new Chart(labaCtx, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [{
                label: 'Laba',
                data: @json($chartData['laba']),
                backgroundColor: function(context) {
                    const value = context.dataset.data[context.dataIndex];
                    return value >= 0 
                        ? 'rgba(75, 192, 192, 0.8)' 
                        : 'rgba(255, 99, 132, 0.8)';
                },
                borderColor: function(context) {
                    const value = context.dataset.data[context.dataIndex];
                    return value >= 0 
                        ? 'rgba(75, 192, 192, 1)' 
                        : 'rgba(255, 99, 132, 1)';
                },
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            return `Laba: ${formatCurrency(context.raw)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        },
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

    // Reset filter function
    function resetFilter() {
        const url = new URL(window.location.href);
        url.search = '';
        window.location.href = url.toString();
    }

    // Preserve form state on page reload
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const inputs = form.querySelectorAll('input, select');
        
        // Load saved values from localStorage
        inputs.forEach(input => {
            const savedValue = localStorage.getItem(`filter_${input.name}`);
            if (savedValue !== null) {
                input.value = savedValue;
            }
        });
        
        // Save values on change
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                localStorage.setItem(`filter_${this.name}`, this.value);
            });
        });
        
        // Clear saved values on reset
        form.addEventListener('reset', function() {
            inputs.forEach(input => {
                localStorage.removeItem(`filter_${input.name}`);
            });
        });
    });
</script>
@endif

<style>
    /* Custom CSS untuk tampilan yang lebih baik */
    .container-fluid {
        padding: 20px;
    }
    
    /* Summary Cards */
    .summary-card {
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
        border-left-width: 4px !important;
    }
    
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
    }
    
    .summary-card .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    /* Rank Badges */
    .rank-badge {
        display: inline-block;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }
    
    .rank-1 {
        background: linear-gradient(135deg, #FFD700, #FFA500);
        color: white;
    }
    
    .rank-2 {
        background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
        color: white;
    }
    
    .rank-3 {
        background: linear-gradient(135deg, #CD7F32, #A0522D);
        color: white;
    }
    
    /* Table styling */
    .top-performer {
        background: linear-gradient(to right, rgba(52, 199, 89, 0.05), transparent);
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        color: #6c757d;
        white-space: nowrap;
        padding: 12px 8px;
    }
    
    .table td {
        padding: 12px 8px;
        vertical-align: middle;
    }
    
    .table tbody tr {
        border-bottom: 1px solid #f1f1f1;
        transition: background-color 0.2s;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0,0,0,0.015);
    }
    
    /* Performance Medal */
    .performance-medal {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #FFD700, #FFA500);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 24px;
        color: white;
    }
    
    /* Chart styling */
    .chart-container canvas {
        max-width: 100%;
    }
    
    /* Filter active badge */
    .badge.bg-light {
        border: 1px solid #dee2e6;
    }
    
    /* Progress bars in table */
    .progress {
        border-radius: 10px;
        background-color: #f1f1f1;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    /* Empty state */
    .empty-state-icon {
        opacity: 0.1;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .summary-card .icon-circle {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .summary-card h3 {
            font-size: 1.2rem;
        }
        
        .table-responsive {
            font-size: 0.9rem;
        }
    }
</style>
@endsection