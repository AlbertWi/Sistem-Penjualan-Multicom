@extends('layouts.catalog')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="container">
    <h2 class="my-4">Keranjang Belanja</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(empty($cart))
        <div class="empty-state">
            <i class="fas fa-shopping-cart fa-4x"></i>
            <h3>Keranjang Kosong</h3>
            <p>Belum ada produk di keranjang Anda</p>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary mt-3">
                Mulai Belanja
            </a>
        </div>
    @else
        <div class="cart-items">
            @foreach($cart as $id => $item)
                <div class="cart-item" style="display: flex; gap: 20px; padding: 20px; background: white; margin-bottom: 15px; border-radius: 10px;">
                    <img src="{{ asset('storage/' . $item['image']) }}" 
                         alt="{{ $item['name'] }}" 
                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;">
                    
                    <div style="flex: 1;">
                        <h4>{{ $item['name'] }}</h4>
                        <p>Harga: Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                        <p>Jumlah: {{ $item['qty'] }}</p>
                        <p><strong>Subtotal: Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</strong></p>
                    </div>
                    
                    <form action="{{ route('customer.cart.remove', $id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            @endforeach

            <div style="background: white; padding: 20px; border-radius: 10px; margin-top: 20px;">
                <h3>Total: Rp {{ number_format(collect($cart)->sum(fn($item) => $item['price'] * $item['qty']), 0, ',', '.') }}</h3>
                @if(!empty($cart))
                    <form action="{{ route('customer.checkout.store') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-credit-card"></i> Checkout
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection