@extends('layouts.catalog')

@section('title', $product->name . ' - ' . config('app.name'))

@section('content')
<div class="product-detail-wrapper">

    {{-- Breadcrumb --}}
    <div class="breadcrumb-section">
        <div class="container">
            <nav class="breadcrumb-nav">
                <a href="{{ route('catalog.index') }}" class="breadcrumb-link">
                    Catalog
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    {{-- Product Detail --}}
    <div class="container">
        <div class="product-detail-grid">

            {{-- Image Gallery --}}
            <div class="product-gallery">
                <div class="main-image-wrapper">
                    @php
                        $mainImage = $product->images->first();
                    @endphp

                    @if($mainImage)
                        <img src="{{ asset('storage/' . $mainImage->file_path) }}"
                             alt="{{ $product->name }}"
                             class="main-product-image"
                             id="mainImage">
                    @else
                        <img src="https://via.placeholder.com/600x400"
                             alt="No image"
                             class="main-product-image"
                             id="mainImage">
                    @endif
                    <div class="image-badge">NEW</div>
                </div>

                {{-- Thumbnails --}}
                <div class="thumbnail-gallery">
                    @foreach($product->images as $img)
                        <div class="thumbnail-item">
                            <img src="{{ asset('storage/' . $img->file_path) }}"
                                 alt="{{ $product->name }}">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Product Info --}}
            <div class="product-info-section">

                <div class="product-header">
                    <span class="product-category">
                        {{ $product->brand->name ?? 'Brand' }}
                    </span>

                    <h1 class="product-title">{{ $product->name }}</h1>
                </div>

                {{-- Price --}}
                <div class="price-section">
                    <div class="price-main">
                        <span class="current-price">
                            Rp {{ number_format($product->ecomSetting->ecom_price, 0, ',', '.') }}
                        </span>
                    </div>
                    <small class="text-muted">
                        Stok tersedia: {{ $stock }}
                    </small>
                </div>
                {{-- Specifications --}}
                    <div class="product-specs">
                        <h3 class="section-title">Specifications</h3>

                        <div class="specs-grid">

                            <div class="spec-item">
                                <span class="spec-label">Brand</span>
                                <span class="spec-value">{{ $product->brand->name ?? '-' }}</span>
                            </div>

                            <div class="spec-item">
                                <span class="spec-label">RAM</span>
                                <span class="spec-value">{{ $product->ram }} GB</span>
                            </div>

                            <div class="spec-item">
                                <span class="spec-label">Storage</span>
                                <span class="spec-value">{{ $product->rom }} GB</span>
                            </div>

                            <div class="spec-item">
                                <span class="spec-label">Battery</span>
                                <span class="spec-value">{{ $product->baterai }} mAh</span>
                            </div>

                            <div class="spec-item">
                                <span class="spec-label">Screen Size</span>
                                <span class="spec-value">{{ $product->ukuran_layar }} inch</span>
                            </div>

                            <div class="spec-item">
                                <span class="spec-label">Camera</span>
                                <span class="spec-value">{{ $product->resolusi_kamera }} MP</span>
                            </div>

                            <div class="spec-item">
                                <span class="spec-label">SIM Slot</span>
                                <span class="spec-value">{{ $product->jumlah_slot_sim }} Slot</span>
                            </div>

                            <div class="spec-item">
                                <span class="spec-label">Warranty</span>
                                <span class="spec-value">{{ $product->masa_garansi }} Bulan</span>
                            </div>

                            <div class="spec-item">
                                <span class="spec-label">Availability</span>
                                <span class="spec-value stock-available">
                                    In Stock
                                </span>
                            </div>

                        </div>
                    </div>


                {{-- Actions --}}
                <div class="product-actions">
                    <div class="quantity-selector">
                        <button class="qty-btn" type="button" onclick="decreaseQty()">−</button>
                        <input type="number" class="qty-input" value="1" min="1" max="{{ $stock }}" id="quantity">
                        <button class="qty-btn" type="button" onclick="increaseQty()">+</button>
                    </div>

                    <form action="{{ route('cart.add', $product) }}"  method="POST">
                        @csrf
                        <input type="hidden" name="qty" id="qtyInput">
                        <button class="btn-add-cart">
                            Add to Cart
                        </button>
                    </form>
                </div>

            </div>
        </div>

        {{-- Related Products (dummy) --}}
        <div class="related-products-section">
            <h2 class="related-title">You May Also Like</h2>
            <div class="related-products-grid">
                @foreach($relatedProducts ?? [] as $rel)
                    <div class="related-product-card">
                        <img src="{{ asset('storage/' . optional($rel->images->first())->file_path) }}">
                        <h4>{{ $rel->name }}</h4>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function increaseQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    if (parseInt(input.value) < max) {
        input.value++;
    }
    syncQty();
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value--;
    }
    syncQty();
}

function syncQty() {
    document.getElementById('qtyInput').value =
        document.getElementById('quantity').value;
}

// initial
syncQty();
</script>
@endpush

