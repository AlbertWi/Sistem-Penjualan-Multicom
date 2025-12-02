<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Type;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Product::with(['brand', 'type']);
        if ($request->has('q') && $request->q !== '') {
            $keyword = $request->q;
            $query->where('name', 'LIKE', "%{$keyword}%");
        }
        $products = $query->get();
        $brands = \App\Models\Brand::all();
        $types = \App\Models\Type::all();
        return view('admin.products.index', compact('products','brands', 'types'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|string|max:255',
            'type_id' => 'required|exists:types,id'
        ],[
            'name.required' => 'Nama Produk harus diisi.',
            'brand_id.required' => 'Brand harus diisi.',
            'type_id.required' => 'Type harus diisi.',
        ]);
        Product::create($validated);
        return redirect()->route('products.index')
            ->with('success', 'Product Berhasil Ditambah');
    }
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ],[
            'name.required' => 'Nama Produk harus diisi.',
        ]);
        $product->update($validated);
        return redirect()->route('products.index')
            ->with('success', 'Product Berhasil Diperbarui');
    }
    public function getLatestPrice(Product $product)
    {
        // Ambil harga terakhir dari purchase item
        $lastPurchaseItem = $product->purchaseItems()->latest()->first();
    
        return response()->json([
            'price' => $lastPurchaseItem?->price ?? 0
        ]);
    }
    public function search(Request $request)
    {
        $query = $request->get('q');
    
        $products = \App\Models\Product::where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name']);
    
        return response()->json($products);
    }
    
    public function updateModalView()
    {
        $products = \App\Models\Product::with('brand')
        ->withAvg(['inventoryItems as average_cost_price' => function ($query) {
            $query->where('status', 'in_stock');
        }], 'cost_price')
        ->paginate(20);
        return view('admin.sales.update_modal', compact('products'));
    }

    public function updateModal(Request $request, $id)
    {
        $request->validate([
            'purchase_price' => 'required|numeric|min:0',
        ]);
    
        $product = Product::findOrFail($id);
    
        // Update harga modal di inventory_items hanya untuk barang yang masih in_stock
        \DB::table('inventory_items')
            ->where('product_id', $product->id)
            ->where('status', 'in_stock')
            ->update(['cost_price' => $request->cost_price]);
    
        return redirect()->back()->with('success', 'Harga modal berhasil diperbarui!');
    }


}
