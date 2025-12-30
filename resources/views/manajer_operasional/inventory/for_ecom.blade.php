@extends('layouts.app')

@section('content')
<div class="container-fluid">

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Produk Siap E-Commerce</h3>

        <div class="card-tools">
            <form method="GET"
                  action="{{ route('manajer_operasional.inventory.for_ecom') }}"
                  class="input-group input-group-sm"
                  style="width: 300px;">
                <input type="text"
                       name="q"
                       class="form-control"
                       placeholder="Cari Produk / Brand"
                       value="{{ request('q') }}">
                <div class="input-group-append">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Produk</th>
                    <th class="text-center">Stok</th>
                    <th>Harga E-Commerce</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        <strong>{{ $product->brand->name }}</strong><br>
                        <small class="text-muted">{{ $product->name }}</small>
                    </td>

                    <td class="text-center">
                        {{ $product->stock_count }}
                    </td>

                    <td>
                        <form method="POST"
                              action="{{ route('manajer_operasional.product_ecom.update', $product) }}"
                              class="d-flex">
                            @csrf

                            <input type="number"
                                   name="ecom_price"
                                   class="form-control form-control-sm mr-2"
                                   value="{{ optional($product->ecomSetting)->ecom_price }}"
                                   required>

                            <button class="btn btn-sm btn-primary">Simpan</button>
                        </form>
                    </td>

                    <td class="text-center">
                        @if(optional($product->ecomSetting)->is_listed)
                            <span class="badge badge-success    ">Posted</span>
                        @else
                            <span class="badge badge-secondary">Belum</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <form method="POST"
                            action="{{ route('manajer_operasional.product_ecom.update', $product) }}">
                            @csrf

                            <input type="hidden" name="ecom_price"
                                value="{{ optional($product->ecomSetting)->ecom_price ?? 0 }}">

                            <input type="hidden" name="is_listed"
                                value="{{ optional($product->ecomSetting)->is_listed ? 0 : 1 }}">

                            <button class="btn btn-sm {{ optional($product->ecomSetting)->is_listed ? 'btn-warning' : 'btn-success' }}">
                                {{ optional($product->ecomSetting)->is_listed ? 'Unpost' : 'Post' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Data tidak ditemukan
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        {{ $products->links() }}
    </div>
</div>

</div>
@endsection
