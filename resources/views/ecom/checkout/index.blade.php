@extends('layouts.catalog')

@section('title', 'Riwayat Pesanan - ' . config('app.name'))

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Riwayat Pesanan</h1>
                <a href="{{ route('catalog.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Lanjut Belanja
                </a>
            </div>

            @if($orders->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-shopping-cart fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Belum ada pesanan</h4>
                    <p class="text-muted mb-4">Mulai belanja dan buat pesanan pertama Anda</p>
                    <a href="{{ route('catalog.index') }}" class="btn btn-primary">
                        <i class="fas fa-shopping-bag me-2"></i>Mulai Belanja
                    </a>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Tanggal</th>
                                    <th>Nomor Order</th>
                                    <th>Status</th>
                                    <th>Pembayaran</th>
                                    <th class="text-end pe-4">Total</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td class="ps-4">{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $order->order_date->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">{{ $order->order_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $order->items->count() }} item</small>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $color = $statusColors[$order->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            @if($order->status == 'pending')
                                                Menunggu
                                            @elseif($order->status == 'processing')
                                                Diproses
                                            @elseif($order->status == 'completed')
                                                Selesai
                                            @else
                                                Dibatalkan
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $paymentColors = [
                                                'pending' => 'warning',
                                                'paid' => 'success',
                                                'failed' => 'danger'
                                            ];
                                            $pColor = $paymentColors[$order->payment_status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $pColor }}">
                                            @if($order->payment_status == 'pending')
                                                Menunggu
                                            @elseif($order->payment_status == 'paid')
                                                Lunas
                                            @else
                                                Gagal
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <strong class="text-primary">
                                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('orders.show', $order->id) }}" 
                                               class="btn btn-outline-primary"
                                               title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($order->payment_status == 'pending' && $order->status != 'cancelled')
                                            <button type="button" 
                                                    class="btn btn-outline-success"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#paymentModal{{ $order->id }}"
                                                    title="Bayar">
                                                <i class="fas fa-credit-card"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                {{-- Payment Modal --}}
                                <div class="modal fade" id="paymentModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Konfirmasi pembayaran untuk order:</p>
                                                <p><strong>{{ $order->order_number }}</strong></p>
                                                <p>Total: <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></p>
                                                
                                                <form action="{{ route('orders.payment', $order->id) }}" method="POST" id="paymentForm{{ $order->id }}">
                                                    @csrf
                                                    @method('PUT')
                                                    
                                                    <div class="mb-3">
                                                        <label for="payment_method{{ $order->id }}" class="form-label">Metode Pembayaran</label>
                                                        <select name="payment_method" id="payment_method{{ $order->id }}" class="form-select" required>
                                                            <option value="">Pilih metode...</option>
                                                            <option value="transfer">Transfer Bank</option>
                                                            <option value="cash">Cash</option>
                                                            <option value="qris">QRIS</option>
                                                            <option value="ewallet">E-Wallet</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="payment_proof{{ $order->id }}" class="form-label">Bukti Pembayaran (Opsional)</label>
                                                        <input type="file" class="form-control" id="payment_proof{{ $order->id }}" name="payment_proof">
                                                        <small class="text-muted">Upload bukti transfer atau pembayaran</small>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="payment_notes{{ $order->id }}" class="form-label">Catatan</label>
                                                        <textarea class="form-control" id="payment_notes{{ $order->id }}" name="payment_notes" rows="2"></textarea>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" form="paymentForm{{ $order->id }}" class="btn btn-success">
                                                    <i class="fas fa-check me-2"></i>Konfirmasi Pembayaran
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- Pagination --}}
                @if($orders->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $orders->links() }}
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.8em;
    }
</style>
@endpush

@push('scripts')
<!-- Bootstrap JS for Modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Filter orders by status
    const filterButtons = document.querySelectorAll('.order-filter');
    filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const status = this.dataset.status;
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter rows
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (status === 'all') {
                    row.style.display = '';
                } else {
                    const rowStatus = row.querySelector('.status-badge').textContent.trim().toLowerCase();
                    row.style.display = rowStatus.includes(status) ? '' : 'none';
                }
            });
        });
    });
});
</script>
@endpush