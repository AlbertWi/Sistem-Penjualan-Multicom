<?php
namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductEcomSetting;
class CatalogController extends Controller
{
    public function index()
    {
        $products = Product::with([
                'brand',
                'images',
                'ecomSetting'
            ])
            ->whereHas('ecomSetting', function ($q) {
                $q->where('is_listed', true);
            })
            ->withCount([
                'inventoryItems as stock_count' => function ($q) {
                    $q->whereHas('product.ecomSetting', function ($subQ) use ($q) {
                        $subQ->where('is_listed', true)
                            ->whereColumn('inventory_items.branch_id', 'product_ecom_settings.branch_id');
                    })
                    ->where('status', 'in_stock');
                }
            ])
            ->having('stock_count', '>', 0)
            ->paginate(20);
        return view('catalog.index', compact('products'));
    }

    public function show(Product $product)
    {
        $ecomSetting = $product->ecomSetting()->first();
        abort_unless(
            optional($product->ecomSetting)->is_listed,
            404
        );
        $stock = $product->inventoryItems()
            ->where('branch_id', $ecomSetting->branch_id)
            ->where('status', 'in_stock')
            ->count();

        $relatedProducts = Product::with(['images', 'ecomSetting'])
        ->where('id', '!=', $product->id)
        ->whereHas('ecomSetting', function ($q) use ($ecomSetting) {
            $q->where('is_listed', true)
            ->where('branch_id', $ecomSetting->branch_id); // Cabang yang sama
        })
        ->inRandomOrder()
        ->take(4)
        ->get();

        return view('catalog.show', compact(
            'product',
            'stock',
            'relatedProducts',
            'ecomSetting'
        ));
    }

}
