@extends('layouts.catalog')

@section('title', 'Detail Pesanan - ' . config('app.name'))

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('orders.index') }}">Riwayat Pesanan</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
                </ol>
            </nav>
        </div>
        
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Pesanan</h5>
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
                    <div>
                        <span class="badge bg-{{ $statusColors[$order->status] }} me-2">
                            {{ ucfirst($order->status) }}
                        </span>
                        <span class="badge bg-{{ $paymentColors[$order->payment_status] }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Pesanan</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nomor Order</th>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>{{ $order->order_date->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Total Item</th>
                                    <td>{{ $order->items->count() }} item</td>
                                </tr>
                                <tr>
                                    <th>Total Amount</th>
                                    <td class="fw-bold text-primary">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Informasi Pembayaran</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Status</th>
                                    <td>
                                        <span class="badge bg-{{ $paymentColors[$order->payment_status] }}">
                                            @if($order->payment_status == 'pending')
                                                Menunggu Pembayaran
                                            @elseif($order->payment_status == 'paid')
                                                Lunas
                                            @else
                                                Gagal
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @if($order->payment_method)
                                <tr>
                                    <th>Metode</th>
                                    <td>{{ ucfirst($order->payment_method) }}</td>
                                </tr>
                                @endif
                                @if($order->paid_at)
                                <tr>
                                    <th>Dibayar pada</th>
                                    <td>{{ $order->paid_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    <h6>Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product->images->first())
                                            <img src="{{ asset('storage/' . $item->product->images->first()->file_path) }}" 
                                                 alt="{{ $item->product->name }}"
                                                 class="rounded me-3"
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <strong>{{ $item->product->name }}</strong><br>
                                                <small class="text-muted">{{ $item->product->brand->name ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end">
                                        <strong class="text-primary">
                                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    @if($order->notes)
                    <div class="mt-4">
                        <h6>Catatan:</h6>
                        <p class="text-muted">{{ $order->notes }}</p>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        
                        @if($order->status == 'pending' && $order->payment_status == 'pending')
                        <div>
                            <button type="button" 
                                    class="btn btn-success me-2"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#paymentModal">
                                <i class="fas fa-credit-card me-2"></i>Bayar
                            </button>
                            <form action="{{ route('orders.cancel', $order->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin membatalkan pesanan?')">
                                    <i class="fas fa-times me-2"></i>Batalkan
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Status Pesanan</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @php
                            $steps = [
                                'pending' => ['icon' => 'clock', 'label' => 'Menunggu', 'color' => 'warning'],
                                'processing' => ['icon' => 'cog', 'label' => 'Diproses', 'color' => 'info'],
                                'completed' => ['icon' => 'check', 'label' => 'Selesai', 'color' => 'success'],
                            ];
                        @endphp
                        
                        @foreach($steps as $status => $step)
                        <div class="timeline-item d-flex align-items-start mb-3">
                            <div class="timeline-icon me-3">
                                <div class="rounded-circle bg-{{ $step['color'] }} p-2 d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-{{ $step['icon'] }} text-white"></i>
                                </div>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ $step['label'] }}</h6>
                                <p class="text-muted small mb-0">
                                    @if($order->status == $status)
                                        <span class="text-{{ $step['color'] }}">• Sedang berjalan</span>
                                    @elseif(array_search($order->status, array_keys($steps)) >= array_search($status, array_keys($steps)))
                                        <span class="text-success">• Selesai</span>
                                    @else
                                        <span class="text-muted">• Menunggu</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Butuh Bantuan?</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        Jika ada pertanyaan mengenai pesanan ini, hubungi customer service kami.
                    </p>
                    <div class="d-grid gap-2">
                        <a href="mailto:support@example.com" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Email Support
                        </a>
                        <a href="tel:+628123456789" class="btn btn-outline-success">
                            <i class="fas fa-phone me-2"></i>Telepon
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('orders.payment', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Konfirmasi pembayaran untuk order:</p>
                    <p><strong>{{ $order->order_number }}</strong></p>
                    <p>Total: <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></p>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="">Pilih metode...</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="cash">Cash</option>
                            <option value="qris">QRIS</option>
                            <option value="ewallet">E-Wallet</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_proof" class="form-label">Bukti Pembayaran</label>
                        <input type="file" class="form-control" id="payment_proof" name="payment_proof">
                        <small class="text-muted">Upload bukti transfer atau pembayaran (jpg, png, pdf)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="payment_notes" name="payment_notes" rows="2" 
                                  placeholder="Contoh: Transfer dari BCA, No. Rek: 123456789"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline-item {
        position: relative;
    }
    .timeline-item:not(:last-child):before {
        content: '';
        position: absolute;
        left: 20px;
        top: 50px;
        width: 2px;
        height: calc(100% + 1rem);
        background-color: #dee2e6;
    }
    .timeline-icon .rounded-circle {
        z-index: 1;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush