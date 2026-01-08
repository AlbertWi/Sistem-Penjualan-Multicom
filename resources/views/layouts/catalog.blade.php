<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #95a5a6;
            --success: #2ecc71;
            --warning: #f39c12;
            --light-gray: #f8f9fa;
            --border-color: #eaeaea;
        }

        body {
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header */
        .site-header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-top {
            background-color: var(--primary);
            color: white;
            padding: 10px 0;
            font-size: 14px;
        }

        .header-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-top-links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            transition: color 0.3s;
        }

        .header-top-links a:hover {
            color: var(--secondary);
        }

        .header-main {
            padding: 15px 0;
        }

        .header-main .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .logo i {
            color: var(--accent);
            margin-right: 10px;
        }

        .search-box {
            flex: 1;
            max-width: 500px;
            margin: 0 30px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid var(--border-color);
            border-radius: 30px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--secondary);
        }

        .search-box button {
            position: absolute;
            right: 5px;
            top: 5px;
            background-color: var(--secondary);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 9px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-box button:hover {
            background-color: var(--primary);
        }

        .header-icons {
            display: flex;
            align-items: center;
        }

        .header-icons a {
            color: var(--dark);
            font-size: 20px;
            margin-left: 20px;
            position: relative;
            text-decoration: none;
            transition: color 0.3s;
        }

        .header-icons a:hover {
            color: var(--secondary);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--accent);
            color: white;
            font-size: 12px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Navigation */
        .site-nav {
            background-color: var(--primary);
            color: white;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            position: relative;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
            transition: background-color 0.3s;
        }

        .nav-links a:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s;
            z-index: 10;
            border-radius: 5px;
        }

        .dropdown-menu a {
            color: var(--dark);
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
        }

        .dropdown-menu a:hover {
            background-color: var(--light-gray);
        }

        .dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .nav-promo {
            background-color: var(--accent);
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 14px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px 0;
        }

        /* Auth Forms */
        .auth-container {
            max-width: 400px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .auth-container h3 {
            margin-bottom: 25px;
            color: var(--primary);
            text-align: center;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            text-align: center;
        }

        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary);
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
        }

        .btn-success:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .mt-3 {
            margin-top: 15px;
        }

        .mt-5 {
            margin-top: 40px;
        }

        .auth-link {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        /* Catalog Styles */
        .catalog-wrapper {
            width: 100%;
        }

        .catalog-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 80px 0;
            margin-bottom: 40px;
            text-align: center;
        }

        .hero-title {
            font-size: 48px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .hero-subtitle {
            font-size: 20px;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .filter-bar {
            background: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .filter-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .result-count {
            color: var(--gray);
            font-size: 16px;
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: 5px;
            background: white;
            font-size: 16px;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--secondary);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .product-image-wrapper {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--accent);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .quick-view-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: var(--primary);
            padding: 12px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s;
        }

        .quick-view-btn:hover {
            transform: scale(1.05);
        }

        .product-info {
            padding: 20px;
        }

        .product-name {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--dark);
            font-weight: 600;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
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
            color: var(--gray);
            font-size: 14px;
        }

        .product-price-wrapper {
            margin-bottom: 20px;
        }

        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent);
        }

        .product-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--secondary);
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .product-btn:hover {
            background: var(--primary);
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state svg {
            color: var(--gray);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--gray);
            font-size: 18px;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .pagination {
            display: flex;
            list-style: none;
            gap: 10px;
        }

        .pagination li {
            display: inline-block;
        }

        .pagination a, .pagination span {
            display: block;
            padding: 8px 16px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: var(--secondary);
            color: white;
            border-color: var(--secondary);
        }

        .pagination .active span {
            background: var(--secondary);
            color: white;
            border-color: var(--secondary);
        }

        /* Product Detail Styles */
        .product-detail-wrapper {
            width: 100%;
        }

        .breadcrumb-section {
            background: var(--light-gray);
            padding: 20px 0;
            margin-bottom: 40px;
        }

        .breadcrumb-nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .breadcrumb-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
        }

        .breadcrumb-link:hover {
            text-decoration: underline;
        }

        .breadcrumb-separator {
            color: var(--gray);
        }

        .breadcrumb-current {
            color: var(--dark);
            font-weight: 600;
        }

        .product-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 80px;
        }

        @media (max-width: 992px) {
            .product-detail-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
        }

        .product-gallery {
            position: sticky;
            top: 100px;
        }

        .main-image-wrapper {
            position: relative;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .main-product-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            padding: 20px;
            background: white;
        }

        .image-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--accent);
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 14px;
        }

        .thumbnail-gallery {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .thumbnail-item {
            flex: 0 0 auto;
            width: 80px;
            height: 80px;
            border: 2px solid transparent;
            border-radius: 5px;
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .thumbnail-item.active {
            border-color: var(--secondary);
        }

        .thumbnail-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info-section {
            padding: 20px 0;
        }

        .product-header {
            margin-bottom: 25px;
        }

        .product-category {
            display: inline-block;
            background: var(--light-gray);
            color: var(--secondary);
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .product-title {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .product-rating-detail {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stars-large {
            display: flex;
            gap: 5px;
        }

        .stars-large svg {
            width: 24px;
            height: 24px;
        }

        .star-filled {
            color: #ffc107;
        }

        .star-empty {
            color: #e0e0e0;
        }

        .rating-text {
            color: var(--gray);
            font-size: 16px;
        }

        .price-section {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px 0;
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .price-main {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .current-price {
            font-size: 36px;
            font-weight: 700;
            color: var(--accent);
        }

        .original-price {
            font-size: 24px;
            color: var(--gray);
            text-decoration: line-through;
        }

        .discount-badge {
            background: var(--accent);
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-weight: 600;
        }

        .section-title {
            font-size: 20px;
            color: var(--dark);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .description-text {
            color: var(--gray);
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .product-specs {
            margin-bottom: 30px;
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .spec-label {
            color: var(--gray);
        }

        .spec-value {
            color: var(--dark);
            font-weight: 600;
        }

        .stock-available {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--success);
            font-weight: 600;
        }

        .product-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            align-items: center;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            border: 2px solid var(--border-color);
            border-radius: 5px;
            overflow: hidden;
        }

        .qty-btn {
            width: 40px;
            height: 40px;
            background: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .qty-btn:hover {
            background: var(--light-gray);
        }

        .qty-input {
            width: 60px;
            height: 40px;
            border: none;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .qty-input:focus {
            outline: none;
        }

        .btn-add-cart {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: var(--secondary);
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-add-cart:hover {
            background: var(--primary);
        }

        .btn-wishlist {
            width: 40px;
            height: 40px;
            background: var(--light-gray);
            border: none;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-wishlist:hover {
            background: var(--border-color);
        }

        .product-features {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 10px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid white;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-item svg {
            color: var(--secondary);
            flex-shrink: 0;
        }

        .feature-text {
            display: flex;
            flex-direction: column;
        }

        .feature-text strong {
            color: var(--dark);
            margin-bottom: 5px;
        }

        .feature-text span {
            color: var(--gray);
            font-size: 14px;
        }

        .related-products-section {
            margin-top: 80px;
            padding-top: 40px;
            border-top: 1px solid var(--border-color);
        }

        .related-title {
            font-size: 28px;
            color: var(--dark);
            margin-bottom: 30px;
            text-align: center;
        }

        .related-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
        }

        .related-product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .related-product-card:hover {
            transform: translateY(-5px);
        }

        .related-image {
            height: 200px;
            overflow: hidden;
        }

        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .related-product-card:hover .related-image img {
            transform: scale(1.1);
        }

        .related-info {
            padding: 20px;
        }

        .related-name {
            font-size: 16px;
            color: var(--dark);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .related-price {
            font-size: 18px;
            color: var(--accent);
            font-weight: 700;
        }

        /* Footer */
        .site-footer {
            background-color: var(--primary);
            color: white;
            margin-top: auto;
        }

        .footer-content {
            padding: 60px 0 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer-col h3 {
            font-size: 20px;
            margin-bottom: 25px;
            position: relative;
        }

        .footer-col h3:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--secondary);
        }

        .footer-col p {
            margin-bottom: 20px;
            color: #bbb;
            line-height: 1.8;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .social-links a:hover {
            background-color: var(--secondary);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #bbb;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: var(--secondary);
        }

        .newsletter-form {
            display: flex;
            margin-top: 20px;
        }

        .newsletter-form input {
            flex-grow: 1;
            padding: 12px 15px;
            border: none;
            border-radius: 5px 0 0 5px;
            font-size: 16px;
        }

        .newsletter-form button {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .newsletter-form button:hover {
            background-color: var(--primary);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 20px 0;
            text-align: center;
            color: #bbb;
            font-size: 14px;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .hero-title {
                font-size: 36px;
            }
            
            .hero-subtitle {
                font-size: 18px;
            }
            
            .product-detail-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .product-gallery {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .header-main .container {
                flex-wrap: wrap;
            }
            
            .search-box {
                order: 3;
                max-width: 100%;
                margin: 15px 0 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background-color: var(--primary);
                flex-direction: column;
                z-index: 100;
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .specs-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .header-top .container {
                flex-direction: column;
                gap: 10px;
            }
            
            .header-top-links a {
                margin: 0 10px;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .product-detail-grid {
                gap: 20px;
            }
            
            .product-actions {
                flex-wrap: wrap;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }
            
            .auth-container {
                margin: 20px auto;
                padding: 20px;
            }
            
            .hero-title {
                font-size: 28px;
            }
            
            .hero-subtitle {
                font-size: 16px;
            }
            .related-products-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 30px;
                justify-items: center;
            }

            .related-product-card {
                width: 100%;
                max-width: 240px;
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 5px 15px rgba(0,0,0,.08);
                transition: .25s ease;
                text-align: center;
            }

            .related-product-card:hover {
                transform: translateY(-6px);
            }

            .related-image {
                height: 220px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .related-image img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                padding: 10px;
            }

            .related-info {
                padding: 15px;
            }

            .related-name {
                font-size: 15px;
                font-weight: 600;
                margin-bottom: 6px;
            }

            .related-price {
                color: var(--accent);
                font-weight: 700;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-links">
                    <a href="#">Bantuan</a>
                    @auth
                        <a href="#">{{ Auth::user()->name }}</a>
                        <a href="{{ route('customer.logout') }}"
                        onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @else
                        <a href="{{ route('customer.login') }}">Masuk / Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
        <div class="header-main">
            <div class="container">
                <a href="{{ route('catalog.index') }}" class="logo">
                    <i class="fas fa-shopping-bag"></i>
                    <span>{{ config('app.name') }}</span>
                </a>
                <form method="GET"
                    action="{{ route('catalog.index') }}"
                    class="search-box">
                    <input type="text"
                        name="q"
                        placeholder="Cari produk atau brand..."
                        value="{{ request('q') }}">
                    <button type="submit">
                        <i class="fass fa-search"></i>
                    </button>
                </form>
                <div class="header-icons">
                    @if(auth('customer')->check())
                        <a href="{{ route('customer.profile') }}">
                            <i class="far fa-user"></i>
                            {{ auth('customer')->user()->name }}
                        </a>

                        <a href="{{ route('customer.logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>

                        <form id="logout-form"
                            action="{{ route('customer.logout') }}"
                            method="POST"
                            class="d-none">
                            @csrf
                        </form>
                    @else
                        <a href="{{ route('customer.login') }}">Masuk / Daftar</a>
                    @endif

                    <!-- Cart icon di luar conditional -->
                    <a href="{{ route('cart.index') }}" id="cartIcon" style="margin-left: 20px; position: relative;">
                        <i class="fas fa-shopping-cart"></i>
                        @php
                            $cartCount = collect(session('cart', []))->sum('qty');
                        @endphp
                        @if($cartCount > 0)
                            <span class="cart-count" id="cartCountBadge">{{ $cartCount }}</span>
                        @else
                            <span class="cart-count" id="cartCountBadge" style="display: none;">0</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
            <nav class="site-nav">
            <div class="container navbar">
                <button class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="nav-links">
                    <li>
                        <a href="{{ route('catalog.index') }}">
                            <i class="fas fa-home"></i> Beranda
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>
    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn')?.addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const navLinks = document.querySelector('.nav-links');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (navLinks?.classList.contains('active') && 
                !navLinks.contains(event.target) && 
                !menuBtn?.contains(event.target)) {
                navLinks.classList.remove('active');
            }
        });

        // Newsletter form submission
        document.querySelector('.newsletter-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            const button = this.querySelector('button');
            
            if (emailInput.value) {
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.style.backgroundColor = "#2ecc71";
                emailInput.value = "";
                
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-paper-plane"></i>';
                    button.style.backgroundColor = "";
                }, 2000);
            }
        });

        // Thumbnail Gallery Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnails = document.querySelectorAll('.thumbnail-item');
            const mainImage = document.getElementById('mainImage');
            
            if (thumbnails.length > 0 && mainImage) {
                thumbnails.forEach(thumbnail => {
                    thumbnail.addEventListener('click', function() {
                        thumbnails.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        const imgSrc = this.querySelector('img').src;
                        mainImage.src = imgSrc;
                    });
                });
            }
        });
    </script>
    
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>