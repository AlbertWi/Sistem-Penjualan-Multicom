    @extends('layouts.app')

    @section('content')
    <div class="container">
        <h3>Daftar Produk</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createProductModal">
                + Tambah Produk
            </button>

            <form method="GET" action="{{ route('manajer_operasional.products.index') }}" class="d-flex" style="max-width: 300px;">
                <input type="text" name="q" class="form-control me-2" placeholder="Cari nama produk..." value="{{ request('q') }}">
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>Nama</th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>

                        <td>
                            @if($product->images->count())
                                <img src="{{ asset('storage/'.$product->images->first()->file_path) }}"
                                width="60" height="60">
                            @else
                                <span class="text-muted">Tidak ada</span>
                            @endif
                        </td>

                        <td>{{ $product->name }}</td>
                        <td>{{ $product->brand->name ?? '-' }}</td>
                        <td>{{ $product->type->name ?? '-' }}</td>

                        <td>
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal{{ $product->id }}">
                                Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('manajer_operasional.products.update', $product->id) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Produk</h5>
                                    </div>
                                    <div class="modal-body">

                                        <div class="mb-2">
                                            <label>Foto Produk</label><br>
                                            @if($product->images->count())
                                                <img src="{{ asset('storage/'.$product->images->first()->file_path) }}"
                                                width="80" class="mb-2 rounded">
                                            @endif
                                            <input type="file" name="foto[]" multiple class="form-control">
                                        </div>

                                        <div class="mb-2">
                                            <label>Nama Produk</label>
                                            <input type="text" name="name" class="form-control" value="{{ $product->name }}">
                                        </div>

                                        <div class="mb-2">
                                            <label>Brand</label>
                                            <input type="text" class="form-control" value="{{ $product->brand->name }}" disabled>
                                            <input type="hidden" name="brand_id" value="{{ $product->brand_id }}">
                                        </div>

                                        <div class="mb-2">
                                            <label>Type</label>
                                            <input type="text" class="form-control" value="{{ $product->type->name }}" disabled>
                                            <input type="hidden" name="type_id" value="{{ $product->type_id }}">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label>RAM (GB)</label>
                                                <select name="ram" class="form-control" required>
                                                    @foreach([2,4,6,8,12,16,24,32] as $ram)
                                                        <option value="{{ $ram }}"
                                                            {{ $product->ram == $ram ? 'selected' : '' }}>
                                                            {{ $ram }} GB
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label>ROM (GB)</label>
                                                <select name="rom" class="form-control" required>
                                                    @foreach([32,64,128,256,512,1024] as $rom)
                                                        <option value="{{ $rom }}"
                                                            {{ $product->rom == $rom ? 'selected' : '' }}>
                                                            {{ $rom }} GB
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label>Baterai (mAh)</label>
                                                <select name="baterai" class="form-control" required>
                                                    @foreach([4000,4500,5000,6000] as $bat)
                                                        <option value="{{ $bat }}"
                                                            {{ $product->baterai == $bat ? 'selected' : '' }}>
                                                            {{ $bat }} mAh
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label>Ukuran Layar (inci)</label>
                                                <select name="ukuran_layar" class="form-control" required>
                                                    @foreach([5.5,6.1,6.5,6.7,6.8] as $layar)
                                                        <option value="{{ $layar }}"
                                                            {{ $product->ukuran_layar == $layar ? 'selected' : '' }}>
                                                            {{ $layar }}"
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label>Masa Garansi (bulan)</label>
                                                <select name="masa_garansi" class="form-control" required>
                                                    @foreach([6,12,18,24] as $garansi)
                                                        <option value="{{ $garansi }}"
                                                            {{ $product->masa_garansi == $garansi ? 'selected' : '' }}>
                                                            {{ $garansi }} Bulan
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label>Resolusi Kamera</label>
                                                <select name="resolusi_kamera" class="form-control" required>
                                                    @foreach(['12 MP','48 MP','50 MP','64 MP','108 MP'] as $kamera)
                                                        <option value="{{ $kamera }}"
                                                            {{ $product->resolusi_kamera == $kamera ? 'selected' : '' }}>
                                                            {{ $kamera }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label>Jumlah Slot SIM</label>
                                                <select name="jumlah_slot_sim" class="form-control" required>
                                                    <option value="1" {{ $product->jumlah_slot_sim == 1 ? 'selected' : '' }}>1 SIM</option>
                                                    <option value="2" {{ $product->jumlah_slot_sim == 2 ? 'selected' : '' }}>2 SIM</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                @empty
                    <tr>
                        <td colspan="6">Tidak ada produk ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal Tambah Produk -->
    <div class="modal fade" id="createProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('manajer_operasional.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Produk</h5>
                    </div>

                    <div class="modal-body">

                        <div class="mb-2">
                            <label>Foto Produk</label>
                            <input type="file" name="foto[]" multiple class="form-control">
                        </div>

                        <div class="mb-2">
                            <label>Brand</label>
                            <select name="brand_id" id="brandSelect" class="form-control">
                                <option value="">-- Pilih Brand --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" data-name="{{ $brand->name }}">
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Type</label>
                            <select name="type_id" id="typeSelect" class="form-control">
                                <option value="">-- Pilih Type --</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" data-name="{{ $type->name }}">
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label>RAM (GB)</label>
                                <select name="ram" class="form-control" required>
                                    <option value="">Pilih RAM</option>
                                    @foreach([2,4,6,8,12,16,24,32] as $ram)
                                        <option value="{{ $ram }}">{{ $ram }} GB</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>ROM (GB)</label>
                                <select name="rom" class="form-control" required>
                                    <option value="">Pilih ROM</option>
                                    @foreach([32,64,128,256,512,1024] as $rom)
                                        <option value="{{ $rom }}">{{ $rom }} GB</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Baterai (mAh)</label>
                                <select name="baterai" class="form-control" required>
                                    @foreach([4000,4500,5000,6000] as $bat)
                                        <option value="{{ $bat }}">{{ $bat }} mAh</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Ukuran Layar (inci)</label>
                                <select name="ukuran_layar" class="form-control" required>
                                    @foreach([5.5,6.1,6.5,6.7,6.8] as $layar)
                                        <option value="{{ $layar }}">{{ $layar }}"</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Masa Garansi (bulan)</label>
                                <select name="masa_garansi" class="form-control" required>
                                    @foreach([6,12,18,24] as $garansi)
                                        <option value="{{ $garansi }}">{{ $garansi }} Bulan</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Resolusi Kamera</label>
                                <select name="resolusi_kamera" class="form-control" required>
                                    @foreach(['12 MP','48 MP','50 MP','64 MP','108 MP'] as $kamera)
                                        <option value="{{ $kamera }}">{{ $kamera }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label>Jumlah Slot SIM</label>
                                <select name="jumlah_slot_sim" class="form-control" required>
                                    <option value="1">1 SIM</option>
                                    <option value="2">2 SIM</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label>Nama Produk</label>
                            <input type="text" name="name" id="productName" class="form-control">
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>

                </div>
            </form>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const brandSelect = document.getElementById('brandSelect');
            const typeSelect = document.getElementById('typeSelect');
            const productName = document.getElementById('productName');

            function updateProductName() {
                const brand = brandSelect.selectedOptions[0]?.dataset.name || '';
                const type = typeSelect.selectedOptions[0]?.dataset.name || '';

                productName.value = brand && type ? `${brand} ${type}` : '';
            }

            brandSelect.addEventListener('change', function() {
                fetch(`/ajax/types-by-brand/${brandSelect.value}`)
                    .then(res => res.json())
                    .then(data => {

                        typeSelect.innerHTML = `<option value="">-- Pilih Type --</option>`;

                        data.forEach(t => {
                            typeSelect.innerHTML += `
                                <option value="${t.id}" data-name="${t.name}">
                                    ${t.name}
                                </option>`;
                        });

                        updateProductName();
                    });

            });
            typeSelect.addEventListener('change', updateProductName);
        });
    </script>

    @endsection
