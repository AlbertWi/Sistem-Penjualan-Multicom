<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        if (!auth()->guard('customer')->check()) {
            return redirect()->route('ecom.login')
                ->with('error', 'Silakan login terlebih dahulu untuk melihat keranjang.');
        }
        
        $cart = session()->get('cart', []);
        
        // Hitung total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['qty'];
        }
        
        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request)
    {
        \Log::info('====== ADD TO CART START ======');
        \Log::info('Request data:', $request->all());
        \Log::info('Session ID:', ['session_id' => session()->getId()]);
        
        // Cek apakah customer sudah login
        if (!auth()->guard('customer')->check()) {
            \Log::warning('Customer not authenticated for add to cart');
            return redirect()->route('ecom.login')
                ->with('error', 'Silakan login terlebih dahulu untuk menambahkan ke keranjang.')
                ->with('intended', url()->current());
        }
        
        \Log::info('Customer authenticated:', ['customer_id' => auth()->guard('customer')->id()]);
        
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1'
        ]);
        
        $product = Product::with(['ecomSetting', 'images', 'inventoryItems'])
            ->findOrFail($request->product_id);
        
        \Log::info('Product found:', [
            'id' => $product->id,
            'name' => $product->name,
            'has_ecom_setting' => !is_null($product->ecomSetting),
            'is_listed' => optional($product->ecomSetting)->is_listed
        ]);
        
        if (! optional($product->ecomSetting)->is_listed) {
            \Log::warning('Product not listed:', ['product_id' => $product->id]);
            return back()->with('error', 'Produk tidak tersedia.');
        }
        
        $stock = $product->inventoryItems()
            ->where('status', 'in_stock')
            ->count();
        
        \Log::info('Stock info:', ['stock' => $stock, 'requested_qty' => $request->qty]);
        
        if ($request->qty > $stock) {
            \Log::warning('Insufficient stock:', ['product_id' => $product->id, 'stock' => $stock, 'requested' => $request->qty]);
            return back()->with('error', 'Stok tidak mencukupi.');
        }
        
        $cart = session()->get('cart', []);
        \Log::info('Cart before update:', ['cart' => $cart]);
        
        if (isset($cart[$product->id])) {
            $cart[$product->id]['qty'] += $request->qty;
            \Log::info('Updated existing product in cart');
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'name'  => $product->name,
                'price' => $product->ecomSetting->ecom_price,
                'qty'   => $request->qty,
                'image' => optional($product->images->first())->file_path
            ];
            \Log::info('Added new product to cart');
        }
        
        // Simpan ke session
        session()->put('cart', $cart);
        session()->save(); // PENTING: Simpan session
        
        \Log::info('Cart after update:', ['cart' => session()->get('cart')]);
        \Log::info('====== ADD TO CART END ======');
        
        return redirect()->route('cart.index')
            ->with('success', 'Produk ditambahkan ke keranjang.');
    }
}