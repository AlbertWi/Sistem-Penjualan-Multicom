<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\Type;

class InventoryItemController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        return view('admin.inventory.index', compact('branches'));
    }
    public function show($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $inventory = InventoryItem::with('product')
            ->where('branch_id', $branchId)
            ->get();

        return view('manajer_operasional.inventory.show', compact('branch', 'inventory'));
    }
    public function editPrice()
    {
        $types = \App\Models\Type::orderBy('name')->get();
        return view('manajer_operasional.inventory.edit_price', compact('types'));
    }
    
    public function updatePrice(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:types,id',
            'purchase_price' => 'required|numeric|min:0',
        ]);
    
        // Ambil semua product id berdasarkan type
        $productIds = \App\Models\Product::where('type_id', $request->type_id)->pluck('id');
    
        if ($productIds->isEmpty()) {
            return back()->with('error', 'Tidak ada produk dengan tipe tersebut.');
        }
    
        // Update semua inventory item yang statusnya in_stock dan product_id masuk dalam list
        $updated = \App\Models\InventoryItem::whereIn('product_id', $productIds)
            ->where('status', 'in_stock')
            ->update([
                'purchase_price' => $request->purchase_price
            ]);
    
        return redirect()->back()->with('success', "Harga modal berhasil diperbarui untuk {$updated} item.");
    }

}
