@extends('layouts.app')

@section('title', 'Assign IMEI - ' . $order->order_number)

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Assign IMEI</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manajer_operasional.orders.index') }}">Orders</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manajer_operasional.orders.show', $order->id) }}">{{ $order->order_number }}</a></li>
                        <li class="breadcrumb-item active">Assign IMEI</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <!-- Product Info Card -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Product Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Product</th>
                                            <td><strong>{{ $orderItem->product->name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Brand</th>
                                            <td>{{ $orderItem->product->brand->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Quantity</th>
                                            <td>{{ $orderItem->quantity }} unit</td>
                                        </tr>
                                        <tr>
                                            <th>Price</th>
                                            <td>Rp {{ number_format($orderItem->price, 0, ',', '.') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Order Number</th>
                                            <td>{{ $order->order_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>Customer</th>
                                            <td>{{ $order->customer->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Order Status</th>
                                            <td>
                                                <span class="badge badge-warning">{{ ucfirst($order->status) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Payment Status</th>
                                            <td>
                                                <span class="badge badge-success">{{ ucfirst($order->payment_status) }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assign IMEI Form -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Select Available IMEI</h3>
                        </div>
                        <form action="{{ route('manajer_operasional.orders.store-imei', [$order, $orderItem]) }}" 
                              method="POST">
                            @csrf
                            <div class="card-body">
                                @if($availableInventory->isEmpty())
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        Tidak ada IMEI tersedia untuk produk ini. Semua stok sudah di-assign atau tidak aktif.
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label for="inventory_item_id">Select IMEI <span class="text-danger">*</span></label>
                                        <select name="inventory_item_id" 
                                                id="inventory_item_id" 
                                                class="form-control @error('inventory_item_id') is-invalid @enderror"
                                                required>
                                            <option value="">-- Pilih IMEI --</option>
                                            @foreach($inventoryByBranch as $branchId => $data)
                                                <optgroup label="📍 {{ $data['branch']->name }} ({{ $data['items']->count() }} available)">
                                                    @foreach($data['items'] as $item)
                                                        <option value="{{ $item->id }}" 
                                                                data-imei="{{ $item->imei }}"
                                                                data-branch="{{ $data['branch']->name }}"
                                                                data-location="{{ $item->storage_location ?? '-' }}">
                                                            IMEI: {{ $item->imei }}
                                                            @if($item->storage_location)
                                                                | Lokasi: {{ $item->storage_location }}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        @error('inventory_item_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Total {{ $availableInventory->count() }} IMEI tersedia untuk produk ini
                                        </small>
                                    </div>

                                    <!-- Selected IMEI Details (will show on selection) -->
                                    <div id="imei-details" class="alert alert-info" style="display:none;">
                                        <h5>Selected IMEI Details:</h5>
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <th width="30%">IMEI:</th>
                                                <td><code id="detail-imei"></code></td>
                                            </tr>
                                            <tr>
                                                <th>Branch:</th>
                                                <td id="detail-branch"></td>
                                            </tr>
                                            <tr>
                                                <th>Storage Location:</th>
                                                <td id="detail-location"></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="form-group">
                                        <label for="notes">Notes (Optional)</label>
                                        <textarea name="notes" 
                                                  id="notes" 
                                                  class="form-control" 
                                                  rows="3"
                                                  placeholder="Add notes about this assignment..."></textarea>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer">
                                @if($availableInventory->isNotEmpty())
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check mr-2"></i> Assign IMEI
                                    </button>
                                @endif
                                <a href="{{ route('manajer_operasional.orders.show', $order->id) }}" 
                                   class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar: Available Stock Summary -->
                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Stock Summary by Branch</h3>
                        </div>
                        <div class="card-body">
                            @foreach($inventoryByBranch as $branchId => $data)
                                <div class="mb-3">
                                    <h5 class="mb-2">
                                        <i class="fas fa-store mr-2"></i>
                                        {{ $data['branch']->name }}
                                    </h5>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: 100%">
                                            {{ $data['items']->count() }} units available
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $data['branch']->address ?? 'No address' }}
                                    </small>
                                </div>
                                @if(!$loop->last)
                                    <hr>
                                @endif
                            @endforeach
                            
                            @if($inventoryByBranch->isEmpty())
                                <p class="text-center text-muted mb-0">
                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                    No stock available
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Tips -->
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb mr-2"></i> Tips
                            </h3>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0 pl-3">
                                <li>Pilih IMEI dari cabang terdekat untuk efisiensi pengiriman</li>
                                <li>Pastikan IMEI sesuai dengan produk yang dipesan</li>
                                <li>Cek kondisi fisik barang sebelum assign</li>
                                <li>Stock yang sudah di-assign akan otomatis direserve</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Show IMEI details on selection
    $('#inventory_item_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        
        if (selectedOption.val()) {
            $('#detail-imei').text(selectedOption.data('imei'));
            $('#detail-branch').text(selectedOption.data('branch'));
            $('#detail-location').text(selectedOption.data('location') || '-');
            $('#imei-details').slideDown();
        } else {
            $('#imei-details').slideUp();
        }
    });
    
    // Confirm before submit
    $('form').on('submit', function(e) {
        const selectedImei = $('#inventory_item_id option:selected').data('imei');
        if (!selectedImei) {
            e.preventDefault();
            alert('Please select an IMEI first!');
            return false;
        }
        
        return confirm(`Confirm assign IMEI: ${selectedImei} to this order?`);
    });
});
</script>
@endpush
@endsection