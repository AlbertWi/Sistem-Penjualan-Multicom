<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    BranchController,
    UserController,
    SupplierController,
    ProductController,
    InventoryItemController,
    PurchaseController,
    PurchaseItemController,
    SaleController,
    SaleItemController,
    StockTransferController,
    StockTransferItemController,
    AuthController,
    BrandController,
    StockController,
    TypeController,
    StockRequestController,
    BranchStockController,
    AccessoryController,
    StockReportController,
    CustomerController,
    ManagerProductController,
    CatalogController
};

// === AUTH ROUTES ===
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/product/{inventoryItem}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');

// === AUTHENTICATED ROUTES ===
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    //shared route
    Route::middleware(['auth', 'role:manajer_operasional,kepala_toko'])->group(function () {
        Route::post('/purchases/{purchase}/save-imei', [PurchaseController::class, 'saveImei'])->name('purchases.save_imei');
        Route::resource('purchases', PurchaseController::class);
        Route::resource('purchase-items', PurchaseItemController::class);
        Route::get('/stock-transfers/find-by-imei/{imei}', [\App\Http\Controllers\StockTransferController::class, 'findByImei'])
            ->where('imei', '.*');
        Route::resource('stock-transfers', StockTransferController::class);
        Route::get('/products/{product}/latest-price', [ProductController::class, 'getLatestPrice']);
        Route::resource('accessory-prices', AccessoryBranchPriceController::class)->only(['index','create','store']);
        Route::get('/products/search', [App\Http\Controllers\ProductController::class, 'search'])->name('products.search');
        Route::get('/report/stock-summary', [App\Http\Controllers\StockReportController::class, 'rekap'])
        ->name('report.stock-summary');
    });

    Route::middleware(['auth', 'role:owner,kepala_toko,manajer_operasional'])->group(function () {
        Route::get('/stok-cabang', [BranchStockController::class, 'index'])->name('stok-cabang');
    });
    // === OWNER ===
    Route::middleware('role:owner')->group(function () {
        Route::resource('inventory', InventoryItemController::class);
        Route::resource('branches', BranchController::class);
        Route::resource('users', UserController::class);
        Route::get('/laporan-penjualan', [SaleController::class, 'laporanPenjualan'])->name('owner.laporan.penjualan');
        Route::get('/sales/export-pdf', [SaleController::class, 'exportPdf'])->name('sales.export-pdf');
        Route::get('stocksReport', [StockReportController::class, 'index'])->name('owner.stocksReport.index');
        Route::get('stocksReport/print', [StockReportController::class, 'print'])->name('owner.stocksReport.print');
        Route::get('stocksReport/pdf', [StockReportController::class, 'pdf'])->name('owner.stocksReport.pdf');
        Route::get('/owner/sales', [SaleController::class, 'ownerIndex'])->name('owner.sales.index');
        Route::post('/owner/sales/{sale}/pelunasan', [SaleController::class, 'pelunasan'])->name('owner.sales.pelunasan');
        Route::get('/owner/purchases', [PurchaseController::class, 'ownerIndex'])->name('owner.purchases.index');
        Route::post('/owner/purchases/{purchase}/pelunasan', [PurchaseController::class, 'pelunasan'])->name('owner.purchases.pelunasan');
    });

    // === manajer_operasional ===
    Route::middleware('role:manajer_operasional')->group(function () {
        Route::Resource('products', ProductController::class);
        Route::resource('suppliers', SupplierController::class);
        Route::get('/stocks/imei/{product}', [StockController::class, 'showImei'])->name('stocks.imei');
        Route::resource('brands', BrandController::class);
        Route::resource('types', TypeController::class);
        Route::resource('accessories', AccessoryController::class);
        Route::resource('stocks', StockController::class);
        Route::resource('customers', CustomerController::class);
        Route::get('/manajer_operasional/inventory/edit-price', [InventoryItemController::class, 'editPrice'])->name('inventory.editPrice');
        Route::post('/manajer_operasional/inventory/update-price', [InventoryItemController::class, 'updatePrice'])->name('inventory.updatePrice');
        Route::get('/inventory/for-ecom', [ManagerProductController::class, 'index'])->name('inventory.for_ecom');
        Route::get('/inventory/{inventoryItem}/edit-price', [ManagerProductController::class, 'editPrice'])->name('inventory.edit_price');
        Route::post('/inventory/{inventoryItem}/update-price', [ManagerProductController::class, 'updatePrice'])->name('inventory.update_price');
        Route::post('/inventory/{inventoryItem}/post', [ManagerProductController::class, 'postToCatalog'])->name('inventory.post');
        Route::post('/inventory/{inventoryItem}/unpost', [ManagerProductController::class, 'unpostFromCatalog'])->name('inventory.unpost');
    });

    // === KEPALA TOKO ===
    Route::middleware('role:kepala_toko')->group(function () {
        Route::resource('product', ProductController::class);
        Route::get('cari-produk-by-imei', [SaleController::class, 'cariByImei'])->name('cari-produk-by-imei');
        Route::get('sales/{id}/input-imei', [SaleController::class, 'inputImei'])->name('sales.input-imei');
        Route::post('sales/{id}/save-imei', [SaleController::class, 'saveImei'])->name('sales.save-imei');
        Route::get('/search-accessory', [SaleController::class, 'searchAccessory'])->name('sales.searchAccessory');
        Route::get('/sales/search-nota', [App\Http\Controllers\SaleController::class, 'searchNota'])->name('sales.searchNota');
        Route::get('/sales/find-imei', [App\Http\Controllers\SaleController::class, 'findImei'])->name('sales.findImei');
        Route::resource('sales', SaleController::class);
        Route::get('/search-by-imei', [SaleController::class, 'searchByImei'])->name('search.by.imei');
        Route::resource('stock-requests', StockRequestController::class);
        Route::post('stock-requests/{id}/approve', [StockRequestController::class, 'approve'])
            ->name('stock-requests.approve');
        Route::post('stock-requests/{id}/reject', [StockRequestController::class, 'reject'])
            ->name('stock-requests.reject');
        Route::get('/sales/{sale}/print', [App\Http\Controllers\SaleController::class, 'print'])->name('sales.print');
        Route::get('/sales/search-customer', [SaleController::class, 'searchCustomer'])->name('sales.searchCustomer');

    });
});
