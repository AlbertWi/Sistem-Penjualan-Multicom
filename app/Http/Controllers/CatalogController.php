<?php
namespace App\Http\Controllers;

use App\Models\InventoryItem;

class CatalogController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with('product.brand','product.images')
                    ->inStock()
                    ->listed()
                    ->paginate(20);

        return view('catalog.index', compact('items'));
    }

    public function show(InventoryItem $inventoryItem)
    {
        abort_unless($inventoryItem->is_listed && $inventoryItem->status === 'in_stock', 404);
        return view('catalog.show', compact('inventoryItem'));
    }
}
