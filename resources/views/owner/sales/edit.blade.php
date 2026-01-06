@extends('layouts.app')

@section('title', 'Edit Penjualan')

@section('content')
<div class="container">
    <h4 class="mb-4">Edit Penjualan</h4>

    <form action="{{ route('owner.sales.update', $sale->id) }}" method="POST" id="saleForm">
        @csrf
        @method('PUT')

        {{-- DATA KONSUMEN --}}
        <div class="card mb-4">
            <div class="card-header">Data Konsumen</div>
            <div class="card-body">
                <select name="customer_id" class="form-control" required>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}"
                            {{ $sale->customer_id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- TAMBAH ITEM --}}
        <div class="card mb-4">
            <div class="card-header">Tambah Item</div>
            <div class="card-body">
                <input type="text" id="imeiInput" class="form-control mb-3" placeholder="Scan IMEI">
                <input type="text" id="accessorySearch" class="form-control" placeholder="Cari Aksesoris">
                <div id="accessoryList" class="list-group"></div>
            </div>
        </div>

        {{-- DAFTAR ITEM --}}
        <table class="table table-bordered" id="itemsTable">
            <thead>
                <tr>
                    <th>Jenis</th>
                    <th>Nama</th>
                    <th>IMEI</th>
                    <th>Harga</th>
                    <th>Modal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th id="totalPrice">0</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>

        <button class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('owner.sales.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
@push('scripts')
<script>
let items = @json($itemsForJs);

function formatNumber(num){
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g,",");
}

function updateTotal(){
    let total = items.reduce((s,i)=>s+parseFloat(i.price),0);
    document.getElementById('totalPrice').innerText = formatNumber(total);
}
document.getElementById('saleForm').addEventListener('submit', function(e){
    if(items.length === 0){
        if(!confirm('Semua item dihapus. Penjualan akan DIHAPUS. Lanjutkan?')){
            e.preventDefault();
        }
    }
});
function renderItems(){
    let tbody = document.querySelector("#itemsTable tbody");
    tbody.innerHTML = "";

    items.forEach((item, i)=>{
        tbody.innerHTML += `
        <tr>
            <td>${item.type}</td>
            <td>${item.name}</td>
            <td>${item.imei ?? '-'}</td>
            <td>
                <input type="hidden" name="items[${i}][imei]" value="${item.imei ?? ''}">
                <input type="hidden" name="items[${i}][accessory_id]" value="${item.accessory_id ?? ''}">
                <input type="hidden" name="items[${i}][modal]" value="${item.modal}">
                <input type="number" class="form-control"
                    name="items[${i}][price]" value="${item.price}">
            </td>
            <td>Rp ${formatNumber(item.modal)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm"
                    onclick="items.splice(${i},1);renderItems()">Hapus</button>
            </td>
        </tr>`;
    });

    updateTotal();
}

renderItems();
</script>
@endpush
