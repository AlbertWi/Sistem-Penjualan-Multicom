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
    StockRequestController
};

// Catalog
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/product/{inventoryItem}', [CatalogController::class, 'show'])->name('catalog.show');

// Authentication - Admin
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');

// Authentication - E-commerce
Route::prefix('ecom')->name('ecom.')->group(function () {
    Route::get('/login', [EcomAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [EcomAuthController::class, 'login']);
    Route::get('/register', [EcomAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [EcomAuthController::class, 'register']);
    Route::post('/logout', [EcomAuthController::class, 'logout'])->name('logout');
});

Route::middleware('auth:customer')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::get('/profile', [EcomAuthController::class, 'profile'])->name('ecom.profile');
    Route::post('/profile/update', [EcomAuthController::class, 'profileUpdate'])->name('ecom.profile.update');
});



Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::middleware('role:manajer_operasional,kepala_toko')->group(function () {
        // Purchases
        Route::post('/purchases/{purchase}/save-imei', [PurchaseController::class, 'saveImei'])->name('purchases.save_imei');
        Route::resource('purchases', PurchaseController::class);
        Route::resource('purchase-items', PurchaseItemController::class);
        
        // Stock Transfers
        Route::get('/stock-transfers/find-by-imei/{imei}', [StockTransferController::class, 'findByImei'])
            ->where('imei', '.*');
        Route::resource('stock-transfers', StockTransferController::class);
        
        // Products & Accessories
        Route::get('/products/{product}/latest-price', [ProductController::class, 'getLatestPrice']);
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
        Route::resource('accessory-prices', AccessoryBranchPriceController::class)->only(['index', 'create', 'store']);
        // Reports
        Route::get('/report/stock-summary', [StockReportController::class, 'rekap'])->name('report.stock-summary');
    });


    Route::middleware('role:owner,kepala_toko,manajer_operasional')->group(function () {
        Route::get('/stok-cabang', [BranchStockController::class, 'index'])->name('stok-cabang');
    });


    Route::middleware('role:manajer_operasional,owner')->group(function () {
        Route::resource('inventory', InventoryItemController::class);
    });



    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function () {
        // Master Data
        Route::resource('branches', BranchController::class);
        Route::resource('users', UserController::class);
        // Sales Management
        Route::get('/sales', [SaleController::class, 'ownerIndex'])->name('sales.index');
        Route::post('/sales/{sale}/pelunasan', [SaleController::class, 'pelunasan'])->name('sales.pelunasan');
        
        // Purchase Management
        Route::get('/purchases', [PurchaseController::class, 'ownerIndex'])->name('purchases.index');
        Route::post('/purchases/{purchase}/pelunasan', [PurchaseController::class, 'pelunasan'])->name('purchases.pelunasan');
        
        // Reports
        Route::get('/laporan-penjualan', [SaleController::class, 'laporanPenjualan'])->name('laporan.penjualan');
        Route::get('/sales/export-pdf', [SaleController::class, 'exportPdf'])->name('sales.export-pdf');
        
        // Stock Reports
        Route::get('/stocksReport', [StockReportController::class, 'index'])->name('stocksReport.index');
        Route::get('/stocksReport/print', [StockReportController::class, 'print'])->name('stocksReport.print');
        Route::get('/stocksReport/pdf', [StockReportController::class, 'pdf'])->name('stocksReport.pdf');
    });




    Route::middleware('role:manajer_operasional')->prefix('manajer_operasional')->name('manajer_operasional.')->group(function () {
        // Master Data
        Route::resource('products', ProductController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('types', TypeController::class);
        Route::resource('accessories', AccessoryController::class);
        Route::resource('stocks', StockController::class);
        Route::resource('customers', CustomerController::class);
        
        // Stock Management
        Route::get('/stocks/imei/{product}', [StockController::class, 'showImei'])->name('stocks.imei');
        
        // Inventory Price Management
        Route::get('/inventory/edit-price', [InventoryItemController::class, 'editPrice'])->name('inventory.editPrice');
        Route::post('/inventory/update-price', [InventoryItemController::class, 'updatePrice'])->name('inventory.updatePrice');
        
        // E-commerce Management
        Route::get('/inventory/for-ecom', [ManagerProductController::class, 'index'])->name('inventory.for_ecom');
        Route::get('/inventory/{inventoryItem}/edit-price', [ManagerProductController::class, 'editPrice'])->name('inventory.edit_price');
        Route::post('/inventory/{inventoryItem}/update-price', [ManagerProductController::class, 'updatePrice'])->name('inventory.update_price');
        Route::post('/inventory/{inventoryItem}/post', [ManagerProductController::class, 'postToCatalog'])->name('inventory.post');
        Route::post('/inventory/{inventoryItem}/unpost', [ManagerProductController::class, 'unpostFromCatalog'])->name('inventory.unpost');
    });




    Route::middleware('role:kepala_toko')->group(function () {
        // Product Management
        Route::resource('product', ProductController::class);
        
        // Sales Management
        Route::get('/cari-produk-by-imei', [SaleController::class, 'cariByImei'])->name('cari-produk-by-imei');
        Route::get('/search-by-imei', [SaleController::class, 'searchByImei'])->name('search.by.imei');
        Route::get('/search-accessory', [SaleController::class, 'searchAccessory'])->name('sales.searchAccessory');
        
        // Sales IMEI
        Route::get('/sales/{id}/input-imei', [SaleController::class, 'inputImei'])->name('sales.input-imei');
        Route::post('/sales/{id}/save-imei', [SaleController::class, 'saveImei'])->name('sales.save-imei');
        Route::get('/sales/find-imei', [SaleController::class, 'findImei'])->name('sales.findImei');
        
        // Sales Search & Print
        Route::get('/sales/search-nota', [SaleController::class, 'searchNota'])->name('sales.searchNota');
        Route::get('/sales/search-customer', [SaleController::class, 'searchCustomer'])->name('sales.searchCustomer');
        Route::get('/sales/{sale}/print', [SaleController::class, 'print'])->name('sales.print');
        
        Route::resource('sales', SaleController::class);
        
        // Stock Request Management
        Route::resource('stock-requests', StockRequestController::class);
        Route::post('/stock-requests/{id}/approve', [StockRequestController::class, 'approve'])->name('stock-requests.approve');
        Route::post('/stock-requests/{id}/reject', [StockRequestController::class, 'reject'])->name('stock-requests.reject');
    });
});