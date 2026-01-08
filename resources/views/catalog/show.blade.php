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
                        {{-- Notification Toast --}}
                        <div id="cartNotification" class="toast align-items-center text-white bg-success border-0 position-fixed top-50 start-50 translate-middle" 
                             role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999; display: none;">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span id="notificationMessage">Produk ditambahkan ke keranjang!</span>
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>

                        {{-- Error Toast --}}
                        <div id="errorNotification" class="toast align-items-center text-white bg-danger border-0 position-fixed top-50 start-50 translate-middle" 
                             role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999; display: none;">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <span id="errorMessage">Terjadi kesalahan!</span>
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>

                        <form id="addToCartForm">
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
                                
                                <button type="button" class="btn-add-cart" id="submitBtn">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span id="btnText">Add to Cart</span>
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
                            
                            <a href="{{ route('customer.login') }}" class="btn-add-cart">
                                <i class="fas fa-sign-in-alt"></i>
                                Login untuk Belanja
                            </a>
                        </div>
                        
                        <p class="text-muted small mt-2">
                            <a href="{{ route('customer.register') }}">Daftar akun</a> untuk mulai berbelanja
                        </p>
                    @endauth
                </div>

            </div>
        </div>

        {{-- Related Products (dummy) --}}
        @if(($relatedProducts ?? collect())->count())
            <section class="related-products-section">
                <h3 class="related-title">You May Also Like</h3>
                <div class="related-products-grid">
                    @foreach($relatedProducts as $product)
                        <div class="related-product-card">
                            <a href="{{ route('catalog.show', $product->id) }}">
                                <div class="related-image">
                                    <img src="{{ asset('storage/' . optional($product->images->first())->file_path) }}">
                                </div>
                                <div class="related-info">
                                    <div class="related-name">{{ $product->name }}</div>
                                    <div class="related-price">
                                        Rp {{ number_format($product->ecomSetting->ecom_price ?? 0) }}
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
/* Animation Styles */
@keyframes cartBounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

