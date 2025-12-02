@extends('layouts.app')

@section('title', 'Tambah Pembelian')

@section('content')
<div class="container">
    <h4 class="mb-4">Tambah Pembelian</h4>

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Start --}}
    <form action="{{ route('purchases.store') }}" method="POST">
        @csrf
        
        <div class="row">
            {{-- Main Form Section --}}
            <div class="col-md-8">
                {{-- Supplier --}}
                <div class="mb-3">
                    <label for="supplier_id" class="form-label"><strong>Supplier</strong></label>
                    <select name="supplier_id" id="supplier_id" class="form-control" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal --}}
                <div class="form-group mb-4">
                    <label><strong>Tanggal</strong></label>
                    <input type="text" class="form-control" value="{{ \Carbon\Carbon::now()->format('d-m-Y') }}" readonly>
                </div>

                {{-- ===================== PRODUK HP ===================== --}}
                <hr>
                <h5 class="mb-3">Daftar Produk HP</h5>
                <div id="product-wrapper"></div>
                <button type="button" id="add-product" class="btn btn-secondary mt-2">+ Tambah Produk HP</button>

                {{-- ===================== ACCESSORIES ===================== --}}
                <hr>
                <h5 class="mb-3">Daftar Accessories</h5>
                <div id="accessory-wrapper"></div>
                <button type="button" id="add-accessory" class="btn btn-secondary mt-2">+ Tambah Accessories</button>

                {{-- Submit Button --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                </div>
            </div>

            {{-- ===================== RINGKASAN - RIGHT SIDE ===================== --}}
            <div class="col-md-4">
                <div class="d-flex flex-column h-100">
                    <div class="flex-grow-1"></div>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Ringkasan</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label"><strong>Total Qty</strong></label>
                                <input type="text" id="total-qty" class="form-control" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Total Harga</strong></label>
                                <input type="text" id="total-price" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let productIndex = 0;
    let accessoryIndex = 0;

    const lastPrices = @json($lastPrices ?? []);
    const products = @json($products ?? []);

    // Tambah Produk HP
    document.getElementById('add-product').addEventListener('click', function () {
        const wrapper = document.getElementById('product-wrapper');
        const row = document.createElement('div');
        row.classList.add('row', 'mb-2');

        let datalistId = `product-list-${productIndex}`;

        row.innerHTML = `
            <div class="col-md-5">
                <input type="hidden" name="products[${productIndex}][product_id]" class="product-id">
                <input type="text" class="form-control product-input" placeholder="Ketik nama HP..." list="${datalistId}" required>
                <datalist id="${datalistId}">
                    ${products.map(p => `<option data-id="${p.id}" value="${p.name}"></option>`).join('')}
                </datalist>
            </div>
            <div class="col-md-2">
                <input type="number" name="products[${productIndex}][qty]" class="form-control qty-input" placeholder="Qty" min="1" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="products[${productIndex}][price]" class="form-control price-input" placeholder="Harga Satuan" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
            </div>
        `;
        wrapper.appendChild(row);
        productIndex++;
        calculateSummary();
    });

    // Tambah Accessories
    document.getElementById('add-accessory').addEventListener('click', function () {
        const wrapper = document.getElementById('accessory-wrapper');
        const row = document.createElement('div');
        row.classList.add('row', 'mb-2');

        row.innerHTML = `
            <div class="col-md-5">
                <select name="accessories[${accessoryIndex}][accessory_id]" class="form-control">
                    <option value="">-- Pilih Accessories --</option>
                    @foreach ($accessories as $accessory)
                        <option value="{{ $accessory->id }}">{{ $accessory->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="accessories[${accessoryIndex}][qty]" class="form-control qty-input" placeholder="Qty" min="1">
            </div>
            <div class="col-md-3">
                <input type="text" name="accessories[${accessoryIndex}][price]" class="form-control price-input" placeholder="Harga Satuan">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
            </div>
        `;
        wrapper.appendChild(row);
        accessoryIndex++;
        calculateSummary();
    });

    // Hapus Row
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.row').remove();
            calculateSummary();
        }
    });

    // Mapping product input ke hidden product_id
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-input')) {
            let input = e.target;
            let list = input.getAttribute('list');
            let option = document.querySelector(`#${list} option[value="${input.value}"]`);
            if (option) {
                input.closest('.row').querySelector('.product-id').value = option.getAttribute('data-id');
                let lastPrice = lastPrices[option.getAttribute('data-id')];
                let priceInput = input.closest('.row').querySelector('.price-input');
                if (lastPrice && !priceInput.value) {
                    priceInput.value = lastPrice.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }
            }
        }
        calculateSummary();
    });

    // Format harga + hitung ulang
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('price-input')) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '')
                                           .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
            calculateSummary();
        }
    });

    // Hitung Ringkasan
    function calculateSummary() {
        let totalQty = 0;
        let totalPrice = 0;

        document.querySelectorAll('#product-wrapper .row, #accessory-wrapper .row').forEach(row => {
            let qty = parseInt(row.querySelector('.qty-input')?.value || 0);
            let price = parseInt((row.querySelector('.price-input')?.value || '0').replace(/,/g, ''));
            if (qty && price) {
                totalQty += qty;
                totalPrice += qty * price;
            }
        });

        document.getElementById('total-qty').value = totalQty;
        document.getElementById('total-price').value = totalPrice.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Bersihkan format harga saat submit
    document.querySelector('form').addEventListener('submit', function() {
        document.querySelectorAll('.price-input').forEach(function(input) {
            input.value = input.value.replace(/,/g, '');
        });
    });
</script>
@endpush