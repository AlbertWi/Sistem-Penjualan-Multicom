@extends('layouts.app')

@section('content')
<h1>Katalog Produk</h1>

<div class="row">
  @foreach($items as $item)
  <div class="col-md-3">
    <div class="card mb-3">
      <img src="{{ $item->product->image_url ?? '/placeholder.png' }}" class="card-img-top" alt="">
      <div class="card-body">
        <h5 class="card-title">{{ $item->product->brand->name ?? '' }} {{ $item->product->name ?? '' }}</h5>
        <p class="card-text">Harga: Rp {{ number_format($item->ecom_price,0,',','.') }}</p>
        <a href="{{ route('catalog.show', $item) }}" class="btn btn-sm btn-primary">Lihat</a>
      </div>
    </div>
  </div>
  @endforeach
</div>

{{ $items->links() }}
@endsection
