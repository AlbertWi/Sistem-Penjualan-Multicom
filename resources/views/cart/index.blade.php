@extends('layouts.catalog')

@section('title', 'Cart')

@section('content')
<div class="container mt-4">
    <h2>Shopping Cart</h2>

    @if(empty($cart))
        <p>Keranjang kosong.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($cart as $item)
                    @php $total += $item['price'] * $item['qty']; @endphp
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['qty'] }}</td>
                        <td>Rp {{ number_format($item['price'],0,',','.') }}</td>
                        <td>Rp {{ number_format($item['price'] * $item['qty'],0,',','.') }}</td>
                        <td>
                            <form method="POST"
                                  action="{{ route('cart.remove', $item['product_id']) }}">
                                @csrf
                                <button class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h4>Total: Rp {{ number_format($total,0,',','.') }}</h4>

        <a href="{{ route('checkout.index') }}" class="btn btn-primary">
            Checkout
        </a>
    @endif
</div>
@endsection
