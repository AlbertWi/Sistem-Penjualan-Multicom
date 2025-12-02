@extends('layouts.app')

@section('title', 'Tambah Penjualan')

@section('content')
<div class="container">
    <h4 class="mb-4">Tambah Penjualan</h4>
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
        @csrf
        
        <div class="card mb-4">
            <div class="card mb-4">
                <div class="card-header">Data Konsumen</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Konsumen</label>
                        <select name="customer_id" id="customer_id" class="form-control" required>
                            <option value="">-- Pilih Konsumen --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-header">Tambah Item</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="imei" class="form-label">Cari IMEI</label>
                    <input type="text" id="imeiInput" class="form-control" placeholder="Masukkan IMEI lalu Enter">
                </div>
                
                <div class="mb-3">
                    <label for="accessorySearch" class="form-label">Cari Aksesoris</label>
                    <input type="text" id="accessorySearch" class="form-control" placeholder="Ketik nama aksesoris">
                    <div id="accessoryList" class="list-group"></div>
                </div>
            </div>
        </div>
        
        <h5>Daftar Item</h5>
        <table class="table table-bordered" id="itemsTable">
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Nama</th>
                    <th>IMEI</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Modal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th id="totalPrice">0</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        
        <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let items = [];

    // Format angka dengan ribuan
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function parseNumber(str) {
        return parseFloat(str.replace(/,/g, "")) || 0;
    }

    // Hitung total harga
    function updateTotal() {
        let total = items.reduce((sum, item) => sum + parseFloat(item.price), 0);
        document.getElementById('totalPrice').innerText = formatNumber(total);
    }

    // Render ulang tabel items
    function renderItems() {
        let tbody = document.querySelector("#itemsTable tbody");
        tbody.innerHTML = "";
        
        items.forEach((item, index) => {
            let row = `
                <tr>
                    <td>${item.type}</td>
                    <td>${item.name}</td>
                    <td>${item.imei ?? '-'}</td>
                    <td>1</td>
                    <td>
                        <input type="text" class="form-control price-input" data-index="${index}" value="${formatNumber(item.price)}">
                        <input type="hidden" name="items[${index}][imei]" value="${item.imei ?? ''}">
                        <input type="hidden" name="items[${index}][accessory_id]" value="${item.accessory_id ?? ''}">
                        <input type="hidden" name="items[${index}][purchase_accessory_id]" value="${item.purchase_accessory_id ?? ''}">
                        <input type="hidden" class="price-hidden" name="items[${index}][price]" value="${item.price}">
                        <input type="hidden" name="items[${index}][modal]" value="${item.modal ?? 0}">
                    </td>
                    <td>Rp ${formatNumber(item.modal ?? 0)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${index})">Hapus</button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        updateTotal();

        // Event ubah harga manual
        document.querySelectorAll('.price-input').forEach(input => {
            input.addEventListener('input', function() {
                let idx = this.dataset.index;
                let rawValue = parseNumber(this.value);
                this.value = formatNumber(rawValue);
                items[idx].price = rawValue;
                document.querySelector(`input[name="items[${idx}][price]"]`).value = rawValue;
                updateTotal();
            });
        });
    }

    function removeItem(index) {
        items.splice(index, 1);
        renderItems();
    }

    // 🔍 Cari IMEI
    document.getElementById('imeiInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let imei = this.value.trim();
            if (!imei) return;

            fetch(`{{ route('search.by.imei') }}?imei=${imei}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        let product = data.inventory.product;
                        items.push({
                            type: 'Phone',
                            name: product.name,
                            imei: data.inventory.imei,
                            price: product.price_jual ?? 0,
                            modal: data.inventory.purchase_price ?? 0,
                            product_id: product.id
                        });
                        renderItems();
                        document.getElementById('imeiInput').value = "";
                    } else {
                        alert(data.message);
                    }
                });
        }
    });

    // 🔍 Cari Aksesoris (autocomplete)
    document.getElementById('accessorySearch').addEventListener('input', function() {
        let keyword = this.value;
        
        if (keyword.length < 2) {
            document.getElementById('accessoryList').innerHTML = "";
            return;
        }

        fetch(`{{ route('sales.searchAccessory') }}?q=${keyword}`)
            .then(res => res.json())
            .then(data => {
                let list = document.getElementById('accessoryList');
                list.innerHTML = "";
                
                if (data.success) {
                    data.data.forEach(acc => {
                        let item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${acc.accessory_name} (Stok: ${acc.qty})`;
                        item.onclick = () => {
                            items.push({
                                type: 'Accessory',
                                name: acc.accessory_name,
                                accessory_id: acc.accessory_id,
                                purchase_accessory_id: acc.purchase_accessory_id || null,
                                price: 0,
                                modal: acc.modal || 0
                            });
                            renderItems();
                            document.getElementById('accessorySearch').value = "";
                            list.innerHTML = "";
                        };
                        list.appendChild(item);
                    });
                }
            });
    });

    // Bersihkan format harga sebelum submit
    document.getElementById('saleForm').addEventListener('submit', function() {
        document.querySelectorAll('.price-input').forEach((input, idx) => {
            let raw = parseNumber(input.value);
            items[idx].price = raw;
            document.querySelector(`input[name="items[${idx}][price]"]`).value = raw;
        });
    });
</script>
@endpush