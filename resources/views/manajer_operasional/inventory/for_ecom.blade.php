@extends('layouts.app')

@section('content')
<h1>Inventory (Siap E-Commerce)</h1>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

<table class="table">
<thead>
<tr><th>IMEI</th><th>Produk</th><th>Buy Price</th><th>Harga E-Com</th><th>Posted</th><th>Aksi</th></tr>
</thead>
<tbody>
@foreach($items as $item)
<tr>
  <td>{{ $item->imei }}</td>
  <td>{{ $item->product->brand->name ?? '' }} {{ $item->product->name ?? '' }}</td>
  <td>{{ number_format($item->buy_price,0,',','.') }}</td>
  <td>
    <form action="{{ route('manajer_operasional.inventory.update_price', $item) }}" method="POST" style="display:inline">
      @csrf
      <input name="ecom_price" value="{{ old('ecom_price', $item->ecom_price) }}" style="width:120px">
      <button class="btn btn-sm btn-primary">Simpan</button>
    </form>
  </td>
  <td>{{ $item->is_listed ? 'Ya' : 'Tidak' }}</td>
  <td>
    @if(!$item->is_listed)
      <form action="{{ route('manajer_operasional.inventory.post', $item) }}" method="POST" style="display:inline">
        @csrf
        <button class="btn btn-sm btn-success">Post ke Katalog</button>
      </form>
    @else
      <form action="{{ route('manajer_operasional.inventory.unpost', $item) }}" method="POST" style="display:inline">
        @csrf
        <button class="btn btn-sm btn-warning">Unpost</button>
      </form>
    @endif
  </td>
</tr>
@endforeach
</tbody>
</table>

{{ $items->links() }}
@endsection
