<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link">
        <span class="brand-text font-weight-light">
            @if(Auth::check())
                @if(Auth::user()->role === 'owner')
                    Multicom
                @else
                    {{ Auth::user()->branch->name ?? 'Multicom' }}
                @endif
            @endif
        </span>
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @if(Auth::check() && Auth::user()->role === 'manajer_operasional')
                    <!-- MASTER DATA -->
                    <li class="nav-item has-treeview {{ request()->is('manajer_operasional/brands*', 'manajer_operasional/types*', 'manajer_operasional/products*', 'manajer_operasional/accessories*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('manajer_operasional/brands*', 'manajer_operasional/types*', 'manajer_operasional/products*', 'manajer_operasional/accessories*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>
                                Master Data
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.brands.index') }}" class="nav-link {{ request()->is('manajer_operasional/brands*') ? 'active' : '' }}">
                                    <i class="nav-icon far fa-copyright"></i>
                                    <p>Brands</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.types.index') }}" class="nav-link {{ request()->is('manajer_operasional/types*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-mobile-alt"></i>
                                    <p>Types</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.products.index') }}" class="nav-link {{ request()->is('manajer_operasional/products*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-box"></i>
                                    <p>Products</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.accessories.index') }}" class="nav-link {{ request()->is('manajer_operasional/accessories*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-headphones"></i>
                                    <p>Accessories</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.inventory.editPrice') }}" class="nav-link {{ request()->is('manajer_operasional/inventory/edit-price*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-money-bill-wave text-warning"></i>
                                    <p>Edit Purchase Price</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- CUSTOMERS & SUPPLIERS -->
                    <li class="nav-item has-treeview {{ request()->is('manajer_operasional/customers*', 'manajer_operasional/suppliers*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('manajer_operasional/customers*', 'manajer_operasional/suppliers*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-address-book"></i>
                            <p>
                                Contacts
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.customers.index') }}" class="nav-link {{ request()->is('manajer_operasional/customers*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-friends"></i>
                                    <p>Customers</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.suppliers.index') }}" class="nav-link {{ request()->is('manajer_operasional/suppliers*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-truck"></i>
                                    <p>Suppliers</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- PURCHASES -->
                    <li class="nav-item">
                        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->is('purchases*') && !request()->is('owner/purchases*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Purchases</p>
                        </a>
                    </li>

                    <!-- STOCK MANAGEMENT -->
                    <li class="nav-item has-treeview {{ request()->is('stock-transfers*', 'stok-cabang*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('stock-transfers*', 'stok-cabang*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-warehouse"></i>
                            <p>
                                Stock Management
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('stock-transfers.index') }}" class="nav-link {{ request()->is('stock-transfers*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-exchange-alt"></i>
                                    <p>Stock Transfers</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('stok-cabang') }}" class="nav-link {{ request()->is('stok-cabang*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-boxes"></i>
                                    <p>Branch Stock</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- E-COMMERCE MANAGEMENT -->
                    <li class="nav-item has-treeview {{ request()->is('manajer_operasional/ecom*', 'manajer_operasional/orders*', 'manajer_operasional/inventory*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('manajer_operasional/ecom*', 'manajer_operasional/orders*', 'manajer_operasional/inventory*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-store"></i>
                            <p>
                                E-Commerce
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <!-- Products for E-commerce -->
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.inventory.for_ecom') }}" class="nav-link {{ request()->is('manajer_operasional/inventory/for_ecom*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shopping-cart"></i>
                                    <p>E-commerce Products</p>
                                </a>
                            </li>

                            <!-- Online Orders -->
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.orders.index') }}" class="nav-link {{ request()->is('manajer_operasional/orders*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shopping-bag"></i>
                                    <p>Online Orders</p>
                                    @php
                                        $pendingOrdersCount = \App\Models\Order::where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingOrdersCount > 0)
                                        <span class="badge badge-danger right">{{ $pendingOrdersCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <!-- Manage Listings -->
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.ecom.listings') }}" class="nav-link {{ request()->is('manajer_operasional/ecom/listings*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tags"></i>
                                    <p>Posting E-Commerce</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @elseif(Auth::check() && Auth::user()->role === 'kepala_toko')
                    <!-- KEPALA TOKO MENU -->
                    <li class="nav-item">
                        <a href="{{ route('sales.index') }}" class="nav-link {{ request()->is('sales*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p>Sales</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('stock-transfers.index') }}" class="nav-link {{ request()->is('stock-transfers*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-exchange-alt"></i>
                            <p>Stock Transfers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->is('purchases*') && !request()->is('owner/purchases*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Purchases</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('stok-cabang') }}" class="nav-link {{ request()->is('stok-cabang*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-warehouse"></i>
                            <p>Branch Stock</p>
                        </a>
                    </li>

                @elseif(Auth::check() && Auth::user()->role === 'owner')
                    <!-- OWNER MENU -->
                    <li class="nav-item">
                        <a href="{{ route('stok-cabang') }}" class="nav-link {{ request()->is('stok-cabang*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>Stock Overview</p>
                        </a>
                    </li>

                    <li class="nav-item has-treeview {{ request()->is('owner/branches*', 'owner/users*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('owner/branches*', 'owner/users*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>
                                Management
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('owner.branches.index') }}" class="nav-link {{ request()->is('owner/branches*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-store"></i>
                                    <p>Branch Management</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.users.index') }}" class="nav-link {{ request()->is('owner/users*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>User Management</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ request()->is('owner/sales*', 'owner/purchases*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('owner/sales*', 'owner/purchases*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Payment Settlements
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('owner.sales.index') }}" class="nav-link {{ request()->is('owner/sales*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cash-register"></i>
                                    <p>Sales Settlements</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.purchases.index') }}" class="nav-link {{ request()->is('owner/purchases*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shopping-cart"></i>
                                    <p>Purchase Settlements</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- E-COMMERCE OVERVIEW (Owner) -->
                    <li class="nav-item has-treeview {{ request()->is('owner/ecom*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('owner/ecom*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-globe"></i>
                            <p>
                                E-Commerce
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('owner.ecom.overview') }}" class="nav-link {{ request()->is('owner/ecom/overview*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <p>E-commerce Overview</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.ecom.orders') }}" class="nav-link {{ request()->is('owner/ecom/orders*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shopping-bag"></i>
                                    <p>Online Orders</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ request()->is('owner/laporan*', 'owner/stocksReport*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('owner/laporan*', 'owner/stocksReport*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>
                                Reports & Analytics
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('owner.laporan.penjualan') }}" class="nav-link {{ request()->is('owner/laporan/penjualan*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>Branch Sales Report</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.stocksReport.index') }}" class="nav-link {{ request()->is('owner/stocksReport*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-boxes"></i>
                                    <p>Stock Report</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.reports.financial') }}" class="nav-link {{ request()->is('owner/reports/financial*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>Financial Report</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- LOGOUT -->
                <li class="nav-item">
                    <a href="{{ route('logout.admin') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                        <p class="text-danger">Logout</p>
                    </a>
                    <form id="logout-form" action="{{ route('logout.admin') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<style>
    /* Custom styling for sidebar */
    .nav-sidebar > .nav-item > .nav-link.active {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .nav-treeview > .nav-item > .nav-link.active {
        background-color: rgba(0, 123, 255, 0.2);
        color: #007bff;
    }
    
    /* Badge styling */
    .badge-danger {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    /* Icon colors */
    .nav-icon.text-warning {
        color: #ffc107 !important;
    }
    
    .nav-icon.text-danger {
        color: #dc3545 !important;
    }
</style>

<script>
    // Auto-expand active menu
    document.addEventListener('DOMContentLoaded', function() {
        const activeLinks = document.querySelectorAll('.nav-link.active');
        activeLinks.forEach(link => {
            const treeview = link.closest('.has-treeview');
            if (treeview) {
                treeview.classList.add('menu-open');
            }
            
            // Expand parent nav-item if exists
            const parentNavItem = link.closest('.nav-treeview');
            if (parentNavItem) {
                const parentTreeview = parentNavItem.closest('.has-treeview');
                if (parentTreeview) {
                    parentTreeview.classList.add('menu-open');
                }
            }
        });
    });
</script>