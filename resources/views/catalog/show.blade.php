@extends('layouts.catalog')

@section('content')
<div class="product-detail-wrapper">
    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
        <div class="container">
            <nav class="breadcrumb-nav">
                <a href="{{ route('catalog.index') }}" class="breadcrumb-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Catalog
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">{{ $inventoryItem->product->name ?? 'Product Detail' }}</span>
            </nav>
        </div>
    </div>

    <!-- Product Detail -->
    <div class="container">
        <div class="product-detail-grid">
            <!-- Image Gallery -->
            <div class="product-gallery">
                <div class="main-image-wrapper">
                    @if($inventoryItem->product->foto)
                        <img src="{{ asset('storage/' . $inventoryItem->product->foto) }}" 
                             alt="{{ $inventoryItem->product->name }}"
                             class="main-product-image"
                             id="mainImage">
                    @else
                        <img src="{{ asset('placeholder.png') }}" 
                             alt="No image"
                             class="main-product-image"
                             id="mainImage">
                    @endif
                    <div class="image-badge">NEW</div>
                </div>

                <!-- Thumbnail Gallery (if you have multiple images) -->
                <div class="thumbnail-gallery">
                    @if($inventoryItem->product->foto)
                        <div class="thumbnail-item active">
                            <img src="{{ asset('storage/' . $inventoryItem->product->foto) }}" alt="Thumbnail 1">
                        </div>
                        <div class="thumbnail-item">
                            <img src="{{ asset('storage/' . $inventoryItem->product->foto) }}" alt="Thumbnail 2">
                        </div>
                        <div class="thumbnail-item">
                            <img src="{{ asset('storage/' . $inventoryItem->product->foto) }}" alt="Thumbnail 3">
                        </div>
                        <div class="thumbnail-item">
                            <img src="{{ asset('storage/' . $inventoryItem->product->foto) }}" alt="Thumbnail 4">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info-section">
                <div class="product-header">
                    <span class="product-category">Electronics</span>
                    <h1 class="product-title">{{ $inventoryItem->product->name ?? 'Product Name' }}</h1>
                    
                    <div class="product-rating-detail">
                        <div class="stars-large">
                            <svg class="star-filled" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <svg class="star-filled" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <svg class="star-filled" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <svg class="star-filled" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            <svg class="star-empty" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <span class="rating-text">4.0 (128 reviews)</span>
                    </div>
                </div>

                <div class="price-section">
                    <div class="price-main">
                        <span class="current-price">Rp {{ number_format($inventoryItem->ecom_price, 0, ',', '.') }}</span>
                        <span class="original-price">Rp {{ number_format($inventoryItem->ecom_price * 1.2, 0, ',', '.') }}</span>
                    </div>
                    <span class="discount-badge">20% OFF</span>
                </div>

                <div class="product-description">
                    <h3 class="section-title">Description</h3>
                    <p class="description-text">
                        {{ $inventoryItem->product->description ?? 'Experience the perfect blend of style and performance with this premium product. Crafted with attention to detail and designed for modern living, this item combines cutting-edge technology with elegant aesthetics.' }}
                    </p>
                </div>

                <div class="product-specs">
                    <h3 class="section-title">Specifications</h3>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Brand</span>
                            <span class="spec-value">{{ $inventoryItem->product->brand ?? 'Premium Brand' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">SKU</span>
                            <span class="spec-value">{{ $inventoryItem->product->sku ?? 'PRD-001' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Availability</span>
                            <span class="spec-value stock-available">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                                In Stock
                            </span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Category</span>
                            <span class="spec-value">{{ $inventoryItem->product->category ?? 'Electronics' }}</span>
                        </div>
                    </div>
                </div>

                <div class="product-actions">
                    <div class="quantity-selector">
                        <button class="qty-btn" onclick="decreaseQty()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </button>
                        <input type="number" class="qty-input" value="1" min="1" id="quantity">
                        <button class="qty-btn" onclick="increaseQty()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                        </button>
                    </div>

                    <button class="btn-add-cart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        Add to Cart
                    </button>

                    <button class="btn-wishlist" title="Add to Wishlist">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </button>
                </div>

                <div class="product-features">
                    <div class="feature-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <div class="feature-text">
                            <strong>Fast Delivery</strong>
                            <span>Free shipping on orders over Rp 500.000</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <div class="feature-text">
                            <strong>7 Days Return</strong>
                            <span>Money back guarantee</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <div class="feature-text">
                            <strong>Secure Payment</strong>
                            <span>100% secure transactions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <div class="related-products-section">
            <h2 class="related-title">You May Also Like</h2>
            <div class="related-products-grid">
                @for($i = 0; $i < 4; $i++)
                <div class="related-product-card">
                    <div class="related-image">
                        @if($inventoryItem->product->foto)
                            <img src="{{ asset('storage/' . $inventoryItem->product->foto) }}" alt="Related Product">
                        @else
                            <img src="{{ asset('placeholder.png') }}" alt="Related Product">
                        @endif
                    </div>
                    <div class="related-info">
                        <h4 class="related-name">Similar Product {{ $i + 1 }}</h4>
                        <span class="related-price">Rp {{ number_format(rand(500000, 2000000), 0, ',', '.') }}</span>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script>
// Quantity Controls
function increaseQty() {
    const input = document.getElementById('quantity');
    input.value = parseInt(input.value) + 1;
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

// Thumbnail Gallery
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    const mainImage = document.getElementById('mainImage');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const imgSrc = this.querySelector('img').src;
            mainImage.src = imgSrc;
        });
    });
});
</script>
@endpush
@endsection