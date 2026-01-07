@extends('layouts.app')

@section('title', 'Assign IMEI')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Assign IMEI – {{ $order->order_number }}</h5>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>IMEI</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>

                        <td>
                            @if($item->inventory_item_id)
                                <span class="badge bg-success">
                                    {{ $item->inventoryItem->imei }}
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    Belum di-assign
                                </span>
                            @endif
                        </td>

                        <td>
                            @if(!$item->inventory_item_id)
                            <form method="POST" action="{{ route('manajer_operasional.orders.assign-imei.store', $item->id) }}">
                                @csrf
                                <div class="d-flex">
                                    <select name="inventory_item_id" class="form-control me-2" required>
                                        <option value="">Pilih IMEI</option>
                                        @foreach($inventories->where('product_id', $item->product_id) as $inv)
                                            <option value="{{ $inv->id }}">
                                                {{ $inv->imei }} ({{ $inv->branch->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-primary btn-sm">
                                        Assign
                                    </button>
                                </div>
                            </form>
                            @else
                                <i class="text-success fas fa-check"></i>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <a href="{{ route('manajer_operasional.orders.index') }}" class="btn btn-secondary mt-3">
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection
