<?php
namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;

class ManagerProductController extends Controller
{
    public function index()
    {
        // tampilkan semua in_stock
        $items = InventoryItem::with('product.brand')
                    ->inStock()
                    ->orderBy('created_at','desc')
                    ->paginate(30);

        return view('manajer_operasional.inventory.for_ecom', compact('items'));
    }

    public function editPrice(InventoryItem $inventoryItem)
    {
        $this->authorize('update', $inventoryItem); // optional policy
        return view('manajer_operasional.inventory.edit_price', compact('inventoryItem'));
    }

    public function updatePrice(Request $request, InventoryItem $inventoryItem)
    {
        $request->validate([
            'ecom_price' => 'required|numeric|min:0'
        ]);

        $inventoryItem->update([
            'ecom_price' => $request->ecom_price
        ]);

        return redirect()->route('inventory.for_ecom')->with('success','Harga tersimpan.');
    }

    public function postToCatalog(InventoryItem $inventoryItem)
    {
        if (! $inventoryItem->ecom_price) {
            return back()->with('error', 'Masukkan harga dulu sebelum di-post.');
        }
        $inventoryItem->update([
            'is_listed' => true,
            'listed_at' => now(),
        ]);
        return back()->with('success','Item berhasil diposting ke katalog.');
    }

    public function unpostFromCatalog(InventoryItem $inventoryItem)
    {
        $inventoryItem->update(['is_listed' => false, 'listed_at' => null]);
        return back()->with('success','Item dihapus dari katalog.');
    }
}

