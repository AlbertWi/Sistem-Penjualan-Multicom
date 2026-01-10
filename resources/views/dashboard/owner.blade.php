{{-- resources/views/dashboard/owner.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Owner')

@section('content')
<div class="row">
    <div class="col-lg-4 col-12">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $totalStock ?? 0 }}</h3>
                <p>Total Stok Seluruh Cabang</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
            <a href="#" class="small-box-footer disabled" onclick="return false;">Data Gabungan <i class="fas fa-info-circle"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalBranches ?? 0 }}</h3>
                <p>Jumlah Cabang</p>
            </div>
            <div class="icon">
                <i class="fas fa-store-alt"></i>
            </div>
            <a href="{{ route('owner.branches.index') }}" class="small-box-footer">Lihat Cabang <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalAdmins ?? 0 }}</h3>
                <p>Total Admin & Kepala Toko</p>
            </div>
            <div class="icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <a href="{{ route('owner.users.index') }}" class="small-box-footer">Kelola User <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">📉 Stok Cabang yang Menipis</h5>
    </div>
    <div class="card-body">
        @if($lowStocks->count())
            @foreach($lowStocks as $branchData)
                <h6><i class="fas fa-store"></i> {{ $branchData['branch'] }}</h6>
                <table class="table table-bordered table-sm mb-4">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branchData['low_stocks'] as $item)
                            <tr>
                                <td>{{ $item['product'] }}</td>
                                <td><span class="badge bg-danger">{{ $item['qty'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @else
            <p class="text-muted">Tidak ada stok yang menipis.</p>
        @endif
    </div>
        <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title">📊 Grafik Penjualan per Cabang</h5>
        </div>
        <div class="card-body">

            {{-- FILTER --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="filter" class="form-control" onchange="this.form.submit()">
                        <option value="hari"  {{ $filter=='hari' ? 'selected' : '' }}>Per Hari</option>
                        <option value="bulan" {{ $filter=='bulan' ? 'selected' : '' }}>Per Bulan</option>
                        <option value="tahun" {{ $filter=='tahun' ? 'selected' : '' }}>Per Tahun</option>
                    </select>
                </div>

                @if($filter === 'hari')
                    <div class="col-md-3">
                        <input type="date" name="date" value="{{ $date }}" class="form-control">
                    </div>
                @endif

                @if($filter === 'bulan')
                    <div class="col-md-2">
                        <select name="month" class="form-control">
                            @for($m=1;$m<=12;$m++)
                                <option value="{{ $m }}" {{ $month==$m?'selected':'' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                @endif

                @if($filter !== 'hari')
                    <div class="col-md-2">
                        <input type="number" name="year" class="form-control"
                            value="{{ $year }}" min="2020" max="{{ now()->year }}">
                    </div>
                @endif

                <div class="col-md-2">
                    <button class="btn btn-primary w-100">Terapkan</button>
                </div>
            </form>

            <canvas id="salesChart" height="100"></canvas>
        </div>
    </div>

</div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('salesChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($salesChart->pluck('branch')),
        datasets: [{
            label: 'Total Penjualan (Rp)',
            data: @json($salesChart->pluck('total')),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: val => 'Rp ' + val.toLocaleString('id-ID')
                }
            }
        }
    }
});
</script>
@endpush

@endsection
