@extends('layouts.app')

@section('title', 'Buat Transfer Stok')

@section('content')
<div class="container">
    <h4 class="mb-4">Buat Transfer Stok</h4>

    {{-- Alert Error / Success --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('stock-transfers.store') }}" method="POST" id="transferForm">
        @csrf

        <div class="mb-3">
            <label for="to_branch_id" class="form-label">Pilih Cabang Tujuan</label>
            <select name="to_branch_id" id="to_branch_id" class="form-control" required>
                <option value="">-- Pilih Cabang --</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="imeiInput" class="form-label">Scan / Input IMEI</label>
            <input type="text" id="imeiInput" class="form-control" placeholder="Masukkan IMEI lalu Enter">
        </div>

        <table class="table table-bordered" id="imeiTable">
            <thead class="table-light">
                <tr>
                    <th>IMEI</th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Row IMEI akan muncul di sini --}}
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Simpan Transfer</button>
        <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

{{-- Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imeiInput = document.getElementById('imeiInput');
    const tableBody = document.querySelector('#imeiTable tbody');

    // Simpan IMEI yang sudah diinput untuk mencegah duplikat
    let imeiList = [];

    imeiInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let imei = imeiInput.value.trim();
            if (!imei) return;

            // Cek duplikat di array
            if (imeiList.includes(imei)) {
                alert('IMEI sudah ditambahkan.');
                imeiInput.value = '';
                return;
            }

            // Fetch ke server
            fetch(`/stock-transfers/find-by-imei/${encodeURIComponent(imei)}`)
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => { throw err; });
                    }
                    return res.json();
                })
                .then(res => {
                    if (res.success) {
                        let row = `
                            <tr data-imei="${res.data.imei}">
                                <td>
                                    ${res.data.imei}
                                    <input type="hidden" name="imeis[]" value="${res.data.imei}">
                                </td>
                                <td>${res.data.brand}</td>
                                <td>${res.data.type}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-row">Hapus</button>
                                </td>
                            </tr>
                        `;
                        tableBody.insertAdjacentHTML('beforeend', row);
                        imeiList.push(res.data.imei);
                        imeiInput.value = '';
                    }
                })
                .catch(err => {
                    alert(err.message || 'IMEI tidak ditemukan atau tidak tersedia.');
                    imeiInput.value = '';
                });
        }
    });

    // Hapus row dari tabel
    tableBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            let row = e.target.closest('tr');
            let imei = row.dataset.imei;
            imeiList = imeiList.filter(i => i !== imei);
            row.remove();
        }
    });
});
</script>
@endsection
