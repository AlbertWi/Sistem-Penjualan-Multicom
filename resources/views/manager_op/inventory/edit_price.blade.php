@extends('layouts.app')

@section('title', 'Ubah Harga Modal Produk')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Ubah Harga Modal Produk</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('inventory.updatePrice') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="product_id">Pilih Produk</label>
                    <select class="form-control select2" name="product_id" id="product_id" required>
                        <option value="">-- Cari & Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="purchase_price">Harga Modal Baru</label>
                    <input type="number" name="purchase_price" id="purchase_price" class="form-control" step="0.01" placeholder="Masukkan harga baru" required>
                </div>

                <button type="submit" class="btn btn-success w-100">Update Semua Produk In Stock</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Inisialisasi select2 jika dipakai
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#product_id').select2({
                placeholder: 'Cari produk...',
                allowClear: true
            });
        }
    });
</script>
@endpush
