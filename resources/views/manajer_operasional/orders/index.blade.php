@extends('layouts.app')

@section('title', 'Online Orders Management')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Online Orders Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Online Orders</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['total_orders'] }}</h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <a href="?status=" class="small-box-footer">
                            View All <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['pending_orders'] }}</h3>
                            <p>Pending Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="?status=pending" class="small-box-footer">
                            View Pending <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $stats['processing_orders'] }}</h3>
                            <p>Processing</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <a href="?status=processing" class="small-box-footer">
                            View Processing <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['pending_payments'] }}</h3>
                            <p>Pending Payments</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <a href="?payment_status=pending" class="small-box-footer">
                            View Payments <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Filters</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('manajer_operasional.orders.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select name="payment_status" class="form-control">
                                        <option value="">All Payments</option>
                                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" 
                                           value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" 
                                           value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Search</label>
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="Order number, customer name, email, phone..."
                                               value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group" style="padding-top: 32px;">
                                    <button type="submit" class="btn btn-primary mr-2">
                                        <i class="fas fa-filter"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('manajer_operasional.orders.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Orders List</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm">
                            <div class="input-group-append">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->order_number }}</strong>
                                    @if($order->stock_picked_at)
                                        <br><small class="text-success">
                                            <i class="fas fa-check-circle"></i> Stock picked
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $order->customer->name }}</strong><br>
                                    <small>{{ $order->customer->email }}</small><br>
                                    <small>{{ $order->customer->phone }}</small>
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $order->items->count() }} items</span><br>
                                    <small>
                                        @foreach($order->items->take(2) as $item)
                                            {{ $item->product->name }}<br>
                                        @endforeach
                                        @if($order->items->count() > 2)
                                            +{{ $order->items->count() - 2 }} more
                                        @endif
                                    </small>
                                </td>
                                <td class="font-weight-bold text-primary">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$order->status] }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $paymentColors = [
                                            'pending' => 'warning',
                                            'paid' => 'success',
                                            'failed' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $paymentColors[$order->payment_status] }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                    @if($order->paid_at)
                                        <br><small>{{ $order->paid_at->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('manajer_operasional.orders.show', $order->id) }}" 
                                           class="btn btn-sm btn-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manajer_operasional.orders.print', $order->id) }}" 
                                           class="btn btn-sm btn-secondary" title="Print Invoice" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if($order->status == 'pending')
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="confirmAction('{{ route('manajer_operasional.orders.confirm-stock-pickup', $order->id) }}', 
                                                    'Konfirmasi pengambilan stok untuk order ini?')"
                                                title="Confirm Stock Pickup">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                        <h5>No Orders Found</h5>
                                        <p class="text-muted">No online orders match your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $orders->links() }}
                    </div>
                    <div class="float-left">
                        <span class="text-muted">
                            Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
function confirmAction(url, message) {
    if (confirm(message)) {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-refresh for pending orders
@if(request('status') == 'pending' || request('status') == '')
setTimeout(function() {
    window.location.reload();
}, 30000); // Refresh every 30 seconds for pending orders
@endif
</script>
@endpush

@push('styles')
<style>
.small-box {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.small-box .icon {
    font-size: 70px;
    top: 10px;
}
.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}
.badge {
    font-size: 0.85em;
    padding: 5px 10px;
}
</style>
@endpush