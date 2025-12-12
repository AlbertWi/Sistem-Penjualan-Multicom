@extends('layouts.app')
@section('content')
<div class="container py-4">
    <h1 class="mb-4">Katalog Produk</h1>
    <div class="row">
        @forelse($items as $item)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">
                @if($item->product->foto)
                    <img src="{{ asset('storage/' . $item->product->foto) }}" 
                         class="card-img-top" 
                         alt="{{ $item->product->name }}"
                         style="height: 200px; object-fit: cover;">
                @else
                    <img src="{{ asset('placeholder.png') }}" 
                         class="card-img-top" 
                         alt="No image"
                         style="height: 200px; object-fit: cover;">
                @endif
                
                <div class="card-body d-flex flex-column">
                    <h5 class="text-muted mb-2">{{ $item->product->name ?? '' }}</h5>
                    <p class="card-text fw-bold text-primary">
                        Rp {{ number_format($item->ecom_price, 0, ',', '.') }}
                    </p>
                    <a href="{{ route('catalog.show', $item) }}" 
                       class="btn btn-primary btn-sm mt-auto">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                Belum ada produk di katalog.
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $items->links() }}
    </div>
</div>
@endsection