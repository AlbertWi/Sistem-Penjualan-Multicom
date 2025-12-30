<?php
namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Product;
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
            ->whereHas('inventoryItems', function ($q) {
                $q->where('status', 'in_stock');
            })
            ->withCount([
                'inventoryItems as stock_count' => function ($q) {
                    $q->where('status', 'in_stock');
                }
            ])
            ->paginate(20);
        return view('catalog.index', compact('products'));
    }

    public function show(Product $product)
    {
        abort_unless(
            optional($product->ecomSetting)->is_listed,
            404
        );
        $stock = $product->inventoryItems()
            ->where('status', 'in_stock')
            ->count();

        return view('catalog.show', compact('product', 'stock'));
    }

}
