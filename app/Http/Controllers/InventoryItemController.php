<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\Product;

class InventoryItemController extends Controller
{
    // Menampilkan stok per cabang
    public function index()
    {
        $branches = Branch::all();
        return view('admin.inventory.index', compact('branches'));
    }

    // Menampilkan stok untuk cabang tertentu
    public function show($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $inventory = InventoryItem::with('product')
            ->where('branch_id', $branchId)
            ->get();

        return view('admin.inventory.show', compact('branch', 'inventory'));
    }
    public function editPrice()
    {
        $products = \App\Models\Product::orderBy('name')->get();
        return view('admin.inventory.edit_price', compact('products'));
    }
    
    public function updatePrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'purchase_price' => 'required|numeric|min:0',
        ]);
    
        $updated = \App\Models\InventoryItem::where('product_id', $request->product_id)
            ->where('status', 'in_stock')
            ->update(['purchase_price' => $request->purchase_price]);
    
        return redirect()->back()->with('success', "Harga modal berhasil diperbarui untuk {$updated} item in stock.");
    }

}
