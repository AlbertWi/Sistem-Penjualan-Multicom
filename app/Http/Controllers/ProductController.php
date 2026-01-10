<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Type;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;



class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Product::with(['brand', 'type']);

        // 🔍 Search nama produk
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        // 🔄 Filter status aktif / nonaktif
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', 1);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', 0);
            }
        }

        $products = $query->latest()->get();

        $brands = \App\Models\Brand::all();
        $types  = \App\Models\Type::all();

        return view(
            'manajer_operasional.products.index',
            compact('products', 'brands', 'types')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|string|max:255',
            'type_id' => 'required|exists:types,id',
            'foto.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'warna'  => 'required|string|max:50',
            'ram' => 'required|integer',
            'rom' => 'required|integer',
            'baterai' => 'required|integer',
            'ukuran_layar' => 'required|numeric',
            'masa_garansi' => 'required|integer',
            'resolusi_kamera' => 'required|string',
            'jumlah_slot_sim' => 'required|integer|in:1,2',
        ],[
            'name.required' => 'Nama Produk harus diisi.',
            'brand_id.required' => 'Brand harus diisi.',
            'type_id.required' => 'Type harus diisi.',
        ]);
        
        // ✅ Simpan hasil create ke variabel $product
        $product = Product::create($validated);
        
        // ✅ Sekarang $product sudah ada
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $file) {
                $path = $file->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'file_path'  => $path,
                ]);
            }
        }
        
        return redirect()->route('manajer_operasional.products.index')
            ->with('success', 'Product Berhasil Ditambah');
    }
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|string|max:255',
            'type_id' => 'required|exists:types,id',
            'foto.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'warna'  => 'required|string|max:50',
            'ram' => 'required|integer',
            'rom' => 'required|integer',
            'baterai' => 'required|integer',
            'ukuran_layar' => 'required|numeric',
            'masa_garansi' => 'required|integer',
            'resolusi_kamera' => 'required|string',
            'jumlah_slot_sim' => 'required|integer|in:1,2',
        ],[
            'name.required' => 'Nama Produk harus diisi.',
        ]);
        $product->update($validated);
        return redirect()->route('manajer_operasional.products.index')->with('success', 'Product Berhasil Diperbarui');
    }
    public function getLatestPrice(Product $product)
    {
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
        return view('manajer_operasional.sales.update_modal', compact('products'));
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

    public function toggleStatus(Product $product)
    {
        $product->update([
            'is_active' => ! $product->is_active
        ]);

        return back()->with(
            'success',
            $product->is_active
                ? 'Produk berhasil diaktifkan.'
                : 'Produk berhasil dinonaktifkan (discontinue).'
        );
    }

}
