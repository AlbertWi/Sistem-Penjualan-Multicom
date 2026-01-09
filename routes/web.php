<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    EcomAuthController,
    CatalogController,
    CartController,
    CheckoutController,
    BranchController,
    UserController,
    SaleController,
    StockReportController,
    PurchaseController,
    PurchaseItemController,
    StockTransferController,
    ProductController,
    AccessoryBranchPriceController,
    BranchStockController,
    SupplierController,
    StockController,
    BrandController,
    TypeController,
    AccessoryController,
    CustomerController,
    InventoryItemController,
    ManagerProductController,
    ProductEcomController,
    ManajerOperasionalOrderController,
    OrderAssignController
};

// ===========================================
// AJAX HELPER ROUTES
// ===========================================
Route::get('/ajax/types-by-brand/{brand_id}', function($brand_id) {
    return \App\Models\Type::where('brand_id', $brand_id)->get();
})->name('ajax.types.by.brand');

// ===========================================
// PUBLIC E-COMMERCE ROUTES
// ===========================================
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

// ===========================================
// E-COMMERCE CUSTOMER AUTHENTICATION
// ===========================================
Route::prefix('customer')->name('customer.')->group(function () {
    // Guest routes
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [EcomAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [EcomAuthController::class, 'login']);
        Route::get('/register', [EcomAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [EcomAuthController::class, 'register']);
    });
    
    // Protected customer routes
    Route::middleware('auth:customer')->group(function () {
        // Profile
        Route::get('/profile', [EcomAuthController::class, 'profile'])->name('profile');
        Route::post('/profile/update', [EcomAuthController::class, 'profileUpdate'])->name('profile.update');
        Route::post('/logout', [EcomAuthController::class, 'logout'])->name('logout');
        
        // Cart
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');
        
        // Checkout & Orders
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
        
        Route::get('/orders', [CheckoutController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [CheckoutController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/payment', [CheckoutController::class, 'payment'])->name('orders.payment');
        Route::put('/orders/{order}/cancel', [CheckoutController::class, 'cancel'])->name('orders.cancel');
    });
});

// ===========================================
// ADMIN/STAFF AUTHENTICATION
// ===========================================
Route::middleware('guest:sanctum')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.admin');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout.admin')->middleware('auth:sanctum');

// ===========================================
// PROTECTED ADMIN/STAFF ROUTES
// ===========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    
    // ===========================================
    // SHARED ROUTES: KEPALA_TOKO & MANAJER_OPERASIONAL
    // ===========================================
    Route::middleware('role:kepala_toko,manajer_operasional')->group(function () {
        // Purchases
        Route::resource('purchases', PurchaseController::class);
        Route::post('/purchases/{purchase}/save-imei', [PurchaseController::class, 'saveImei'])->name('purchases.save_imei');
        Route::resource('purchase-items', PurchaseItemController::class);
        
        // Stock Transfers
        Route::get('/stock-transfers/find-by-imei/{imei}', [StockTransferController::class, 'findByImei'])
            ->where('imei', '.*')
            ->name('stock-transfers.find-by-imei');
        Route::resource('stock-transfers', StockTransferController::class);
        
        // Products
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
        Route::get('/products/{product}/latest-price', [ProductController::class, 'getLatestPrice'])->name('products.latest-price');
        
        // Accessories
        Route::resource('accessory-prices', AccessoryBranchPriceController::class)->only(['index', 'create', 'store']);
        
        // Reports
        Route::get('/report/stock-summary', [StockReportController::class, 'rekap'])->name('report.stock-summary');
    });

    // ===========================================
    // SHARED ROUTES: ALL ROLES (OWNER, KEPALA_TOKO, MANAJER_OPERASIONAL)
    // ===========================================
    Route::middleware('role:owner,kepala_toko,manajer_operasional')->group(function () {
        Route::get('/stok-cabang', [BranchStockController::class, 'index'])->name('stok-cabang');
    });

    // ===========================================
    // SHARED ROUTES: MANAJER_OPERASIONAL & OWNER
    // ===========================================
    Route::middleware('role:manajer_operasional,owner')->group(function () {
        Route::resource('inventory', InventoryItemController::class);
    });

    // ===========================================
    // OWNER ONLY ROUTES
    // ===========================================
    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function () {
        // Management
        Route::resource('branches', BranchController::class);
        Route::resource('users', UserController::class);
        
        // Sales Settlements
        Route::get('/sales', [SaleController::class, 'ownerIndex'])->name('sales.index');
        Route::get('/sales/{id}/edit', [SaleController::class, 'ownerEdit'])->name('sales.edit');
        Route::put('/sales/{id}', [SaleController::class, 'ownerUpdate'])->name('sales.update');
        Route::get('/sales/pelunasan', [SaleController::class, 'ownerLunas'])->name('sales.lunas');
        Route::post('/sales/{sale}/pelunasan', [SaleController::class, 'pelunasan'])->name('sales.pelunasan');
        
        // Purchase Settlements
        Route::get('/purchases', [PurchaseController::class, 'ownerIndex'])->name('purchases.index');
        Route::post('/purchases/{purchase}/pelunasan', [PurchaseController::class, 'pelunasan'])->name('purchases.pelunasan');
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/financial', [SaleController::class, 'financialReport'])->name('financial');
        });
        
        // Sales Reports
        Route::get('/laporan-penjualan', [SaleController::class, 'laporanPenjualan'])->name('laporan.penjualan');
        Route::get('/sales/export-pdf', [SaleController::class, 'exportPdf'])->name('sales.export-pdf');
        
        // Stock Reports
        Route::prefix('stocksReport')->name('stocksReport.')->group(function () {
            Route::get('/', [StockReportController::class, 'index'])->name('index');
            Route::get('/print', [StockReportController::class, 'print'])->name('print');
            Route::get('/pdf', [StockReportController::class, 'pdf'])->name('pdf');
        });
        
        // E-commerce Overview
        Route::prefix('ecom')->name('ecom.')->group(function () {
            Route::get('/overview', [ManajerOperasionalOrderController::class, 'ownerOverview'])->name('overview');
            Route::get('/orders', [ManajerOperasionalOrderController::class, 'ownerOrders'])->name('orders');
        });
    });

    // ===========================================
    // MANAJER_OPERASIONAL ONLY ROUTES
    // ===========================================
    Route::middleware('role:manajer_operasional')->prefix('manajer_operasional')->name('manajer_operasional.')->group(function () {
        // Master Data
        Route::resource('brands', BrandController::class);
        Route::resource('types', TypeController::class);
        Route::resource('products', ProductController::class);
        Route::resource('accessories', AccessoryController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('customers', CustomerController::class);
        Route::resource('stocks', StockController::class);
        
        // Stock IMEI
        Route::get('/stocks/imei/{product}', [StockController::class, 'showImei'])->name('stocks.imei');
        
        // Inventory Management
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/show', [InventoryItemController::class, 'show'])->name('show');
            Route::get('/edit-price', [InventoryItemController::class, 'editPrice'])->name('editPrice');
            Route::post('/update-price', [InventoryItemController::class, 'updatePrice'])->name('updatePrice');
            Route::get('/for-ecom', [ManagerProductController::class, 'index'])->name('for_ecom');
            Route::get('/{inventoryItem}/edit-price', [ManagerProductController::class, 'editPrice'])->name('edit_price');
            Route::post('/{inventoryItem}/update-price', [ManagerProductController::class, 'updatePrice'])->name('update_price');
            Route::post('/{inventoryItem}/post', [ManagerProductController::class, 'postToCatalog'])->name('post');
            Route::post('/{inventoryItem}/unpost', [ManagerProductController::class, 'unpostFromCatalog'])->name('unpost');
        });
        
        // Product E-commerce
        Route::prefix('product')->name('product_ecom.')->group(function () {
            Route::get('/for-ecom', [ProductEcomController::class, 'index'])->name('index');
            Route::post('/for-ecom/{product}', [ProductEcomController::class, 'update'])->name('update');
        });
        
        // E-commerce Management
        Route::prefix('ecom')->name('ecom.')->group(function () {
            Route::get('/index', [ManagerProductController::class, 'index'])->name('listings');
            Route::get('/settings', [ManagerProductController::class, 'settings'])->name('settings');
        });
        
        // Online Orders Management
        Route::prefix('orders')->name('orders.')->group(function () {
            // List & Detail
            Route::get('/', [ManajerOperasionalOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [ManajerOperasionalOrderController::class, 'show'])->name('show');
            
            // === ASSIGN IMEI ROUTES===
            Route::get('/{order}/assign-imei/{orderItem}', [ManajerOperasionalOrderController::class, 'assignImei'])->name('assign-imei');
            Route::post('/{order}/assign-imei/{orderItem}', [ManajerOperasionalOrderController::class, 'storeAssignedImei'])->name('store-imei');
            Route::post('{order}/reallocate-single', [ManajerOperasionalOrderController::class, 'reallocateSingleStock'])->name('reallocate-single');
            
            // Stock Pickup
            Route::post('/{order}/confirm-stock-pickup', [ManajerOperasionalOrderController::class, 'confirmStockPickup'])->name('confirm-stock-pickup');
            Route::post('/{order}/confirm-branch-pickup/{branchId}', [ManajerOperasionalOrderController::class, 'confirmBranchPickup'])->name('confirm-branch-pickup');
            
            // Order Actions
            Route::post('/{order}/complete', [ManajerOperasionalOrderController::class, 'complete'])->name('complete');
            Route::post('/{order}/cancel', [ManajerOperasionalOrderController::class, 'cancel'])->name('cancel');
            Route::post('/{order}/update-status', [ManajerOperasionalOrderController::class, 'updateStatus'])->name('update-status');
            
            // Branch Stock & Reallocation
            Route::get('/{order}/branch-stock/{branchId}', [ManajerOperasionalOrderController::class, 'getBranchStock'])->name('branch-stock');
            Route::post('/{order}/reallocate-stock', [ManajerOperasionalOrderController::class, 'reallocateStock'])->name('reallocate-stock');
            
            // Print
            Route::get('/{order}/print', [ManajerOperasionalOrderController::class, 'printInvoice'])->name('print');
        });
            
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales', [SaleController::class, 'manajerSalesReport'])->name('sales');
        });
    });

    // ===========================================
    // KEPALA_TOKO ONLY ROUTES
    // ===========================================
    Route::middleware('role:kepala_toko')->group(function () {
        // Products
        Route::resource('product', ProductController::class);
        
        // Sales Management
        Route::resource('sales', SaleController::class);
        Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
        
        // Sales Search & IMEI
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/search-nota', [SaleController::class, 'searchNota'])->name('searchNota');
            Route::get('/search-customer', [SaleController::class, 'searchCustomer'])->name('searchCustomer');
            Route::get('/find-imei', [SaleController::class, 'findImei'])->name('findImei');
            Route::get('/{id}/input-imei', [SaleController::class, 'inputImei'])->name('input-imei');
            Route::post('/{id}/save-imei', [SaleController::class, 'saveImei'])->name('save-imei');
        });
        
        // Product Search
        Route::get('/cari-produk-by-imei', [SaleController::class, 'cariByImei'])->name('cari-produk-by-imei');
        Route::get('/search-by-imei', [SaleController::class, 'searchByImei'])->name('search.by.imei');
        Route::get('/search-accessory', [SaleController::class, 'searchAccessory'])->name('sales.searchAccessory');
    });
});

// ===========================================
// LEGACY REDIRECTS
// ===========================================
Route::permanentRedirect('/ecom/login', '/customer/login');
Route::permanentRedirect('/ecom/register', '/customer/register');