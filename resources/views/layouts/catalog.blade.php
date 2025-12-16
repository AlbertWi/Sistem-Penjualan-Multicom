<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', config('app.name') . ' - Catalog')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ===== HEADER / NAVBAR ===== */
        .catalog-header {
            background: white;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #667eea;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .header-logo svg {
            width: 32px;
            height: 32px;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .nav-link {
            color: #666;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #667eea;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: #667eea;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link.active {
            color: #667eea;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-login {
            padding: 10px 24px;
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-register {
            padding: 10px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .user-menu {
            position: relative;
        }

        .user-button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-button:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .user-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .dropdown-item:first-child {
            border-radius: 12px 12px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 12px 12px;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
            color: #667eea;
        }

        .dropdown-divider {
            height: 1px;
            background: #e0e0e0;
            margin: 4px 0;
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
        }

        .mobile-menu-toggle svg {
            width: 24px;
            height: 24px;
            stroke: #333;
        }

        /* ===== FLASH MESSAGES ===== */
        .flash-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
        }

        .flash-message {
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
            backdrop-filter: blur(10px);
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .flash-message.success {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
            color: white;
        }

        .flash-message.error {
            background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
            color: white;
        }

        .flash-message.warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
        }

        .flash-message.info {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            color: white;
        }

        .flash-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }

        .flash-close {
            margin-left: auto;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.2s ease;
        }

        .flash-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        /* ===== CATALOG STYLES ===== */
        .catalog-wrapper {
            background: #f8f9fa;
            min-height: 100vh;
        }

        /* Hero Section */
        .catalog-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 0;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .catalog-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .catalog-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .hero-content {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 800;
            color: white;
            margin-bottom: 12px;
            letter-spacing: -1px;
        }

        .hero-subtitle {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 300;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            padding: 20px 0;
            margin-bottom: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .filter-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .result-count {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .filter-select {
            padding: 10px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:hover {
            border-color: #667eea;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
        }

        .product-image-wrapper {
            position: relative;
            width: 100%;
            padding-top: 100%;
            overflow: hidden;
            background: #f5f5f5;
        }

        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .product-card:hover .product-image {
            transform: scale(1.08);
        }

        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            z-index: 2;
            box-shadow: 0 4px 12px rgba(238, 90, 111, 0.3);
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .quick-view-btn {
            background: white;
            color: #333;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            transform: translateY(10px);
        }

        .product-card:hover .quick-view-btn {
            transform: translateY(0);
        }

        .quick-view-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .product-info {
            padding: 20px;
        }

        .product-name {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 44px;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }

        .stars {
            display: flex;
            gap: 2px;
        }

        .star-filled {
            color: #ffc107;
        }

        .star-empty {
            color: #e0e0e0;
        }

        .review-count {
            font-size: 13px;
            color: #757575;
            font-weight: 500;
        }

        .product-price-wrapper {
            margin-bottom: 16px;
        }

        .product-price {
            font-size: 22px;
            font-weight: 700;
            color: #667eea;
            letter-spacing: -0.5px;
        }

        .product-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .product-btn svg {
            transition: transform 0.3s ease;
        }

        .product-btn:hover svg {
            transform: translateX(4px);
        }

        /* Empty State */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state svg {
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 16px;
            color: #757575;
        }

        /* Pagination */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            padding-bottom: 60px;
        }

        .pagination {
            display: flex;
            gap: 8px;
            list-style: none;
            padding: 0;
        }

        .pagination li a,
        .pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination li a {
            background: white;
            color: #667eea;
            border: 2px solid #e0e0e0;
        }

        .pagination li a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .pagination li.active span {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 2px solid #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .pagination li.disabled span {
            background: #f5f5f5;
            color: #ccc;
            border: 2px solid #e0e0e0;
            cursor: not-allowed;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .header-nav {
                display: none;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .header-actions {
                gap: 8px;
            }

            .btn-login,
            .btn-register {
                padding: 8px 16px;
                font-size: 13px;
            }

            .user-name {
                display: none;
            }

            .flash-messages {
                left: 20px;
                right: 20px;
                max-width: none;
            }

            .hero-title {
                font-size: 32px;
            }
            
            .hero-subtitle {
                font-size: 16px;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 16px;
            }
            
            .filter-content {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }
            
            .filter-select {
                width: 100%;
            }
            
            .product-info {
                padding: 16px;
            }
            
            .product-name {
                font-size: 14px;
                min-height: 40px;
            }
            
            .product-price {
                font-size: 18px;
            }
        }

        @media (min-width: 1400px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    {{-- Header / Navbar --}}
    <header class="catalog-header">
        <div class="container">
            <div class="header-content">
                <a href="{{ route('catalog.index') }}" class="header-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <span>ShopName</span>
                </a>

                <nav class="header-nav">
                    <a href="{{ route('catalog.index') }}" class="nav-link {{ Request::routeIs('catalog.index') ? 'active' : '' }}">Catalog</a>
                    <a href="#" class="nav-link">Categories</a>
                    <a href="#" class="nav-link">Deals</a>
                    <a href="#" class="nav-link">About</a>
                </nav>

                <div class="header-actions">
                    @guest
                        <a href="{{ route('login') }}" class="btn-login">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                <polyline points="10 17 15 12 10 7"/>
                                <line x1="15" y1="12" x2="3" y2="12"/>
                            </svg>
                            Login
                        </a>
                        <a href="{{ route('ecom.register') }}" class="btn-register">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="8.5" cy="7" r="4"/>
                                <line x1="20" y1="8" x2="20" y2="14"/>
                                <line x1="23" y1="11" x2="17" y2="11"/>
                            </svg>
                            Register
                        </a>
                    @else
                        <div class="user-menu">
                            <button class="user-button">
                                <div class="user-avatar">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="user-name">{{ Auth::user()->name }}</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>

                            <div class="user-dropdown">
                                <a href="{{ route('dashboard') }}" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="7" height="7"/>
                                        <rect x="14" y="3" width="7" height="7"/>
                                        <rect x="14" y="14" width="7" height="7"/>
                                        <rect x="3" y="14" width="7" height="7"/>
                                    </svg>
                                    Dashboard
                                </a>
                                <a href="#" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    Profile
                                </a>
                                <a href="#" class="dropdown-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="21" r="1"/>
                                        <circle cx="20" cy="21" r="1"/>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                    </svg>
                                    My Orders
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; border: none; background: none; cursor: pointer;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                            <polyline points="16 17 21 12 16 7"/>
                                            <line x1="21" y1="12" x2="9" y2="12"/>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest

                    <button class="mobile-menu-toggle">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Flash Messages --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div class="flash-messages">
        @if(session('success'))
        <div class="flash-message success">
            <svg class="flash-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
            <button class="flash-close" onclick="this.parentElement.remove()">×</button>
        </div>
        @endif

        @if(session('error'))
        <div class="flash-message error">
            <svg class="flash-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span>{{ session('error') }}</span>
            <button class="flash-close" onclick="this.parentElement.remove()">×</button>
        </div>
        @endif

        @if(session('warning'))
        <div class="flash-message warning">
            <svg class="flash-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>{{ session('warning') }}</span>
            <button class="flash-close" onclick="this.parentElement.remove()">×</button>
        </div>
        @endif

        @if(session('info'))
        <div class="flash-message info">
            <svg class="flash-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('info') }}</span>
            <button class="flash-close" onclick="this.parentElement.remove()">×</button>
        </div>
        @endif
    </div>
    @endif

    {{-- Main Content --}}
    @yield('content')

    {{-- Scripts --}}
    <script>
        // Auto close flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.animation = 'slideIn 0.3s ease-out reverse';
                    setTimeout(function() {
                        message.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>