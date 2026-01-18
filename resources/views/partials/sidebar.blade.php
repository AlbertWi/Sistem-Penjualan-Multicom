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
                    <!-- DATA MASTER -->
                    <li class="nav-item has-treeview {{ request()->is('manajer_operasional/brands*', 'manajer_operasional/types*', 'manajer_operasional/products*', 'manajer_operasional/accessories*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('manajer_operasional/brands*', 'manajer_operasional/types*', 'manajer_operasional/products*', 'manajer_operasional/accessories*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-database"></i>
                            <p>
                                Master
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.brands.index') }}" class="nav-link {{ request()->is('manajer_operasional/brands*') ? 'active' : '' }}">
                                    <i class="nav-icon far fa-copyright"></i>
                                    <p>Merek</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.types.index') }}" class="nav-link {{ request()->is('manajer_operasional/types*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-mobile-alt"></i>
                                    <p>Type</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.products.index') }}" class="nav-link {{ request()->is('manajer_operasional/products*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-box"></i>
                                    <p>Produk</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.accessories.index') }}" class="nav-link {{ request()->is('manajer_operasional/accessories*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-headphones"></i>
                                    <p>Aksesoris</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.inventory.editPrice') }}" class="nav-link {{ request()->is('manajer_operasional/inventory/edit-price*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-money-bill-wave text-warning"></i>
                                    <p>Edit Harga Produk</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- KONTAK -->
                    <li class="nav-item has-treeview {{ request()->is('manajer_operasional/customers*', 'manajer_operasional/suppliers*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('manajer_operasional/customers*', 'manajer_operasional/suppliers*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-address-book"></i>
                            <p>
                                Kontak
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.customers.index') }}" class="nav-link {{ request()->is('manajer_operasional/customers*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-friends"></i>
                                    <p>Pelanggan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.suppliers.index') }}" class="nav-link {{ request()->is('manajer_operasional/suppliers*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-truck"></i>
                                    <p>Supplier</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- PEMBELIAN -->
                    <li class="nav-item">
                        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->is('purchases*') && !request()->is('owner/purchases*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Pembelian</p>
                        </a>
                    </li>

                    <!-- MANAJEMEN STOK -->
                    <li class="nav-item has-treeview {{ request()->is('stock-transfers*', 'stok-cabang*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('stock-transfers*', 'stok-cabang*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-warehouse"></i>
                            <p>
                                Manajemen Stok
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('stock-transfers.index') }}" class="nav-link {{ request()->is('stock-transfers*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-exchange-alt"></i>
                                    <p>Transfer Stok</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('stok-cabang') }}" class="nav-link {{ request()->is('stok-cabang*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-boxes"></i>
                                    <p>Stok Cabang</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- E-COMMERCE -->
                    <li class="nav-item has-treeview {{ request()->is('manajer_operasional/ecom*', 'manajer_operasional/orders*', 'manajer_operasional/inventory*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('manajer_operasional/ecom*', 'manajer_operasional/orders*', 'manajer_operasional/inventory*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-store"></i>
                            <p>
                                E-Commerce
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <!-- Produk E-commerce -->
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.inventory.for_ecom') }}" class="nav-link {{ request()->is('manajer_operasional/inventory/for_ecom*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shopping-cart"></i>
                                    <p>Produk E-commerce</p>
                                </a>
                            </li>

                            <!-- Pesanan Online -->
                            <li class="nav-item">
                                <a href="{{ route('manajer_operasional.orders.index') }}" class="nav-link {{ request()->is('manajer_operasional/orders*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shopping-bag"></i>
                                    <p>Pesanan Online</p>
                                    @php
                                        $pendingOrdersCount = \App\Models\Order::where('status', 'pending')->count();
                                    @endphp
                                    @if($pendingOrdersCount > 0)
                                        <span class="badge badge-danger right">{{ $pendingOrdersCount }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </li>
                @elseif(Auth::check() && Auth::user()->role === 'kepala_toko')
                    <!-- MENU KEPALA TOKO -->
                    <li class="nav-item">
                        <a href="{{ route('sales.index') }}" class="nav-link {{ request()->is('sales*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p>Penjualan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('stock-transfers.index') }}" class="nav-link {{ request()->is('stock-transfers*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-exchange-alt"></i>
                            <p>Transfer Stok</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->is('purchases*') && !request()->is('owner/purchases*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Pembelian</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('stok-cabang') }}" class="nav-link {{ request()->is('stok-cabang*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-warehouse"></i>
                            <p>Stok Cabang</p>
                        </a>
                    </li>

                @elseif(Auth::check() && Auth::user()->role === 'owner')
                    <!-- MENU OWNER -->
                    <li class="nav-item">
                        <a href="{{ route('stok-cabang') }}" class="nav-link {{ request()->is('stok-cabang*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-boxes"></i>
                            <p>Stok Cabang</p>
                        </a>
                    </li>

                    <li class="nav-item has-treeview {{ request()->is('owner/branches*', 'owner/users*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('owner/branches*', 'owner/users*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-building"></i>
                            <p>
                                Manajemen
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('owner.branches.index') }}" class="nav-link {{ request()->is('owner/branches*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-store"></i>
                                    <p>Manajemen Cabang</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.users.index') }}" class="nav-link {{ request()->is('owner/users*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>Manajemen User</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item has-treeview {{ request()->is('owner/sales*', 'owner/purchases*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('owner/sales*', 'owner/purchases*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i>
                            <p>
                                Pelunasan
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('owner.sales.lunas') }}" class="nav-link {{ request()->is('owner/sales*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cash-register"></i>
                                    <p>Pelunasan Penjualan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.purchases.index') }}" class="nav-link {{ request()->is('owner/purchases*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shopping-cart"></i>
                                    <p>Pelunasan Pembelian</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('owner.sales.index') }}"
                        class="nav-link {{ request()->is('owner/sales*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-cash-register"></i>
                            <p>Edit Penjualan</p>
                        </a>
                    </li>
                    <!-- LAPORAN & ANALITIK -->
                    <li class="nav-item has-treeview {{ request()->is('owner/laporan*', 'owner/stocksReport*', 'owner/reports*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('owner/laporan*', 'owner/stocksReport*', 'owner/reports*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>
                                Laporan & Analitik
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('owner.laporan.penjualan') }}" class="nav-link {{ request()->is('owner/laporan/penjualan*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>Laporan Penjualan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.stocksReport.index') }}" class="nav-link {{ request()->is('owner/stocksReport*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-boxes"></i>
                                    <p>Laporan Stok</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('owner.salesReport') }}" class="nav-link {{ request()->is('owner/laporan/penjualan*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-bar"></i>
                                    <p>Laporan Penjualan Online</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                <!-- LOGOUT -->
                <li class="nav-item">
                    <a href="{{ route('logout.admin') }}" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                        <p class="text-danger">Keluar</p>
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
    /* Styling kustom untuk sidebar */
    .nav-sidebar > .nav-item > .nav-link.active {
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .nav-treeview > .nav-item > .nav-link.active {
        background-color: rgba(0, 123, 255, 0.2);
        color: #007bff;
    }
    
    /* Styling badge */
    .badge-danger {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    /* Warna ikon */
    .nav-icon.text-warning {
        color: #ffc107 !important;
    }
    
    .nav-icon.text-danger {
        color: #dc3545 !important;
    }
    
    /* Menu khusus owner dengan warna berbeda */
    .nav-link .text-warning {
        color: #ffc107 !important;
        font-weight: bold;
    }
</style>

<script>
    // Auto-expand menu aktif
    document.addEventListener('DOMContentLoaded', function() {
        const activeLinks = document.querySelectorAll('.nav-link.active');
        activeLinks.forEach(link => {
            const treeview = link.closest('.has-treeview');
            if (treeview) {
                treeview.classList.add('menu-open');
            }
            
            // Expand parent nav-item jika ada
            const parentNavItem = link.closest('.nav-treeview');
            if (parentNavItem) {
                const parentTreeview = parentNavItem.closest('.has-treeview');
                if (parentTreeview) {
                    parentTreeview.classList.add('menu-open');
                }
            }
        });
        
        // Highlight menu baru untuk owner
        const editSalesMenu = document.querySelector('a[href*="owner/sales/edit"]');
        if (editSalesMenu) {
            editSalesMenu.addEventListener('click', function() {
                // Hapus active dari semua menu
                document.querySelectorAll('.nav-link.active').forEach(link => {
                    link.classList.remove('active');
                });
                // Tambah active ke menu ini
                this.classList.add('active');
            });
        }
    });
</script>