@keyframes checkmark {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes floatToCart {
    0% {
        transform: translate(0, 0) scale(1);
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        transform: translate(var(--tx), var(--ty)) scale(0.3);
        opacity: 0;
    }
}

.cart-animation {
    position: fixed;
    width: 40px;
    height: 40px;
    background: var(--secondary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9998;
    pointer-events: none;
    animation: floatToCart 1s ease-in-out forwards;
}

.cart-count-animate {
    animation: cartBounce 0.5s ease-in-out 2;
}

.checkmark-animate {
    animation: checkmark 0.5s ease-in-out;
}

/* Toast Notification */
.toast {
    min-width: 300px;
}
</style>
@endpush

@push('scripts')
<script>
// Quantity Functions
function increaseQty() {
    let qty = document.getElementById('quantity');
    let max = parseInt(qty.getAttribute('max'));
    
    if (parseInt(qty.value) < max) {
        qty.value = parseInt(qty.value) + 1;
        syncQty();
    }
}

function decreaseQty() {
    let qty = document.getElementById('quantity');
    
    if (parseInt(qty.value) > 1) {
        qty.value = parseInt(qty.value) - 1;
        syncQty();
    }
}

function syncQty() {
    const qtyValue = document.getElementById('quantity').value;
    document.getElementById('qtyInput').value = qtyValue;
}

// Create floating animation element
function createFloatingCartAnimation(startX, startY, endX, endY) {
    const floatingElement = document.createElement('div');
    floatingElement.className = 'cart-animation';
    floatingElement.innerHTML = '<i class="fas fa-shopping-cart"></i>';
    
    // Calculate animation path
    const tx = endX - startX;
    const ty = endY - startY;
    floatingElement.style.setProperty('--tx', `${tx}px`);
    floatingElement.style.setProperty('--ty', `${ty}px`);
    
    // Position at start
    floatingElement.style.left = `${startX}px`;
    floatingElement.style.top = `${startY}px`;
    
    document.body.appendChild(floatingElement);
    
    // Remove after animation completes
    setTimeout(() => {
        floatingElement.remove();
    }, 1000);
    
    return floatingElement;
}

// Update cart count in header
function updateCartCount(count) {
    const cartCountElement = document.querySelector('.cart-count');
    const cartIcon = document.querySelector('.header-icons a[href*="cart"]');
    
    if (cartCountElement) {
        cartCountElement.textContent = count;
        cartCountElement.classList.add('cart-count-animate');
        setTimeout(() => {
            cartCountElement.classList.remove('cart-count-animate');
        }, 1000);
    } else if (count > 0) {
        // Create cart count badge if doesn't exist
        const badge = document.createElement('span');
        badge.className = 'cart-count';
        badge.textContent = count;
        cartIcon.appendChild(badge);
        badge.classList.add('cart-count-animate');
    }
}

// AJAX Add to Cart
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addToCartForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const cartNotification = new bootstrap.Toast(document.getElementById('cartNotification'));
    const errorNotification = new bootstrap.Toast(document.getElementById('errorNotification'));
    
    if (!submitBtn || !form) return;
    
    submitBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const originalText = btnText.textContent;
        const originalHTML = submitBtn.innerHTML;
        
        // Get button position for animation
        const btnRect = submitBtn.getBoundingClientRect();
        const startX = btnRect.left + btnRect.width / 2 - 20;
        const startY = btnRect.top + btnRect.height / 2 - 20;
        
        // Get cart icon position (target)
        const cartIcon = document.querySelector('.header-icons a[href*="cart"]');
        const cartRect = cartIcon.getBoundingClientRect();
        const endX = cartRect.left + cartRect.width / 2 - 20;
        const endY = cartRect.top + cartRect.height / 2 - 20;
        
        // Create animation
        createFloatingCartAnimation(startX, startY, endX, endY);
        
        // Show loading state
        submitBtn.disabled = true;
        btnText.textContent = 'Menambahkan...';
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menambahkan...';
        
        try {
            // Prepare form data
            const formData = new FormData(form);
            
            // Send AJAX request
            const response = await fetch('{{ route("customer.cart.add") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                // Success animation
                submitBtn.innerHTML = '<i class="fas fa-check checkmark-animate"></i> Ditambahkan!';
                submitBtn.style.backgroundColor = 'var(--success)';
                
                // Show notification
                document.getElementById('notificationMessage').textContent = data.message || 'Produk ditambahkan ke keranjang!';
                cartNotification.show();
                if (data.requires_login === true) {
                    setTimeout(() => {
                        window.location.href = '{{ route("customer.login") }}';
                    }, 1500);
                } else {
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        submitBtn.innerHTML = originalHTML;
                        submitBtn.style.backgroundColor = '';
                        btnText.textContent = originalText;
                        submitBtn.disabled = false;
                    }, 2000);
                }
                // Update cart count
                if (data.cart_count !== undefined) {
                    updateCartCount(data.cart_count);
                }
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    submitBtn.innerHTML = originalHTML;
                    submitBtn.style.backgroundColor = '';
                    btnText.textContent = originalText;
                    submitBtn.disabled = false;
                }, 2000);
                
            } else {
                // Error handling
                throw new Error(data.message || 'Terjadi kesalahan');
            }
            
        } catch (error) {
            console.error('Add to cart error:', error);
            
            // Show error notification
            document.getElementById('errorMessage').textContent = error.message;
            errorNotification.show();
            
            // Reset button with error state
            submitBtn.innerHTML = '<i class="fas fa-times"></i> Gagal';
            submitBtn.style.backgroundColor = 'var(--accent)';
            
            setTimeout(() => {
                submitBtn.innerHTML = originalHTML;
                submitBtn.style.backgroundColor = '';
                btnText.textContent = originalText;
                submitBtn.disabled = false;
            }, 2000);
        }
    });
    
    // Quantity sync on load
    syncQty();
});
</script>
@endpush