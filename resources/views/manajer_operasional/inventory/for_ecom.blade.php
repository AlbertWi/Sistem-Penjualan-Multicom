@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Inventory (Siap E-Commerce)</h3>

            <div class="card-tools">
                <form method="GET" action="{{ route('manajer_operasional.inventory.for_ecom') }}" class="input-group input-group-sm" style="width: 300px;">
                    <input type="text"
                           name="q"
                           class="form-control float-right"
                           placeholder="Cari IMEI / Produk / Brand"
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
            <table class="table table-hover table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>IMEI</th>
                        <th>Produk</th>
                        <th class="text-right">Buy Price</th>
                        <th>Harga E-Com</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->imei }}</td>
                        <td>
                            <strong>{{ $item->product->brand->name ?? '' }}</strong><br>
                            <small class="text-muted">{{ $item->product->name ?? '' }}</small>
                        </td>
                        <td class="text-right">
                            Rp {{ number_format($item->purchase_price,0,',','.') }}
                        </td>
                        <td>
                            <form action="{{ route('manajer_operasional.inventory.update_price', $item) }}"
                                  method="POST" class="d-flex">
                                @csrf
                                <input type="number"
                                       name="ecom_price"
                                       class="form-control form-control-sm mr-2"
                                       value="{{ $item->ecom_price }}">
                                <button class="btn btn-sm btn-primary">
                                    Simpan
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            @if($item->is_listed)
                                <span class="badge badge-success">Posted</span>
                            @else
                                <span class="badge badge-secondary">Belum</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!$item->is_listed)
                                <form action="{{ route('manajer_operasional.inventory.post', $item) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-success">
                                        <i class="fas fa-upload"></i> Post
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('manajer_operasional.inventory.unpost', $item) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-warning">
                                        <i class="fas fa-times"></i> Unpost
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Data tidak ditemukan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer clearfix">
            {{ $items->appends(['q' => request('q')])->links() }}
        </div>
    </div>

</div>
@endsection
