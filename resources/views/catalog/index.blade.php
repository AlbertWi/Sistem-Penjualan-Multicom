@extends('layouts.catalog')

@section('title', config('app.name') . ' - Product Catalog')

@section('content')
<div class="catalog-wrapper">

    {{-- Hero Section --}}
    <section class="catalog-hero">
        <div class="container">
            <div class="hero-content">
                <p class="hero-subtitle">Discover our collection of premium products</p>
            </div>
        </div>
    </section>

    {{-- Filter Bar --}}
    <section class="filter-bar">
        <div class="container">
            <div class="filter-content">
                <div class="result-count">
                    Showing {{ $products->count() }} of {{ $products->total() }} products
                </div>
                <select class="filter-select">
                    <option>Sort by: Featured</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Newest First</option>
                </select>
            </div>
        </div>
    </section>

    {{-- Product Grid --}}
    <section class="container">
        <div class="product-grid">

            @forelse($products as $product)
            <div class="product-card">

                {{-- Image --}}
                <div class="product-image-wrapper">
                    <span class="product-badge">NEW</span>

                    @php
                        $image = $product->images->first();
                    @endphp

                    @if($image)
                        <img src="{{ asset('storage/' . $image->file_path) }}"
                             class="product-image"
                             alt="{{ $product->name }}">
                    @else
                        <img src="https://via.placeholder.com/400"
                             class="product-image"
                             alt="{{ $product->name }}">
                    @endif

                    <div class="product-overlay">
                        <a href="{{ route('catalog.show', $product) }}" class="quick-view-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            Quick View
                        </a>
                    </div>
                </div>

                {{-- Info --}}
                <div class="product-info">
                    <h3 class="product-name">{{ $product->name }}</h3>

                    {{-- Rating dummy --}}
                    <div class="product-rating">
                        <div class="stars">
                            @for($i = 1; $i <= 5; $i++)
                                <svg width="16" height="16" viewBox="0 0 24 24"
                                     fill="{{ $i <= 4 ? '#ffc107' : 'none' }}"
                                     stroke="{{ $i <= 4 ? '#ffc107' : '#e0e0e0' }}"
                                     stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27
                                                     17 14.14 18.18 21.02
                                                     12 17.77 5.82 21.02
                                                     7 14.14 2 9.27
                                                     8.91 8.26 12 2"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="review-count">(24)</span>
                    </div>

                    {{-- Price --}}
                    <div class="product-price-wrapper">
                        <div class="product-price">
                            Rp {{ number_format($product->ecomSetting->ecom_price, 0, ',', '.') }}
                        </div>
                        <small class="text-muted">
                            Stok tersedia: {{ $product->stock_count }}
                        </small>
                    </div>

                    {{-- Button --}}
                    <a href="{{ route('catalog.show', $product) }}" class="product-btn">
                        View Details
                        <svg width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>

            </div>
            @empty
            <div class="empty-state">
                <svg width="120" height="120" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <h3>No Products Found</h3>
                <p>Sorry, we couldn't find any products matching your criteria.</p>
            </div>
            @endforelse

        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="pagination-wrapper">
            {{ $products->links() }}
        </div>
        @endif
    </section>

</div>
@endsection
