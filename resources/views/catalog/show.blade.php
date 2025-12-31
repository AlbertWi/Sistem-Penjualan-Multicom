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
                    @auth('customer')
                        {{-- Tampilkan error/success messages --}}
                        @if(session('error'))
                            <div class="alert alert-danger mb-3">
                                {{ session('error') }}
                            </div>
                        @endif
                        
                        @if(session('success'))
                            <div class="alert alert-success mb-3">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="qty" id="qtyInput" value="1">
                            
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <div class="quantity-selector">
                                    <button class="qty-btn" type="button" onclick="decreaseQty()">−</button>
                                    <input
                                        type="number"
                                        class="qty-input"
                                        value="1"
                                        min="1"
                                        max="{{ $stock }}"
                                        id="quantity"
                                        onchange="syncQty()"
                                    >
                                    <button class="qty-btn" type="button" onclick="increaseQty()">+</button>
                                </div>
                                
                                <button type="submit" class="btn-add-cart" id="submitBtn">
                                    <i class="fas fa-shopping-cart"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Untuk guest --}}
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <div class="quantity-selector" style="opacity: 0.5;">
                                <button class="qty-btn" type="button" disabled>−</button>
                                <input
                                    type="number"
                                    class="qty-input"
                                    value="1"
                                    disabled
                                >
                                <button class="qty-btn" type="button" disabled>+</button>
                            </div>
                            
                            <a href="{{ route('ecom.login') }}" class="btn-add-cart">
                                <i class="fas fa-sign-in-alt"></i>
                                Login untuk Belanja
                            </a>
                        </div>
                        
                        <p class="text-muted small mt-2">
                            <a href="{{ route('ecom.register') }}">Daftar akun</a> untuk mulai berbelanja
                        </p>
                    @endauth
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
// Pastikan fungsi global untuk menghindari conflict
window.increaseQty = function() {
    console.log('increaseQty called');
    let qty = document.getElementById('quantity');
    let max = parseInt(qty.getAttribute('max'));
    
    if (parseInt(qty.value) < max) {
        qty.value = parseInt(qty.value) + 1;
        window.syncQty();
    }
}

window.decreaseQty = function() {
    console.log('decreaseQty called');
    let qty = document.getElementById('quantity');
    
    if (parseInt(qty.value) > 1) {
        qty.value = parseInt(qty.value) - 1;
        window.syncQty();
    }
}

window.syncQty = function() {
    const qtyValue = document.getElementById('quantity').value;
    document.getElementById('qtyInput').value = qtyValue;
    console.log('Quantity synced to hidden input:', qtyValue);
}

// Form submit handler dengan lebih robust
document.addEventListener('DOMContentLoaded', function() {
    console.log('Product detail page loaded');
    
    const form = document.getElementById('addToCartForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (!form) {
        console.error('Form not found! Check if user is logged in.');
        return;
    }
    
    console.log('Form found:', form);
    console.log('Submit button:', submitBtn);
    
    // Sync quantity awal
    window.syncQty();
    
    // Handle form submit
    form.addEventListener('submit', function(e) {
        console.log('=== FORM SUBMIT TRIGGERED ===');
        console.log('Action:', this.action);
        console.log('Method:', this.method);
        console.log('Product ID:', this.querySelector('[name="product_id"]').value);
        console.log('Quantity:', this.querySelector('[name="qty"]').value);
        console.log('CSRF Token exists:', !!this.querySelector('[name="_token"]'));
        
        // Tampilkan loading state
        if (submitBtn) {
            const originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;
            
            // Restore setelah 3 detik (fallback jika redirect gagal)
            setTimeout(() => {
                submitBtn.innerHTML = originalHtml;
                submitBtn.disabled = false;
            }, 3000);
        }
        
        // Biarkan form submit normal
        // Tidak ada e.preventDefault()
    });
    
    // Tambahkan juga click handler untuk button
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            console.log('Submit button clicked directly');
            console.log('Button type:', this.type);
        });
    }
    
    // Quantity input listeners
    const qtyInput = document.getElementById('quantity');
    if (qtyInput) {
        qtyInput.addEventListener('input', window.syncQty);
        qtyInput.addEventListener('change', window.syncQty);
    }
    
    // Quantity buttons
    const qtyButtons = document.querySelectorAll('.qty-btn');
    qtyButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            console.log('Qty button clicked:', this.textContent);
        });
    });
});
</script>
@endpush
