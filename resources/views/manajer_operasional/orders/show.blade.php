@extends('layouts.app')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Order Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('manajer_operasional.orders.index') }}">Online Orders</a></li>
                        <li class="breadcrumb-item active">{{ $order->order_number }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Order Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Order #{{ $order->order_number }}</h3>
                            <div class="card-tools">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $paymentColors = [
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger'
                                    ];
                                @endphp
                                <span class="badge badge-{{ $statusColors[$order->status] }} mr-2">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <span class="badge badge-{{ $paymentColors[$order->payment_status] }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Customer Information</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="30%">Name</th>
                                            <td>{{ $order->customer->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $order->customer->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            <td>{{ $order->customer->phone }}</td>
                                        </tr>
                                        <tr>
                                            <th>Address</th>
                                            <td>{{ $order->customer->address ?? 'Not provided' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Order Information</h5>
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">Order Date</th>
                                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount</th>
                                            <td class="font-weight-bold text-primary">
                                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Payment Method</th>
                                            <td>{{ $order->payment_method ? ucfirst($order->payment_method) : 'Not specified' }}</td>
                                        </tr>
                                        @if($order->paid_at)
                                        <tr>
                                            <th>Paid At</th>
                                            <td>{{ $order->paid_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @endif
                                        @if($order->stock_picked_at)
                                        <tr>
                                            <th>Stock Picked At</th>
                                            <td>{{ $order->stock_picked_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Picked By</th>
                                            <td>{{ $order->stock_picked_by ? \App\Models\User::find($order->stock_picked_by)->name ?? 'Unknown' : 'Unknown' }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            
                            @if($order->notes)
                            <div class="mt-3">
                                <h5>Order Notes</h5>
                                <div class="alert alert-info">
                                    {{ $order->notes }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Allocation by Branch -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Stock Allocation by Branch</h3>
                        </div>
                        <div class="card-body">
                            @foreach($itemsByBranch as $branchId => $items)
                                @php
                                        $branch = $items->first()?->inventoryItem?->branch 
                                                ?? $items->first()?->branch;
                                    $statusCounts = $items->groupBy(function($item) {
                                        return $item->inventoryItem->status ?? 'unknown';
                                    });
                                @endphp
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h4 class="mb-0">
                                            <i class="fas fa-store mr-2"></i>
                                            {{ $branch?->name ?? 'Cabang tidak diketahui' }}
                                            <span class="badge badge-primary ml-2">{{ $items->count() }} items</span>
                                        </h4>
                                    </div>
                                    <div class="card-body">
                                        <!-- Branch status summary -->
                                        <div class="row mb-3">
                                            <div class="col-12">
                                                @foreach($statusCounts as $status => $statusItems)
                                                    @php
                                                        $statusColors = [
                                                            'in_stock' => 'success',
                                                            'reserved' => 'warning',
                                                            'sold' => 'primary',
                                                            'damaged' => 'danger',
                                                            'unknown' => 'secondary'
                                                        ];
                                                    @endphp
                                                    <span class="badge badge-{{ $statusColors[$status] ?? 'secondary' }} mr-2">
                                                        {{ ucfirst(str_replace('_', ' ', $status)) }}: {{ $statusItems->count() }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        <!-- Items table -->
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>IMEI/Serial</th>
                                                        <th>Qty</th>
                                                        <th>Price</th>
                                                        <th>Subtotal</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($items as $item)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $item->product->name }}</strong><br>
                                                            <small class="text-muted">{{ $item->product->brand->name ?? '-' }}</small>
                                                        </td>
                                                        <td>
                                                            @if($item->inventoryItem)
                                                                <code>{{ $item->inventoryItem->imei ?? 'N/A' }}</code>
                                                                @if($item->inventoryItem->sku)
                                                                    <br><small class="text-muted">SKU: {{ $item->inventoryItem->sku }}</small>
                                                                @endif
                                                            @else
                                                                <span class="text-danger">No inventory assigned</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ $item->quantity }}</td>
                                                        <td class="text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                        <td class="text-right font-weight-bold">
                                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                        </td>
                                                        <td>
                                                            @php
                                                                $status = $item->inventoryItem->status ?? 'unknown';
                                                            @endphp
                                                            <span class="badge badge-{{ $statusColors[$status] ?? 'secondary' }}">
                                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Branch actions -->
                                        @if($order->status == 'pending')
                                            @php
                                                $branchItemsReserved = $items->filter(function($item) {
                                                    return $item->inventoryItem && $item->inventoryItem->status == 'reserved';
                                                })->count();
                                            @endphp
                                            
                                            @if($branchItemsReserved > 0)
                                                <div class="mt-3">
                                                    <form action="{{ route('manajer_operasional.orders.confirm-branch-pickup', [$order->id, $branchId]) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Confirm stock pickup from {{ $branch?->name ?? 'Cabang tidak diketahui' }}?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check mr-1"></i> Confirm Stock Pickup from {{ $branch?->name ?? 'Cabang tidak diketahui' }}
                                                        </button>
                                                        <small class="text-muted ml-2">({{ $branchItemsReserved }} items reserved)</small>
                                                    </form>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Order Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    @if($order->status == 'pending')
                                        <form action="{{ route('manajer_operasional.orders.confirm-stock-pickup', $order->id) }}" 
                                              method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label>Add Notes (Optional)</label>
                                                <textarea name="notes" class="form-control" rows="2" 
                                                        placeholder="Add notes about stock pickup..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-check-circle mr-2"></i> Confirm All Stock Pickup
                                            </button>
                                            <small class="text-muted">This will mark all reserved items as sold</small>
                                        </form>
                                    @elseif($order->status == 'processing')
                                        <form action="{{ route('manajer_operasional.orders.complete', $order->id) }}" 
                                            method="POST">
                                            @csrf
                                            <div class="form-group">
                                                <label>Completion Notes</label>
                                                <textarea name="notes" class="form-control" rows="2" 
                                                        placeholder="Add completion notes..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-check-circle mr-2"></i> Mark as Completed
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                
                                <div class="col-md-4">
                                    @if(in_array($order->status, ['pending', 'processing']))
                                        <form action="{{ route('manajer_operasional.orders.cancel', $order->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                            @csrf
                                            <div class="form-group">
                                                <label>Cancellation Reason</label>
                                                <textarea name="reason" class="form-control" rows="2" required
                                                          placeholder="Enter cancellation reason..."></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-danger btn-block">
                                                <i class="fas fa-times mr-2"></i> Cancel Order
                                            </button>
                                            <small class="text-muted">Stock will be returned to inventory</small>
                                        </form>
                                    @endif
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="d-flex flex-column gap-2">
                                        <a href="{{ route('manajer_operasional.orders.print', $order->id) }}" 
                                           class="btn btn-secondary btn-block" target="_blank">
                                            <i class="fas fa-print mr-2"></i> Print Invoice
                                        </a>
                                        
                                        <a href="mailto:{{ $order->customer->email }}" 
                                           class="btn btn-info btn-block">
                                            <i class="fas fa-envelope mr-2"></i> Email Customer
                                        </a>
                                        
                                        <a href="{{ route('manajer_operasional.orders.index') }}" 
                                           class="btn btn-outline-secondary btn-block">
                                            <i class="fas fa-arrow-left mr-2"></i> Back to Orders
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Reallocation Modal -->
<div class="modal fade" id="reallocationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reallocate Stock</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="reallocationForm">
                    <input type="hidden" name="order_item_id" id="reallocOrderItemId">
                    
                    <div class="form-group">
                        <label>Select Available Stock</label>
                        <select name="inventory_item_id" id="availableStockSelect" class="form-control" required>
                            <option value="">Select inventory item...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Reason for reallocation..."></textarea>
                    </div>
                    
                    <div id="inventoryDetails"></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showReallocationModal(orderItemId, productId) {
    $('#reallocOrderItemId').val(orderItemId);
    $('#availableStockSelect').empty().append('<option value="">Loading...</option>');
    $('#inventoryDetails').empty();
    
    // Fetch available stock for ALL branches
    $.get(`{{ route('manajer_operasional.orders.branch-stock', [$order->id, 'all']) }}`, function(response) {
        if (response.success && response.data) {
            $('#availableStockSelect').empty().append('<option value="">Select inventory item...</option>');
            
            // Filter untuk product yang dipilih
            if (response.data[productId]) {
                const productData = response.data[productId];
                
                // Loop melalui semua branch yang memiliki stok
                Object.keys(productData).forEach(branchId => {
                    const branchStock = productData[branchId];
                    
                    // Loop melalui items di branch ini
                    branchStock.items.forEach(item => {
                        $('#availableStockSelect').append(
                            `<option value="${item.id}" 
                                    data-branch-id="${branchId}"
                                    data-imei="${item.imei}" >
                                IMEI ${item.imei} (${branchStock.branch.name})
                            </option>`
                        );
                    });
                });
                
                // Hitung total available
                let totalAvailable = 0;
                Object.values(productData).forEach(branchStock => {
                    totalAvailable += branchStock.count;
                });
                
                // Show inventory details
                $('#inventoryDetails').html(`
                    <div class="alert alert-info mt-2">
                        <strong>${productData[Object.keys(productData)[0]].product_name}</strong><br>
                        Available: ${totalAvailable} units across ${Object.keys(productData).length} branches
                    </div>
                `);
            } else {
                $('#availableStockSelect').empty().append('<option value="">No available stock found for this product</option>');
            }
        } else {
            $('#availableStockSelect').empty().append('<option value="">No available stock found</option>');
        }
    }).fail(function() {
        $('#availableStockSelect').empty().append('<option value="">Error loading stock data</option>');
    });
    
    $('#reallocationModal').modal('show');
}

function submitReallocation() {
    const formData = {
        'order_item_id': $('#reallocOrderItemId').val(),
        'inventory_item_id': $('#availableStockSelect').val(),
        'notes': $('textarea[name="notes"]').val(),
        '_token': '{{ csrf_token() }}'
    };
    
    if (!formData.inventory_item_id) {
        alert('Please select an inventory item');
        return;
    }
    
    const url = '{{ route("manajer_operasional.orders.reallocate-single", $order->id) }}';
    
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#reallocationModal').modal('hide');
                alert('Stock reallocated successfully!');
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    location.reload();
                }
            } else {
                alert('Failed to reallocate stock: ' + response.message);
            }
        },
        error: function(xhr) {
            let message = 'An error occurred. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            alert(message);
        }
    });
}
</script>
@endpush