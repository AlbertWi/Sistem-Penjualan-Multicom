@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Detail Pembelian</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{!! $error !!}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') }}</p>
        <p><strong>Cabang:</strong> {{ $purchase->branch->name ?? 'N/A' }}</p>
    </div>

    {{-- ================= PRODUK HP ================= --}}
    @if($purchase->items->sum(fn($item) => $item->inventoryItems->count()) > 0)
        <form id="imeiForm" action="{{ route('purchases.save_imei', $purchase->id) }}" method="POST">
            @csrf
            <h5>Daftar Produk HP</h5>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>IMEI</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->items as $item)
                        @foreach ($item->inventoryItems as $inventory)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>1 unit</td>
                                <td>
                                    <input type="text" name="imeis[{{ $inventory->id }}]" 
                                           value="{{ old("imeis.$inventory->id", $inventory->imei) }}"
                                           class="form-control imei-input" placeholder="Scan / Masukkan IMEI">
                                </td>
                                <td>
                                    <span class="badge bg-{{ $inventory->status == 'in_stock' ? 'success' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $inventory->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan IMEI
                </button>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    @endif

    {{-- ================= ACCESSORIES ================= --}}
    @if($purchase->purchaseAccessories->count() > 0)
        <h5 class="mt-5">Daftar Accessories</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Nama Accessories</th>
                    <th>Qty</th>
                    <th>Harga Beli</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->purchaseAccessories as $acc)
                    <tr>
                        <td>{{ $acc->accessory->name }}</td>
                        <td>{{ $acc->qty }}</td>
                        <td>Rp {{ number_format($acc->price,0,',','.') }}</td>
                        <td>Rp {{ number_format($acc->qty * $acc->price,0,',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const imeiInputs = document.querySelectorAll(".imei-input");
    const form = document.getElementById("imeiForm");

    imeiInputs.forEach((input, index) => {
        input.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();

                // Cek apakah ada input dengan IMEI yang sama
                let currentVal = this.value.trim();
                if (currentVal !== "") {
                    let duplicate = false;

                    imeiInputs.forEach((other, idx) => {
                        if (idx !== index && other.value.trim() === currentVal) {
                            duplicate = true;
                        }
                    });

                    if (duplicate) {
                        alert("⚠️ IMEI ini sudah pernah dimasukkan!");
                        this.value = ""; // kosongkan input duplikat
                        this.focus();
                        return;
                    }
                }

                // Kalau bukan duplikat → pindah ke input berikutnya
                let nextInput = imeiInputs[index + 1];
                if (nextInput) {
                    nextInput.focus();
                } else {
                    // kalau terakhir → auto submit form (opsional)
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush

